<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['transaction_id'])) {
    header('Location: train.php');
    exit;
}

$transaction_id = $_SESSION['transaction_id'];

// Fetch transaction details
$stmt = $conn2->prepare("SELECT t.*, tr.train_name, tr.start_point, tr.end_point, r.compartment_id, t.seats, r.reservation_date, tr.train_name 
                         FROM train_transactions t
                         JOIN reservations r ON t.train_id = r.train_id
                         JOIN trains tr ON r.train_id = tr.train_id
                         WHERE t.transaction_id = ?");
$stmt->bind_param("s", $transaction_id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    echo 'error: transaction not found';
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Confirmation - Mass Transport Ticketing System</title>
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
                        <p class="card-text"><strong>User Name:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                        <p class="card-text"><strong>Train:</strong> <?php echo htmlspecialchars($transaction['train_name']); ?></p>
                        <p class="card-text"><strong>Start Point:</strong> <?php echo htmlspecialchars($transaction['start_point']); ?></p>
                        <p class="card-text"><strong>End Point:</strong> <?php echo htmlspecialchars($transaction['end_point']); ?></p>
                        <p class="card-text"><strong>Compartment Number:</strong> <?php echo htmlspecialchars($transaction['compartment_id']); ?></p>
                        <p class="card-text"><strong>Seat Numbers:</strong> <?php echo htmlspecialchars($transaction['seats']); ?></p>
                        <p class="card-text"><strong>Reservation Date:</strong> <?php echo htmlspecialchars($transaction['reservation_date']); ?></p>
                        <p class="card-text"><strong>Amount Paid:</strong> BDT <?php echo htmlspecialchars($transaction['amount']); ?></p>
                        <p class="card-text"><strong>Payment Time:</strong> <?php echo htmlspecialchars($transaction['payment_time']); ?></p>
                        <a href="train_generate_receipt.php?transaction_id=<?php echo htmlspecialchars($transaction['transaction_id']); ?>" class="btn btn-primary btn-block"><i class="fas fa-download"></i> Download Receipt</a>
                        <a href="train.php" class="btn btn-secondary btn-block"><i class="fas fa-arrow-left"></i> Back to Train</a>
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

        // Note: Ensure there's an element with ID 'dark-mode-toggle' in nav.php or elsewhere
        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
    </script>
</body>
</html>