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
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #50c878;
            --accent-color: #ff6b6b;
            --glass-bg: rgba(0, 0, 0, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            --transition-speed: 0.3s;
        }

        body {
            background: url('images/Metro_Ticket_Counter.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: fadeIn 1.5s ease-out;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.5));
            z-index: -1;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--glass-shadow);
            animation: slideIn 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            max-width: 100%;
            margin: 0;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(80, 200, 120, 0.1));
            pointer-events: none;
            animation: shine 3s infinite linear;
        }

        @keyframes shine {
            0% { background-position: -100% 0; }
            100% { background-position: 200% 0; }
        }

        @keyframes slideIn {
            from { transform: translateY(50px) scale(0.95); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        .card-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 30px;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .card-title i {
            color: var(--secondary-color);
            font-size: 2.8rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .card-text {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-text i {
            color: var(--primary-color);
            font-size: 1.3rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .card-text strong {
            color: var(--secondary-color);
            font-weight: 600;
            min-width: 150px;
        }

        .qr-code {
            width: 200px;
            height: 200px;
            margin: 20px auto;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            animation: float 3s ease-in-out infinite;
            border: 2px solid var(--glass-border);
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #357abd);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
            margin-bottom: 15px;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .btn-primary:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 8px 25px rgba(74, 144, 226, 0.4);
        }

        .btn-primary:hover::before {
            transform: translateX(100%);
        }

        .btn-primary i {
            margin-right: 10px;
            animation: float 2s ease-in-out infinite;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #ff6b6b, #ff5252);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
        }

        .btn-secondary i {
            margin-right: 10px;
            animation: float 2s ease-in-out infinite;
        }

        .review-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--glass-shadow);
            animation: slideIn 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.3s both;
            height: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            min-height: 300px;
            margin: 0;
        }

        .review-card .card-title {
            font-size: 1.6rem;
            margin-bottom: 15px;
            color: var(--secondary-color);
        }

        .review-card .card-text {
            font-size: 1rem;
            margin-bottom: 25px;
            border: none;
            justify-content: center;
            line-height: 1.4;
        }

        .review-card .btn-success {
            margin-top: 10px;
            padding: 10px 20px;
            font-size: 1rem;
        }

        .btn-success {
            background: linear-gradient(135deg, #50c878, #3aa662);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(80, 200, 120, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 8px 25px rgba(80, 200, 120, 0.4);
        }

        .btn-success i {
            margin-right: 10px;
            animation: float 2s ease-in-out infinite;
        }

        /* Dark Mode Adjustments */
        body.dark-mode::before {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6));
        }

        body.dark-mode .card,
        body.dark-mode .review-card {
            background: rgba(0, 0, 0, 0.6);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .col-md-8, .col-md-4 {
                width: 100%;
                margin-bottom: 20px;
            }
            
            .review-card {
                margin-top: 0;
                min-height: 250px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="row align-items-start">
                    <div class="col-md-8">
                        <div class="card">
                            <h3 class="card-title"><i class="fas fa-check-circle"></i> Payment Successful</h3>
                            <p class="card-text"><i class="fas fa-info-circle"></i> Your payment has been successfully processed.</p>
                            <p class="card-text"><i class="fas fa-id-card"></i> <strong>Transaction ID:</strong> <?php echo htmlspecialchars($transaction['transaction_id']); ?></p>
                            <p class="card-text"><i class="fas fa-user"></i> <strong>User Name:</strong> <?php echo htmlspecialchars($userName); ?></p>
                            <p class="card-text"><i class="fas fa-map-marker-alt"></i> <strong>Start Location:</strong> <?php echo htmlspecialchars($transaction['start_location']); ?></p>
                            <p class="card-text"><i class="fas fa-map-marker-alt"></i> <strong>End Location:</strong> <?php echo htmlspecialchars($transaction['end_location']); ?></p>
                            <p class="card-text"><i class="fas fa-money-bill-wave"></i> <strong>Fare:</strong> BDT <?php echo htmlspecialchars($transaction['fare']); ?></p>
                            <p class="card-text"><i class="fas fa-clock"></i> <strong>Payment Time:</strong> <?php echo htmlspecialchars($transaction['created_at']); ?></p>
                            <img src="qrcodes/<?php echo htmlspecialchars($transaction['transaction_id']); ?>.png" alt="QR Code" class="qr-code">
                            
                            <a href="metro_generate_receipt.php?transaction_id=<?php echo htmlspecialchars($transaction['transaction_id']); ?>" class="btn btn-primary btn-block"><i class="fas fa-download"></i> Download Receipt</a>
                            <a href="metro.php" class="btn btn-secondary btn-block"><i class="fas fa-arrow-left"></i> Back to Metro Main Page</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="review-card">
                            <h5 class="card-title"><i class="fas fa-star"></i> Like our service?</h5>
                            <p class="card-text">Please leave a review and let us know your thoughts.</p>
                            <a href="review.php" class="btn btn-success btn-block"><i class="fas fa-star"></i> Leave a Review</a>
                        </div>
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

        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
    </script>
</body>
</html>