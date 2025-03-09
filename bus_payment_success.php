<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$transaction_id = $_GET['transaction_id'];

// Fetch transaction details
$query = "SELECT * FROM bus_transactions WHERE transaction_id = ?";
$stmt = $conn3->prepare($query);
$stmt->bind_param("s", $transaction_id);
$stmt->execute();
$result = $stmt->get_result();
$transaction = $result->fetch_assoc();

if (!$transaction) {
    echo "Transaction not found.";
    exit;
}

// Generate QR code
require 'phpqrcode/qrlib.php';

$qrContent = 'Transaction ID: ' . $transaction['transaction_id'] . "\n" .
             'Name: ' . $_SESSION['user_name'] . "\n" .
             'Fare: ' . $transaction['amount'] . " BDT\n" .
             'Origin: ' . $transaction['origin'] . "\n" .
             'Destination: ' . $transaction['destination'] . "\n" .
             'Bus: ' . $transaction['bus_name'];

$qrFilePath = 'qrcodes/' . $transaction_id . '.png';
QRcode::png($qrContent, $qrFilePath, QR_ECLEVEL_L, 5);


$_SESSION['payment_completed'] = true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Mass Transport Ticketing System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            animation: fadeIn 2s ease-in-out;
        }
        body.dark-mode {
            background-color: #121212;
            color: #ffffff;
        }
        h1 {
            margin-top: 20px;
            text-align: center;
            color: #007bff;
            animation: fadeIn 2s ease-in-out;
        }
        body.dark-mode h1 {
            color: #bb86fc;
        }
        .card {
            margin: 20px auto;
            animation: fadeIn 2s ease-in-out;
            transition: transform 0.3s, box-shadow 0.3s;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 300px; /* Make the card smaller */
        }
        body.dark-mode .card {
            background: rgba(18, 18, 18, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
        }
        body.dark-mode .card:hover {
            box-shadow: 0 0 30px rgba(187, 134, 252, 0.4);
        }
        .card-body {
            color: #000000;
        }
        body.dark-mode .card-body {
            color: #ffffff;
        }
        .card-title, .card-text {
            color: inherit;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
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
        .icon {
            font-size: 50px;
            color: #007bff;
            animation: bounce 2s infinite;
        }
        body.dark-mode .icon {
            color: #bb86fc;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1>Payment Successful</h1>
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-check-circle icon"></i>
                <p class="card-text">Transaction ID: <?php echo htmlspecialchars($transaction['transaction_id']); ?></p>
                <p class="card-text">Bus: <?php echo htmlspecialchars($transaction['bus_name']); ?></p>
                <p class="card-text">Fare: <?php echo htmlspecialchars($transaction['amount']); ?> BDT</p>
                <p class="card-text">Origin: <?php echo htmlspecialchars($transaction['origin']); ?></p>
                <p class="card-text">Destination: <?php echo htmlspecialchars($transaction['destination']); ?></p>
                <img src="qrcodes/<?php echo htmlspecialchars($transaction['transaction_id']); ?>.png" alt="QR Code">
                <a href="bus_download_receipt.php?transaction_id=<?php echo htmlspecialchars($transaction['transaction_id']); ?>" class="btn btn-primary">Download Receipt</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('dark-mode', document.body.classList.contains('dark-mode'));
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('dark-mode') === 'true') {
                document.body.classList.add('dark-mode');
            }

            // Prevent going back to the previous page
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }

            // Redirect to bus_select_local_route if user tries to go back
            window.addEventListener('popstate', function(event) {
                window.location.href = 'bus_select_type.php';
            });
        });

        // Note: Ensure there's an element with ID 'dark-mode-toggle' in nav.php or elsewhere
        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
    </script>
</body>
</html>