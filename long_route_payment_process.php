<?php
// Start output buffering to prevent accidental output before headers
ob_start();

session_start();
include 'db.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include QR Code library
include 'phpqrcode/qrlib.php';

// Load Composer's autoloader
require 'vendor/autoload.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_log("Starting long_route_payment_process.php");

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in. Redirecting to login.php");
    header('Location: login.php');
    exit;
}

// Check if the payment form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['payment_method'])) {
    error_log("Invalid form submission: Missing required fields. Redirecting to long_route_payment.php");
    header('Location: long_route_payment.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method']; // bkash, rocket, or card

// Log user_id for debugging
error_log("Processing payment for user_id: $user_id");

// Validate account number and PIN based on payment method
$account_number = isset($_POST['account_number']) ? (int)$_POST['account_number'] : null;
$pin = isset($_POST['pin']) ? (int)$_POST['pin'] : null;
$card_number = isset($_POST['card_number']) ? (int)$_POST['card_number'] : null;
$expiry_date = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
$cvv = isset($_POST['cvv']) ? (int)$_POST['cvv'] : null;

// For bKash or Rocket, account_number and pin are required
if ($payment_method !== 'card' && (!$account_number || !$pin)) {
    error_log("Missing account_number or pin for payment method: $payment_method. Redirecting to long_route_payment.php");
    header('Location: long_route_payment.php');
    exit;
}

// For card, card_number, expiry_date, and cvv are required
if ($payment_method === 'card' && (!$card_number || !$expiry_date || !$cvv)) {
    error_log("Missing card details for payment method: $payment_method. Redirecting to long_route_payment.php");
    header('Location: long_route_payment.php');
    exit;
}

// Retrieve booking details from the session
if (!isset($_SESSION['long_route_booking'])) {
    error_log("No booking session found. Redirecting to long_route_seats.php");
    header('Location: long_route_seats.php');
    exit;
}

$booking = $_SESSION['long_route_booking'];
$bus_id = $booking['bus_id'];
$selected_seats = $booking['selected_seats'];
$total_fare = $booking['total_fare'];
$journey_date = $booking['journey_date']; // Add this line
error_log("Journey date from session: $journey_date");

// Begin transaction
$conn3->begin_transaction();

try {
    // Validate the payment account (for bKash and Rocket)
    if ($payment_method !== 'card') {
        $account_query = "SELECT * FROM demo_accounts WHERE account_type = ? AND account_number = ? AND pin = ?";
        $stmt = $conn1->prepare($account_query);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare account query - " . $conn1->error);
        }
        $stmt->bind_param("sii", $payment_method, $account_number, $pin);
        $stmt->execute();
        $account_result = $stmt->get_result();
        $account = $account_result->fetch_assoc();

        if (!$account) {
            throw new Exception("Invalid account details: account_number=$account_number, payment_method=$payment_method");
        }

        // Check if the account has sufficient balance
        if ($account['balance'] < $total_fare) {
            throw new Exception("Insufficient balance: balance={$account['balance']}, total_fare=$total_fare");
        }

        // Deduct the fare from the account balance
        $new_balance = $account['balance'] - $total_fare;
        $update_balance_query = "UPDATE demo_accounts SET balance = ? WHERE id = ?";
        $stmt = $conn1->prepare($update_balance_query);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare balance update query - " . $conn1->error);
        }
        $stmt->bind_param("ii", $new_balance, $account['id']);
        $stmt->execute();
    } else {
        // For card payments, you might want to integrate a real payment gateway.
        // For now, we'll assume the card payment is successful.
        error_log("Processing card payment: card_number=$card_number, expiry_date=$expiry_date, cvv=$cvv");
    }

    // Generate a random transaction ID
    $transaction_id = 'txn-' . strtoupper(bin2hex(random_bytes(3))); // Example: TXN-5F2A1B3C4D
    error_log("Generated transaction_id: $transaction_id");

    // Save the transaction in the long_route_transactions table
    $transaction_query = "INSERT INTO long_route_transactions (transaction_id, user_id, bus_id, seat_numbers, amount, payment_method, payment_time, payment_status, payment_transaction_id, journey_date) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'completed', ?, ?)";
    $seat_numbers = implode(',', array_map(function ($seat) {
        return $seat['number'];
    }, $selected_seats));
    $payment_transaction_id = $transaction_id; // Use the same transaction ID for payment_transaction_id
    $stmt = $conn3->prepare($transaction_query);
    if (!$stmt) {
        throw new Exception("Database error: Failed to prepare transaction insert query - " . $conn3->error);
    }
    $stmt->bind_param("siisisss", $transaction_id, $user_id, $bus_id, $seat_numbers, $total_fare, $payment_method, $payment_transaction_id, $journey_date);
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert transaction: " . $stmt->error);
    }
    error_log("Transaction inserted successfully: transaction_id=$transaction_id, user_id=$user_id, bus_id=$bus_id");

    // Mark the selected seats as booked
    foreach ($selected_seats as $seat) {
        $update_seat_query = "UPDATE long_route_seats SET status = 'booked' WHERE bus_id = ? AND seat_number = ?";
        $stmt = $conn3->prepare($update_seat_query);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare seat update query - " . $conn3->error);
        }
        $stmt->bind_param("is", $bus_id, $seat['number']);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update seat status: " . $stmt->error);
        }
    }
    error_log("Seats updated successfully for bus_id: $bus_id");

    // Fetch bus details for the email
    $bus_query = "SELECT * FROM long_route_buses WHERE bus_id = ?";
    $stmt = $conn3->prepare($bus_query);
    if (!$stmt) {
        throw new Exception("Database error: Failed to prepare bus query - " . $conn3->error);
    }
    $stmt->bind_param("i", $bus_id);
    $stmt->execute();
    $bus_result = $stmt->get_result();
    $bus = $bus_result->fetch_assoc();
    if (!$bus) {
        throw new Exception("Bus not found for bus_id: $bus_id");
    }

    // Fetch user details for the email
    $user_query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn1->prepare($user_query);
    if (!$stmt) {
        throw new Exception("Database error: Failed to prepare user query - " . $conn3->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();
    if (!$user) {
        throw new Exception("User not found for user_id: $user_id");
    }

    // Generate QR Code data
    $qrCodeData = "Transaction ID: $transaction_id\n" .
                  "Seats: $seat_numbers\n" .
                  "Bus: {$bus['bus_name']}\n" .
                  "From: {$bus['from_location']}\n" .
                  "To: {$bus['to_location']}\n" .
                  "Date: " . date('l, F j, Y', strtotime($journey_date)) . "\n" . // Use $journey_date
                  "Departure: " . date('h:i A', strtotime($bus['departure_time'])) . "\n" .
                  "Amount Paid: BDT " . number_format($total_fare, 2);

    // Define the path to save the QR code image
    $qrCodePath = 'qrcodes/' . $transaction_id . '.png';

    // Generate and save the QR code
    QRcode::png($qrCodeData, $qrCodePath, QR_ECLEVEL_H, 5);

    // Send email confirmation
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'masstransportsystem@gmail.com';  // Replace with your email
        $mail->Password   = 'vsez xczk yqfm mdbx';         // Replace with your password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('noreply@masstransport.com', 'Mass Transport Ticketing System');
        $mail->addAddress($user['email'], $user['name']);

        // Attach QR Code to the email
        $mail->addAttachment($qrCodePath);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Bus Ticket Confirmation - ' . $transaction_id;

        // Email body
        $email_body = "
            <h2>Ticket Confirmation</h2>
            <p>Dear {$user['name']},</p>
            <p>Thank you for booking with Mass Transport Ticketing System. Your ticket has been confirmed.</p>
            
            <h3>Booking Details:</h3>
            <p><strong>Transaction ID:</strong> {$transaction_id}</p>
            <p><strong>Bus:</strong> {$bus['bus_name']}</p>
            <p><strong>From:</strong> {$bus['from_location']}</p>
            <p><strong>To:</strong> {$bus['to_location']}</p>
            <p><strong>Date:</strong> " . date('l, F j, Y', strtotime($journey_date)) . "</p>
            <p><strong>Departure Time:</strong> " . date('h:i A', strtotime($bus['departure_time'])) . "</p>
            <p><strong>Seat Numbers:</strong> {$seat_numbers}</p>
            <p><strong>Amount Paid:</strong> BDT " . number_format($total_fare, 2) . "</p>
            
            <p>Please arrive at the bus station at least 30 minutes before departure. Show this email or your transaction ID to the bus conductor.</p>
            
            <p>Thank you for choosing Mass Transport Ticketing System!</p>
        ";
        $mail->Body = $email_body;
        $mail->AddEmbeddedImage($qrCodePath, 'qr_code');

        $mail->send();
        error_log("Email sent successfully to {$user['email']}");
    } catch (Exception $e) {
        // Email sending failed, but don't disrupt the user experience
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

    // Commit the transaction
    if (!$conn3->commit()) {
        throw new Exception("Failed to commit transaction: " . $conn3->error);
    }
    error_log("Transaction committed successfully");

    // Verify the transaction exists in the database
    $verify_query = "SELECT * FROM long_route_transactions WHERE payment_transaction_id = ? AND user_id = ?";
    $stmt = $conn3->prepare($verify_query);
    $stmt->bind_param("si", $transaction_id, $user_id);
    $stmt->execute();
    $verify_result = $stmt->get_result();
    $verify_transaction = $verify_result->fetch_assoc();
    if (!$verify_transaction) {
        throw new Exception("Transaction not found after commit: transaction_id=$transaction_id, user_id=$user_id");
    }
    error_log("Transaction verified in database: " . print_r($verify_transaction, true));

    // Clear the booking session
    unset($_SESSION['long_route_booking']);

    // Set success message
    $_SESSION['payment_success'] = "Your payment was successful! Your seats have been booked. Transaction ID: $transaction_id";
    error_log("Payment processed successfully. Session payment_success set: " . $_SESSION['payment_success']);

    // Ensure the session is saved
    session_write_close();

    // Ensure no output has been sent before the redirect
    if (ob_get_length()) {
        error_log("Output buffer contents: " . ob_get_contents());
        ob_end_clean(); // Clear the buffer if there's any output
    }

    // Redirect to success page
    error_log("Redirecting to long_route_payment_success.php");
    header('Location: long_route_payment_success.php');
    exit;
} catch (Exception $e) {
    // Rollback the transaction in case of an error
    $conn3->rollback();
    error_log("Transaction failed: " . $e->getMessage());

    $_SESSION['payment_error'] = "An error occurred while processing your payment. Please try again.";
    error_log("Redirecting to long_route_payment.php due to error: " . $e->getMessage());
    header('Location: long_route_payment.php');
    exit;
}