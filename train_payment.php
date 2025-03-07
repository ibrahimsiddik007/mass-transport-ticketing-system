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
    $amount = $reservation['fare'];

    // Process payment (dummy processing for demonstration)
    $payment_success = true; // Assume payment is successful

    if ($payment_success) {
        // Update reservation status to 'paid'
        $update_stmt = $conn2->prepare("UPDATE reservations SET status = 'paid' WHERE user_id = ? AND status = 'pending'");
        $update_stmt->bind_param("i", $user_id);
        $update_stmt->execute();

        // Insert transaction details into transactions table
        $transaction_id = 'txn_' .substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 7);
        $payment_time = date('Y-m-d H:i:s'); // Get the current date and time
        $amount = $reservation['fare'];
        $compartment_ID = $reservation['compartment_id'];
        $train_id = $reservation['train_id'];
        $seats = $seat_numbers_str;
        $departure_time = $reservation['departure_time'];

        $insert_stmt = $conn2->prepare("INSERT INTO train_transactions (transaction_id, user_id, amount, compartment_ID, train_ID, seats, payment_time, departure_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("siiiisss", $transaction_id, $user_id, $amount, $compartment_ID, $train_id, $seats, $payment_time, $departure_time);
        $insert_stmt->execute();

        $_SESSION['payment_completed'] = true;
        $_SESSION['transaction_id'] = $transaction_id;
        header('Location: train_payment_confirmation.php?success=true');
        exit;
    } else {
        $error_message = "Payment failed. Please try again.";
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
            background: rgba(172, 187, 107, 0.8); /* Slightly transparent background */
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
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
        <?php endif; ?>
        <div class="row justify-content-start"> <!-- Changed justify-content-center to justify-content-start -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title"><i class="fas fa-train"></i> Payment Information</h3>
                        <p class="card-text"><strong><i class="fas fa-train"></i> Train:</strong> <?php echo $reservation['train_name']; ?></p>
                        <p class="card-text"><strong><i class="fas fa-th"></i> Compartment Number:</strong> <?php echo $reservation['compartment_id']; ?></p>
                        <p class="card-text"><strong><i class="fas fa-chair"></i> Seat Numbers:</strong> <?php echo $seat_numbers_str; ?></p>
                        <p class="card-text"><strong><i class="fas fa-map-marker-alt"></i> Start Point:</strong> <?php echo $reservation['start_point']; ?></p>
                        <p class="card-text"><strong><i class="fas fa-map-marker-alt"></i> End Point:</strong> <?php echo $reservation['end_point']; ?></p>
                        <p class="card-text"><strong><i class="fas fa-money-bill-wave"></i> Fare:</strong> BDT <?php echo number_format($reservation['fare'], 2); ?></p>
                        <form method="POST">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-credit-card"></i> Pay Now</button>
                            <a href="train.php" class="btn btn-secondary btn-block"><i class="fas fa-times"></i> Cancel</a>
                        </form>
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

        // Note: Ensure there's an element with ID 'dark-mode-toggle' in nav.php or elsewhere
        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
    </script>
</body>
</html>