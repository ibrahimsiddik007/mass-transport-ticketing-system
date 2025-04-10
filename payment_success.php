<?php
// Ensure session is started only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Dhaka'); // Set your timezone
include 'db.php'; // Include your database connection file

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader (if using Composer)
require 'vendor/autoload.php';

// Include QR Code library only once
require_once 'phpqrcode/qrlib.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'metro.php';
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the token is valid
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        header('Location: metro.php');
        exit;
    }

    // Unset the token to prevent reuse
    unset($_SESSION['token']);

    $startLocation = $_POST['startLocation'];
    $endLocation = $_POST['endLocation'];
    $fare = $_POST['fare'];
    $account_type = $_POST['payment_method'];
    $accountNumber = $_POST['account_number'];
    $pin = $_POST['pin'];

    // Simulate payment success
    $paymentStatus = "success";

    // Generate unique transaction_id
    $transactionId = 'txn_' . substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 7);

    // Get user_id from session
    $userId = $_SESSION['user_id'];

    // Get current timestamp
    $createdAt = date('Y-m-d H:i:s');

    // Calculate "valid till" time (e.g., 24 hours from now)
    $validTill = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Validate payment details
    $stmt = $conn1->prepare("SELECT balance FROM demo_accounts WHERE account_type = ? AND account_number = ? AND pin = ?");
    $stmt->bind_param("sss", $account_type, $accountNumber, $pin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('Invalid account details or insufficient balance.'); window.location.href='payment.php';</script>";
        exit;
    }

    $row = $result->fetch_assoc();
    $currentBalance = $row['balance'];

    // Deduct the fare from the account balance
    $newBalance = $currentBalance - $fare;
    $stmt = $conn1->prepare("UPDATE demo_accounts SET balance = ? WHERE account_type = ? AND account_number = ?");
    $stmt->bind_param("dss", $newBalance, $account_type, $accountNumber);
    $stmt->execute();

    // Save transaction details
    $stmt = $conn1->prepare("INSERT INTO transactions (transaction_id, user_id, start_location, end_location, fare, created_at, valid_till) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn1->error));
    }
    $stmt->bind_param("sssssss", $transactionId, $userId, $startLocation, $endLocation, $fare, $createdAt, $validTill);
    $stmt->execute();
    if ($stmt->affected_rows === 0) {
        die('Insert failed: ' . htmlspecialchars($stmt->error));
    }
    $stmt->close();

    // Store transaction ID in session for receipt generation
    $_SESSION['transaction_id'] = $transactionId;

    // Fetch user email from the database
    $userQuery = "SELECT email, name FROM users WHERE id = ?";
    $userStmt = $conn1->prepare($userQuery);
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $user = $userResult->fetch_assoc();

    if (!$user) {
        echo "Error: User not found.";
        exit;
    }

    $userEmail = $user['email'];
    $userName = $user['name'];

    // Generate QR code
    $qrContent = "Transaction ID: $transactionId\nStart Location: $startLocation\nEnd Location: $endLocation\nFare: BDT $fare\nPayment Time: $createdAt\nValid Till: $validTill";
    $qrFilePath = 'qrcodes/' . $transactionId . '.png';
    QRcode::png($qrContent, $qrFilePath, QR_ECLEVEL_L, 3); // Reduced size by lowering the scale factor

    // Send confirmation email using PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = 'masstransportsystem@gmail.com'; // Replace with your email
        $mail->Password = 'vsez xczk yqfm mdbx'; // Replace with your App Password

        $mail->setFrom('masstransportsystem@gmail.com', 'Mass Transport Ticketing System');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Payment Confirmation - Mass Transport Ticketing System';
        $mail->Body = "
            <h1>Payment Confirmation</h1>
            <p>Dear {$userName},</p>
            <p>Your payment has been successfully processed. Below are the details:</p>
            <ul>
                <li><strong>Transaction ID:</strong> {$transactionId}</li>
                <li><strong>Start Location:</strong> {$startLocation}</li>
                <li><strong>End Location:</strong> {$endLocation}</li>
                <li><strong>Fare:</strong> BDT {$fare}</li>
                <li><strong>Payment Time:</strong> {$createdAt}</li>
                <li><strong>Valid Till:</strong> {$validTill}</li>
            </ul>
            <p>Attached is your QR code for the transaction. Please keep it safe.</p>
            <p>If you have any questions or concerns, feel free to contact us.</p>
            <p>Thank you for using the Mass Transport Ticketing System.</p>
        ";

        // Attach QR Code
        $mail->addAttachment($qrFilePath);

        // Send the email
        $mail->send();
        echo "Confirmation email sent successfully.";
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        // Ensure the QR code is resized for better visibility
        $qrContent = "Transaction ID: $transactionId\nStart Location: $startLocation\nEnd Location: $endLocation\nFare: BDT $fare\nPayment Time: $createdAt\nValid Till: $validTill";
        $qrFilePath = 'qrcodes/' . $transactionId . '.png';
        QRcode::png($qrContent, $qrFilePath, QR_ECLEVEL_L, 3); // Adjusted scale factor for smaller QR code
    }

    // Redirect to payment success page
    header('Location: payment_success.php?success=true');
    exit;
}

// Fetch transaction details
if (isset($_SESSION['transaction_id'])) {
    $transaction_id = $_SESSION['transaction_id'];
    $stmt = $conn1->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $transaction = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else {
    header('Location: metro.php');
    exit;
}

// Fetch user email from the database
$user_id = $_SESSION['user_id'];
$userQuery = "SELECT email, name FROM users WHERE id = ?";
$userStmt = $conn1->prepare($userQuery);
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();

if (!$user) {
    echo "Error: User not found.";
    exit;
}

$userEmail = $user['email'];
$userName = $user['name'];

// Generate QR code
$qrContent = "Transaction ID: {$transaction['transaction_id']}\nStart Location: {$transaction['start_location']}\nEnd Location: {$transaction['end_location']}\nFare: BDT {$transaction['fare']}\nPayment Time: {$transaction['created_at']}";
$qrFilePath = 'qrcodes/' . $transaction_id . '.png';
QRcode::png($qrContent, $qrFilePath, QR_ECLEVEL_L, 5);

// Send confirmation email using PHPMailer
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->Username = 'masstransportsystem@gmail.com'; // Replace with your email
    $mail->Password = 'vsez xczk yqfm mdbx'; // Replace with your App Password

    $mail->setFrom('masstransportsystem@gmail.com', 'Mass Transport Ticketing System');
    $mail->addAddress($userEmail);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Payment Confirmation - Mass Transport Ticketing System';
    $mail->Body = "
        <h1>Payment Confirmation</h1>
        <p>Dear {$userName},</p>
        <p>Your payment has been successfully processed. Below are the details:</p>
        <ul>
            <li><strong>Transaction ID:</strong> {$transaction['transaction_id']}</li>
            <li><strong>Start Location:</strong> {$transaction['start_location']}</li>
            <li><strong>End Location:</strong> {$transaction['end_location']}</li>
            <li><strong>Fare:</strong> BDT {$transaction['fare']}</li>
            <li><strong>Payment Time:</strong> {$transaction['created_at']}</li>
        </ul>
        <p>Attached is your QR code for the transaction. Please keep it safe.</p>
        <p>If you have any questions or concerns, feel free to contact us.</p>
        <p>Thank you for using the Mass Transport Ticketing System.</p>
    ";

    // Attach QR Code
    $mail->addAttachment($qrFilePath);

    // Send the email
    $mail->send();
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background: url('images/Metro_Ticket_Counter.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #000000; /* Default light mode text color */
            font-family: Arial, sans-serif;
            animation: fadeIn 2s ease-in-out;
        }
        body.dark-mode {
            background-color: #121212;
            color: #ffffff; /* Dark mode text color */
        }
        .card {
            margin: 20px auto;
            animation: fadeIn 2s ease-in-out;
            transition: transform 0.3s, box-shadow 0.3s;
            background: rgba(30, 204, 53, 0.8); /* Slightly transparent background */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(0, 0, 0, 0.1);
            max-width: 500px; /* Set max width for the card */
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
        }
        body.dark-mode .card {
            background: #1e1e1e; /* Solid dark background color */
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card-body {
            color: #000000; /* Light mode card text */
        }
        body.dark-mode .card-body {
            color: #ffffff; /* Dark mode card text */
        }
        .btn-primary {
            background-color:rgb(67, 97, 58);
            border-color: #007bff;
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s;
        }
        .btn-primary:hover {
            background-color:rgb(8, 116, 13);
            border-color: #0056b3;
            transform: scale(1.05);
        }

        .btn-secondary {
            background-color:rgb(182, 37, 44);
            border-color:rgb(184, 48, 66);
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s;
        }
        .btn-secondary:hover {
            background-color:rgb(250, 0, 0);
            border-color:rgb(155, 12, 12);
            transform: scale(1.05);
        }
        body.dark-mode .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
        }
        body.dark-mode .btn-primary:hover {
            background-color: #9a67ea;
            border-color: #9a67ea;
            transform: scale(1.05);
        }
        .review-card {
            background: rgba(255, 255, 255, 0.8); /* Slightly transparent background */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        body.dark-mode .review-card {
            background: #2e2e2e; /* Solid dark background color */
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        img.qr-code {
            width: 150px; /* Set a fixed width for the QR code */
            height: auto; /* Maintain aspect ratio */
            margin-bottom: 20px; /* Add space below the QR code */
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="card-title"><i class="fas fa-check-circle"></i> Payment Successful</h3>
                        <p class="card-text">Your payment has been successfully processed.</p>
                        <p class="card-text"><strong>Transaction ID:</strong> <?php echo htmlspecialchars($transaction['transaction_id']); ?></p>
                        <p class="card-text"><strong>User Name:</strong> <?php echo htmlspecialchars($userName); ?></p>
                        <p class="card-text"><strong>Start Location:</strong> <?php echo htmlspecialchars($transaction['start_location']); ?></p>
                        <p class="card-text"><strong>End Location:</strong> <?php echo htmlspecialchars($transaction['end_location']); ?></p>
                        <p class="card-text"><strong>Fare:</strong> BDT <?php echo htmlspecialchars($transaction['fare']); ?></p>
                        <p class="card-text"><strong>Payment Time:</strong> <?php echo htmlspecialchars($transaction['created_at']); ?></p>
                        <img src="qrcodes/<?php echo htmlspecialchars($transaction['transaction_id']); ?>.png" alt="QR Code" class="img-fluid qr-code">
                        
                        <a href="metro_generate_receipt.php?transaction_id=<?php echo htmlspecialchars($transaction['transaction_id']); ?>" class="btn btn-primary btn-block"><i class="fas fa-download"></i> Download Receipt</a>
                        <a href="metro.php" class="btn btn-secondary btn-block"><i class="fas fa-arrow-left"></i> Back to Metro Main Page</a>
                    </div>
                </div>
                <div class="card review-card mt-4">
                    <div class="card-body text-center">
                        <h5 class="card-title">Like our service?</h5>
                        <p class="card-text">Please leave a review and let us know your thoughts.</p>
                        <a href="review.php" class="btn btn-success btn-block"><i class="fas fa-star"></i> Leave a Review</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('dark-mode', document.body.classList.contains('dark-mode'));
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('dark-mode') === 'true') {
                document.body.classList.add('dark-mode');
            }

        });

        window.history.forward();

        // Note: Ensure there's an element with ID 'dark-mode-toggle' in nav.php or elsewhere
        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
    </script>
</body>
</html>