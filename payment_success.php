<?php
session_start();
include 'db.php'; // Include your database connection file

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

    // Simulate payment success
    $paymentStatus = "success";

    // Generate unique transaction_id
    $transactionId = 'txn_' . substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 7);

    // Get user_id from session
    $userId = $_SESSION['user_id'];

    // Get current timestamp
    $createdAt = date('Y-m-d H:i:s');

    // Save transaction details
    $stmt = $conn1->prepare("INSERT INTO transactions (transaction_id, user_id, start_location, end_location, fare, status, created_at) VALUES (?, ?, ?, ?, ?, 'paid', ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn1->error));
    }
    $stmt->bind_param("ssssss", $transactionId, $userId, $startLocation, $endLocation, $fare, $createdAt);
    $stmt->execute();
    if ($stmt->affected_rows === 0) {
        die('Insert failed: ' . htmlspecialchars($stmt->error));
    }
    $stmt->close();

    // Store transaction ID in session for receipt generation
    $_SESSION['transaction_id'] = $transactionId;

    // Redirect to payment success page
    header('Location: payment_success.php?success=true');
    exit;
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
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="card-title"><i class="fas fa-check-circle"></i> Payment Successful</h3>
                <p class="card-text">Your payment has been successfully processed.</p>
                <a href="generate_receipt.php" class="btn btn-primary btn-block"><i class="fas fa-download"></i> Download Receipt</a>
                <a href="metro.php" class="btn btn-secondary btn-block"><i class="fas fa-arrow-left"></i> Back to Metro</a>
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

        // Note: Ensure there's an element with ID 'dark-mode-toggle' in nav.php or elsewhere
        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>