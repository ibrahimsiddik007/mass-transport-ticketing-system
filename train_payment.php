<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    if (!isset($_SESSION['redirect_to'])) {
        $_SESSION['redirect_to'] = 'train_payment.php';
    }
    header('Location: login.php');
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$user_id = $_SESSION['user_id'];

// Fetch reservation details for the user
$stmt = $conn2->prepare("SELECT r.*, t.train_name, t.start_point, t.end_point, t.fare, t.departure_time, c.compartment_id 
                         FROM reservations r
                         JOIN trains t ON r.train_id = t.train_id
                         JOIN compartments c ON r.compartment_id = c.compartment_id
                         WHERE r.user_id = ? AND r.status = 'pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    header('Location: train.php');
    exit;
}

// Fetch all seat numbers for the reservation
$seat_stmt = $conn2->prepare("SELECT seat_number 
                              FROM reservations 
                              WHERE user_id = ? AND status = 'pending'");
$seat_stmt->bind_param("i", $user_id);
$seat_stmt->execute();
$seat_result = $seat_stmt->get_result();
$seat_numbers = [];
while ($seat_row = $seat_result->fetch_assoc()) {
    $seat_numbers[] = $seat_row['seat_number'];
}
$seat_numbers_str = implode(', ', $seat_numbers);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    $account_number = $_POST['account_number'] ?? null;
    $pin = $_POST['pin'] ?? null;
    $card_number = $_POST['card_number'] ?? null;
    $expiry_date = $_POST['expiry_date'] ?? null;
    $cvv = $_POST['cvv'] ?? null;

    $amount = $reservation['fare'];

    // Validate payment details
    if ($payment_method === 'bkash' || $payment_method === 'rocket') {
        if (!$account_number || !$pin) {
            $error_message = "Account number and PIN are required for $payment_method.";
        }
    } elseif ($payment_method === 'card') {
        if (!$card_number || !$expiry_date || !$cvv) {
            $error_message = "Card details are required for card payment.";
        }
    }

    if (!isset($error_message)) {
        // Process payment (dummy processing for demonstration)
        $payment_success = true; // Assume payment is successful

        if ($payment_success) {
            // Update reservation status to 'paid'
            $update_stmt = $conn2->prepare("UPDATE reservations SET status = 'paid' WHERE user_id = ? AND status = 'pending'");
            $update_stmt->bind_param("i", $user_id);
            $update_stmt->execute();

            // Insert transaction details into transactions table
            $transaction_id = 'txn_' . substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 7);
            date_default_timezone_set('Asia/Dhaka'); // Set appropriate timezone
            $payment_time = date("Y-m-d H:i:s", time()); // Get the current date and time
            $compartment_ID = $reservation['compartment_id'];
            $train_id = $reservation['train_id'];
            $seats = $seat_numbers_str;
            $total_amount = $reservation['fare'] * count($seat_numbers); // Calculate total amount

            $insert_stmt = $conn2->prepare("INSERT INTO train_transactions (transaction_id, user_id, amount, compartment_ID, train_ID, seats, payment_time, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("siiiisss", $transaction_id, $user_id, $total_amount, $compartment_ID, $train_id, $seats, $payment_time, $payment_method);
            $insert_stmt->execute();

            // Generate QR Code
            include 'phpqrcode/qrlib.php';
            $qrCodeData = "Transaction ID: $transaction_id\n" .
                          "Seats: $seats\n" .
                          "Train: {$reservation['train_name']}\n" .
                          "From: {$reservation['start_point']}\n" .
                          "To: {$reservation['end_point']}\n" .
                          "Date: " . date('l, F j, Y', strtotime($reservation['departure_time'])) . "\n" .
                          "Amount Paid: BDT " . number_format($total_amount, 2);
            $qrCodePath = 'qrcodes/' . $transaction_id . '.png';
            QRcode::png($qrCodeData, $qrCodePath, QR_ECLEVEL_H, 5);

            // Send email confirmation


            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'masstransportsystem@gmail.com'; // Replace with your email
                $mail->Password   = 'vsez xczk yqfm mdbx';           // Replace with your email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('masstransportsystem@gmail.com', 'Mass Transport Ticketing System');
                $mail->addAddress($_SESSION['email']); // User's email from session

                // Attachments
                $mail->addAttachment($qrCodePath, 'Transaction_QR_Code.png');

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Payment Confirmation - Transaction ID: ' . $transaction_id;
                $mail->Body    = '<h3>Payment Confirmation</h3>
                                  <p>Dear ' . htmlspecialchars($_SESSION['user_name']) . ',</p>
                                  <p>Your payment has been successfully processed. Please find the details below:</p>
                                  <ul>
                                      <li><strong>Transaction ID:</strong> ' . htmlspecialchars($transaction_id) . '</li>
                                      <li><strong>Train:</strong> ' . htmlspecialchars($reservation['train_name']) . '</li>
                                      <li><strong>Start Point:</strong> ' . htmlspecialchars($reservation['start_point']) . '</li>
                                      <li><strong>End Point:</strong> ' . htmlspecialchars($reservation['end_point']) . '</li>
                                      <li><strong>Compartment Number:</strong> ' . htmlspecialchars($reservation['compartment_id']) . '</li>
                                      <li><strong>Seat Numbers:</strong> ' . htmlspecialchars($seats) . '</li>
                                      <li><strong>Amount Paid:</strong> BDT ' . number_format($total_amount, 2) . '</li>
                                      <li><strong>Payment Time:</strong> ' . htmlspecialchars($payment_time) . '</li>
                                  </ul>
                                  <p>Please find your QR code attached for verification purposes.</p>
                                  <p>Thank you for using our service!</p>';

                $mail->send();
            } catch (Exception $e) {
                error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }

            $_SESSION['payment_completed'] = true;
            $_SESSION['transaction_id'] = $transaction_id;
            header('Location: train_payment_confirmation.php?success=true');
            exit;
        } else {
            $error_message = "Payment failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Train Payment - Mass Transport Ticketing System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background: url('images/Railway Background Image.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            animation: fadeIn 1.5s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        body.dark-mode {
            background-color: #121212;
            color: #ffffff;
        }
        
        .container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .card {
            margin: 20px;
            animation: fadeIn 1.5s ease-in-out;
            transition: transform 0.3s, box-shadow 0.3s;
            background: rgba(255, 255, 255, 0.92); /* More solid background */
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
        }
        
        body.dark-mode .card {
            background: rgba(30, 30, 30, 0.95); /* More solid dark background */
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        }
        
        .card-title {
            color: #1a2530;
            font-weight: bold;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.15);
            padding-bottom: 0.5rem;
        }
        
        body.dark-mode .card-title {
            color: #ecf0f1;
            border-bottom: 2px solid rgba(255, 255, 255, 0.15);
        }
        
        .card-text {
            margin-bottom: 1rem;
            color: #000000; /* Black text for better visibility */
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        body.dark-mode .card-text {
            color: #ffffff; /* White text for dark mode */
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .card-text:hover {
            transform: translateX(5px);
            background: rgba(0, 0, 0, 0.05); /* Subtle hover effect */
        }
        
        body.dark-mode .card-text:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .total-fare {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1a2530;
            margin-top: 1rem;
            padding: 15px;
            background: rgba(0, 0, 0, 0.05); /* Subtle background */
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        body.dark-mode .total-fare {
            color: #ecf0f1;
            background: rgba(255, 255, 255, 0.05);
        }
        
        .ticket-count {
            font-size: 0.9rem;
            color: #555555;
            margin-left: 10px;
        }
        
        body.dark-mode .ticket-count {
            color: #aaaaaa;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.8); /* More opaque background */
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            padding: 10px 15px;
            color: #000000; /* Black text for visibility */
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.95);
            border-color: #2c3e50;
            box-shadow: 0 0 5px rgba(44, 62, 80, 0.3);
        }
        
        body.dark-mode .form-control {
            background: rgba(30, 30, 30, 0.8); /* More opaque background */
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        body.dark-mode .form-control:focus {
            background: rgba(30, 30, 30, 0.95);
            border-color: #ecf0f1;
        }
        
        .btn-primary {
            background: #2c3e50;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: bold;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            color: white;
        }
        
        .btn-primary:hover {
            background: #3498db;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #c0392b;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: bold;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #e74c3c;
            transform: translateY(-2px);
        }
        
        .payment-method-label {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 10px;
            background: rgba(0, 0, 0, 0.05); /* Subtle background */
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #000000; /* Black text */
        }
        
        body.dark-mode .payment-method-label {
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff; /* White text */
        }
        
        .payment-method-label:hover {
            background: rgba(0, 0, 0, 0.1);
        }
        
        body.dark-mode .payment-method-label:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .payment-method-label input[type="radio"] {
            margin-right: 10px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
        <?php endif; ?>
        <div class="row w-100">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title"><i class="fas fa-train"></i> Journey Details</h3>
                        <p class="card-text">
                            <span><i class="fas fa-train"></i> Train:</span>
                            <span><?php echo $reservation['train_name']; ?></span>
                        </p>
                        <p class="card-text">
                            <span><i class="fas fa-th"></i> Compartment Number:</span>
                            <span><?php echo $reservation['compartment_id']; ?></span>
                        </p>
                        <p class="card-text">
                            <span><i class="fas fa-chair"></i> Seat Numbers:</span>
                            <span><?php echo $seat_numbers_str; ?></span>
                        </p>
                        <p class="card-text">
                            <span><i class="fas fa-map-marker-alt"></i> Start Point:</span>
                            <span><?php echo $reservation['start_point']; ?></span>
                        </p>
                        <p class="card-text">
                            <span><i class="fas fa-map-marker-alt"></i> End Point:</span>
                            <span><?php echo $reservation['end_point']; ?></span>
                        </p>
                        <p class="card-text">
                            <span><i class="fas fa-ticket-alt"></i> Number of Tickets:</span>
                            <span><?php echo count($seat_numbers); ?> <span class="ticket-count">(<?php echo implode(', ', $seat_numbers); ?>)</span></span>
                        </p>
                        <p class="card-text">
                            <span><i class="fas fa-money-bill-wave"></i> Fare per Ticket:</span>
                            <span>BDT <?php echo number_format($reservation['fare'], 2); ?></span>
                        </p>
                        <div class="total-fare">
                            <span>Total Amount to Pay:</span>
                            <span>BDT <?php echo number_format($reservation['fare'] * count($seat_numbers), 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title"><i class="fas fa-credit-card"></i> Payment Details</h3>
                        <form method="POST">
                            <div class="form-group">
                                <h4>Select Payment Method</h4>
                                <label class="payment-method-label">
                                    <input type="radio" name="payment_method" value="bkash" required> 
                                    <i class="fas fa-mobile-alt"></i> bKash
                                </label>
                                <label class="payment-method-label">
                                    <input type="radio" name="payment_method" value="rocket" required> 
                                    <i class="fas fa-rocket"></i> Rocket
                                </label>
                                <label class="payment-method-label">
                                    <input type="radio" name="payment_method" value="card" required> 
                                    <i class="fas fa-credit-card"></i> Card
                                </label>
                            </div>

                            <div id="bkash-rocket-fields" style="display: none;">
                                <div class="form-group">
                                    <label for="account_number">Account Number</label>
                                    <input type="number" class="form-control" id="account_number" name="account_number" placeholder="Enter your account number">
                                </div>
                                <div class="form-group">
                                    <label for="pin">PIN</label>
                                    <input type="password" class="form-control" id="pin" name="pin" placeholder="Enter your PIN">
                                </div>
                            </div>

                            <div id="card-fields" style="display: none;">
                                <div class="form-group">
                                    <label for="card_number">Card Number</label>
                                    <input type="number" class="form-control" id="card_number" name="card_number" placeholder="Enter your card number">
                                </div>
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="number" class="form-control" id="cvv" name="cvv" placeholder="Enter CVV">
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-credit-card"></i> Pay Now
                                </button>
                                <a href="train.php" class="btn btn-secondary btn-block mt-2">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            const bkashRocketFields = document.getElementById('bkash-rocket-fields');
            const cardFields = document.getElementById('card-fields');

            paymentMethods.forEach(method => {
                method.addEventListener('change', function () {
                    if (this.value === 'bkash' || this.value === 'rocket') {
                        bkashRocketFields.style.display = 'block';
                        cardFields.style.display = 'none';
                    } else if (this.value === 'card') {
                        bkashRocketFields.style.display = 'none';
                        cardFields.style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>
</html>