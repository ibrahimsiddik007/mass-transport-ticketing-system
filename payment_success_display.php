<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'metro.php';
    header('Location: login.php');
    exit;
}

// Ensure the payment was completed
if (!isset($_SESSION['payment_completed']) || $_SESSION['payment_completed'] !== true) {
    header('Location: metro.php');
    exit;
}

// Get transaction details
if (!isset($_GET['transaction_id'])) {
    header('Location: metro.php');
    exit;
}

$transactionId = $_GET['transaction_id'];
$stmt = $conn1->prepare("SELECT start_location, end_location, fare, created_at FROM transactions WHERE transaction_id = ?");
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn1->error));
}
$stmt->bind_param("s", $transactionId);
$stmt->execute();
$stmt->bind_result($startLocation, $endLocation, $fare, $createdAt);
$stmt->fetch();
$stmt->close();

// Debugging: Check if the transaction details were fetched correctly
if (empty($startLocation) || empty($endLocation) || empty($fare) || empty($createdAt)) {
    die('Transaction not found. Transaction ID: ' . htmlspecialchars($transactionId));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            color: #fff;
            animation: fadeIn 2s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .success-box {
            background: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            animation: slideIn 1s ease-in-out;
        }
        @keyframes slideIn {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .btn-primary {
            transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        .btn-primary:hover {
            transform: scale(1.05);
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/Metro_Ticket_Counter.jpg') no-repeat center center fixed;
            background-size: cover;
            filter: brightness(50%);
            z-index: -1;
        }
    </style>
    <script>
        // Prevent back navigation
        function preventBack() {
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function() {
                window.history.pushState(null, "", window.location.href);
            };
        }
        preventBack();
        window.onunload = function () { null };
    </script>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="success-box mt-5 text-center">
                    <h2 class="text-center"><i class="fas fa-check-circle"></i> Payment Successful</h2>
                    <p><strong>Start Location:</strong> <?= htmlspecialchars($startLocation) ?></p>
                    <p><strong>End Location:</strong> <?= htmlspecialchars($endLocation) ?></p>
                    <p><strong>Fare:</strong> <?= htmlspecialchars($fare) ?> BDT</p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($createdAt) ?></p>
                    <p class="text-center">Thank you for your payment. Your ticket has been successfully purchased.</p>
                    <div class="d-flex justify-content-center mt-3">
                        <a href="receipts/<?= htmlspecialchars($transactionId) ?>.pdf" download class="btn btn-primary mr-2"><i class="fas fa-download"></i> Download Receipt</a>
                        <a href="metro.php" class="btn btn-primary"><i class="fas fa-home"></i> Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>