<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'metro.php';
    header('Location: login.php');
    exit;
}

// Check if the payment has already been completed
if (isset($_SESSION['payment_completed']) && $_SESSION['payment_completed'] === true) {
    header('Location: metro.php');
    exit;
}

$startLocation = $_GET['startLocation'];
$endLocation = $_GET['endLocation'];
$fare = $_GET['fare'];

// Generate a token to prevent CSRF
$_SESSION['token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Payment</title>
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
        .card {
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
        .btn-primary, .btn-secondary {
            transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        .btn-primary:hover {
            transform: scale(1.05);
            color: red;
        }
        .btn-secondary {
            background-color: #ffcccc;
            border-color: #ffcccc;
            color: #000;
        }
        .btn-secondary:hover {
            background-color: red;
            border-color: red;
            transform: scale(1.05);
            color: #fff;
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
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5">
                    <div class="card-body">
                        <h2 class="text-center">Confirm Payment</h2>
                        <form action="payment_success.php" method="POST">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token']) ?>">
                            <div class="form-group">
                                <label for="startLocation">Start Location:</label>
                                <input type="text" class="form-control" id="startLocation" name="startLocation" value="<?= htmlspecialchars($startLocation) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="endLocation">End Location:</label>
                                <input type="text" class="form-control" id="endLocation" name="endLocation" value="<?= htmlspecialchars($endLocation) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="fare">Fare (BDT):</label>
                                <input type="text" class="form-control" id="fare" name="fare" value="<?= htmlspecialchars($fare) ?>" readonly>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">Pay Now</button>
                                <a href="metro.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
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