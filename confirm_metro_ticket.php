<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'metro.php';
    header('Location: login.php');
    exit;
}

// Check if payment has already been completed
if (isset($_SESSION['payment_completed']) && $_SESSION['payment_completed'] === true) {
    header('Location: metro.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $startLocation = $_POST['startLocation'];
    $endLocation = $_POST['endLocation'];
    $fare = $_POST['fare'];
} else {
    header('Location: metro.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Ticket</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/Metro_Ticket_Counter.jpg') no-repeat center center fixed;
            background-size: cover;
            background-color: rgba(0, 0, 0, 0.9); /* Add a black overlay with 50% opacity */
            z-index: -1;
        }
        body {
            color: #fff;
            animation: fadeIn 2s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .confirmation-box {
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
        .btn-primary, .btn-danger {
            transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        .btn-primary:hover, .btn-danger:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="confirmation-box mt-5">
                    <h2 class="text-center"><i class="fas fa-ticket-alt"></i> Confirm Ticket</h2>
                    <p><strong>Start Location:</strong> <?= htmlspecialchars($startLocation) ?></p>
                    <p><strong>End Location:</strong> <?= htmlspecialchars($endLocation) ?></p>
                    <p><strong>Fare:</strong> <?= htmlspecialchars($fare) ?> BDT</p>
                    <div class="text-center">
                        <a href="metro.php" class="btn btn-danger"><i class="fas fa-times"></i> Cancel</a>
                        <a href="payment.php?startLocation=<?= urlencode($startLocation) ?>&endLocation=<?= urlencode($endLocation) ?>&fare=<?= urlencode($fare) ?>" class="btn btn-primary"><i class="fas fa-check"></i> Proceed to Payment</a>
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