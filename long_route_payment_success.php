<?php
// Start output buffering
ob_start();

session_start();
include 'db.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_log("Starting long_route_payment_success.php");

// Log the entire session for debugging
error_log("Session data on page load: " . print_r($_SESSION, true));

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in. Redirecting to login.php");
    $_SESSION['redirect_to'] = 'long_route.php';
    header('Location: login.php');
    exit;
}

// Check if payment success message exists
if (!isset($_SESSION['payment_success'])) {
    error_log("Payment success message not set in session. Redirecting to long_route.php");
    header('Location: long_route.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Parse the transaction ID from the payment success message
$payment_success_message = $_SESSION['payment_success'];
error_log("Payment success message: $payment_success_message");

preg_match('/Transaction ID: (txn-[A-F0-9]+)/', $payment_success_message, $matches);
if (!isset($matches[1])) {
    error_log("Failed to parse transaction ID from payment success message: $payment_success_message. Redirecting to long_route.php");
    header('Location: long_route.php');
    exit;
}
$transaction_id = $matches[1];
error_log("Parsed transaction ID: $transaction_id");

// Fetch transaction details from the database (using payment_transaction_id)
$transaction_query = "SELECT * FROM long_route_transactions WHERE payment_transaction_id = ? AND user_id = ?";
$stmt = $conn3->prepare($transaction_query);
$stmt->bind_param("si", $transaction_id, $user_id);
$stmt->execute();
$transaction_result = $stmt->get_result();
$transaction = $transaction_result->fetch_assoc();

if (!$transaction) {
    // Log all transactions with this payment_transaction_id for debugging
    $debug_query = "SELECT * FROM long_route_transactions WHERE payment_transaction_id = ?";
    $debug_stmt = $conn3->prepare($debug_query);
    $debug_stmt->bind_param("s", $transaction_id);
    $debug_stmt->execute();
    $debug_result = $debug_stmt->get_result();
    $debug_transactions = $debug_result->fetch_all(MYSQLI_ASSOC);
    error_log("All transactions with payment_transaction_id=$transaction_id: " . print_r($debug_transactions, true));
    error_log("Transaction not found for payment_transaction_id: $transaction_id, user_id: $user_id. Redirecting to long_route.php");
    header('Location: long_route.php');
    exit;
}

error_log("Transaction found: " . print_r($transaction, true));

// Ensure we have the journey date (from transaction or URL)
$journey_date = isset($transaction['journey_date']) ? $transaction['journey_date'] : 
               (isset($_GET['journey_date']) ? $_GET['journey_date'] : date('Y-m-d'));

// Log the journey date for debugging
error_log("Journey date for ticket: $journey_date");

// Fetch bus details
$bus_query = "SELECT * FROM long_route_buses WHERE bus_id = ?";
$stmt = $conn3->prepare($bus_query);
$stmt->bind_param("i", $transaction['bus_id']);
$stmt->execute();
$bus_result = $stmt->get_result();
$bus = $bus_result->fetch_assoc();

if (!$bus) {
    error_log("Bus not found for bus_id: {$transaction['bus_id']}. Redirecting to long_route.php");
    header('Location: long_route.php');
    exit;
}

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn1->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Clear the payment success message from session
unset($_SESSION['payment_success']);
error_log("Session payment_success unset. Proceeding to display success page");

$qrCodePath = 'qrcodes/' . $transaction_id . '.png';
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
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #50c878;
            --accent-color: #ff6b6b;
            --background-color: #f8f9fa;
            --text-color: #2c3e50;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
            --success-color: #28a745;
            --warning-color: #ffc107;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: all var(--transition-speed);
            margin-bottom: 1.5rem;
            overflow: hidden;
            animation: slideIn 0.6s ease-out forwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .card-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            border-radius: 50%;
            background-color: var(--success-color);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleUp 0.5s ease-in-out, pulse 2s infinite;
        }

        @keyframes scaleUp {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .success-checkmark i {
            color: white;
            font-size: 40px;
            animation: checkmark 0.5s ease-in-out;
        }

        @keyframes checkmark {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        .ticket {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
            animation: slideIn 0.6s ease-out forwards;
            border: 1px solid rgba(74, 144, 226, 0.1);
        }

        .ticket:before,
        .ticket:after {
            content: '';
            position: absolute;
            left: -4px;
            width: 8px;
            height: 8px;
            background: var(--background-color);
            border-radius: 50%;
            z-index: 1;
        }

        .ticket:before {
            top: -4px;
        }

        .ticket:after {
            bottom: -4px;
        }

        .ticket-header {
            border-bottom: 2px dashed rgba(74, 144, 226, 0.2);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .ticket-body {
            padding: 1rem 0;
        }

        .ticket-footer {
            border-top: 2px dashed rgba(74, 144, 226, 0.2);
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 0.9em;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all var(--transition-speed);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            background-color: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
        }

        .btn-success {
            background-color: var(--success-color);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all var(--transition-speed);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }

        .qr-code {
            border-radius: 8px;
            padding: 1rem;
            background: white;
            box-shadow: var(--card-shadow);
            transition: transform var(--transition-speed);
        }

        .qr-code:hover {
            transform: scale(1.02);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .ticket {
                padding: 1.5rem;
            }
        }

        /* Dark Mode Support */
        body.dark-mode {
            background-color: #121212;
            color: #ffffff;
        }

        body.dark-mode .card,
        body.dark-mode .ticket {
            background-color: #1e1e1e;
            border-color: #333;
        }

        body.dark-mode .ticket:before,
        body.dark-mode .ticket:after {
            background-color: #121212;
        }

        body.dark-mode .ticket-header,
        body.dark-mode .ticket-footer {
            border-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .qr-code {
            background-color: #2d2d2d;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1 class="text-center mt-4 mb-4">Payment Successful!</h1>
        
        <div class="card text-center mb-4">
            <div class="card-body">
                <div class="success-checkmark mb-3">
                    <i class="fas fa-check"></i>
                </div>
                <h3 class="card-title">Thank You!</h3>
                <p class="card-text">Your payment has been processed successfully and your bus ticket has been confirmed.</p>
                <p class="card-text">Transaction ID: <strong><?= htmlspecialchars($transaction['payment_transaction_id']) ?></strong></p>
                <p class="card-text">A confirmation email has been sent to your registered email address.</p>
                <?php if (file_exists($qrCodePath)): ?>
                    <p><strong>Scan the QR Code below for your ticket details:</strong></p>
                    <img src="<?= htmlspecialchars($qrCodePath) ?>" alt="QR Code" style="width: 300px; height: 300px;">
                <?php endif; ?>
            </div>
        </div>
        
        <div class="ticket">
            <div class="ticket-header">
                <div class="row">
                    <div class="col-md-8">
                        <h3>Bus Ticket</h3>
                    </div>
                    <div class="col-md-4 text-right">
                        <h5>Transaction ID: <?= substr($transaction['payment_transaction_id'], 0, 8) ?></h5>
                    </div>
                </div>
            </div>
            
            <div class="ticket-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Bus:</strong> <?= htmlspecialchars($bus['bus_name']) ?></p>
                        <p><strong>From:</strong> <?= htmlspecialchars($bus['from_location']) ?></p>
                        <p><strong>To:</strong> <?= htmlspecialchars($bus['to_location']) ?></p>
                        <p><strong>Passenger:</strong> <?= htmlspecialchars($user['name']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date:</strong> <?= date('l, F j, Y', strtotime($journey_date)) ?></p>
                        <p><strong>Departure:</strong> <?= date('h:i A', strtotime($bus['departure_time'])) ?></p>
                        <p><strong>Bus Type:</strong> <?= htmlspecialchars($bus['bus_type']) ?></p>
                        <p><strong>Seat(s):</strong> <?= htmlspecialchars($transaction['seat_numbers']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="ticket-footer">
                <div class="row">
                    <div class="col-md-6">
                        <p>Total Amount Paid: BDT <?= number_format($transaction['amount'], 2) ?></p>
                    </div>
                    <div class="col-md-6 text-right">
                        <p><i class="fas fa-info-circle"></i> Please arrive 30 minutes before departure</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mb-4">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Return to Home
            </a>
            <button class="btn btn-success ml-2" onclick="window.print()">
                <i class="fas fa-print"></i> Print Ticket
            </button>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Dark mode toggle functionality
        function toggleDarkMode() {
            const body = document.body;
            const isDarkMode = body.classList.contains('dark-mode');
            
            if (isDarkMode) {
                body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            } else {
                body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            }
            
            // Update icon
            const icon = document.querySelector('.dark-mode-toggle i');
            if (icon) {
                icon.className = isDarkMode ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        // Check for saved theme preference
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const body = document.body;
            
            if (savedTheme === 'dark') {
                body.classList.add('dark-mode');
            } else {
                body.classList.remove('dark-mode');
            }

            // Add dark mode toggle button if it doesn't exist
            if (!document.querySelector('.dark-mode-toggle')) {
                const toggleButton = document.createElement('button');
                toggleButton.className = 'dark-mode-toggle';
                toggleButton.innerHTML = '<i class="fas fa-moon"></i>';
                toggleButton.onclick = toggleDarkMode;
                document.body.appendChild(toggleButton);
            }

            // Update initial icon
            const icon = document.querySelector('.dark-mode-toggle i');
            if (icon) {
                icon.className = body.classList.contains('dark-mode') ? 'fas fa-sun' : 'fas fa-moon';
            }
        });
    </script>
</body>
</html>