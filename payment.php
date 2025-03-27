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

$startLocation = $_POST['startLocation'];
$endLocation = $_POST['endLocation'];
$fare = $_POST['fare'];

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
            font-family: Arial, sans-serif;
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
                            <div class="form-group">
                                <label for="paymentMethod">Payment Method:</label>
                                <select class="form-control" id="paymentMethod" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="bkash">bKash</option>
                                    <option value="rocket">Rocket</option>
                                    <option value="card">Card</option>
                                </select>
                            </div>
                            <div id="paymentFields"></div> <!-- Placeholder for dynamically generated fields -->
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paymentMethod = document.getElementById('paymentMethod');
            const paymentFields = document.getElementById('paymentFields');

            paymentMethod.addEventListener('change', function () {
                const selectedMethod = paymentMethod.value;
                paymentFields.innerHTML = ''; // Clear previous fields

                if (selectedMethod === 'bkash') {
                    paymentFields.innerHTML = `
                        <div class="form-group">
                            <label for="bkashNumber">bKash Number:</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Enter your bKash number" required>
                        </div>
                        <div class="form-group">
                            <label for="bkashTrxID">PIN :</label>
                            <input type="password" class="form-control" id="pin" name="pin" placeholder="Enter bKash PIN" required>
                        </div>
                    `;
                } else if (selectedMethod === 'rocket') {
                    paymentFields.innerHTML = `
                        <div class="form-group">
                            <label for="rocketNumber">Rocket Number:</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Enter your Rocket number" required>
                        </div>
                        <div class="form-group">
                            <label for="rocketTrxID">Pin :</label>
                            <input type="password" class="form-control" id="pin" name="pin" placeholder="Enter your Rocket PIN" required>
                        </div>
                    `;
                } else if (selectedMethod === 'card') {
                    paymentFields.innerHTML = `
                        <div class="form-group">
                            <label for="cardNumber">Card Number:</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Enter your card number" required>
                        </div>
                        <div class="form-group">
                            <label for="cardExpiry">Expiry Date:</label>
                            <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YY" required>
                        </div>
                        <div class="form-group">
                            <label for="cardCVV">CVV:</label>
                            <input type="text" class="form-control" id="pin" name="pin" placeholder="Enter CVV" required>
                        </div>
                    `;
                }
            });
        });
    </script>
</body>
</html>