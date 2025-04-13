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
            --dropdown-bg: #2a2a2a;
            --dropdown-text: #ffffff;
            --dropdown-hover: #3a3a3a;
        }

        body {
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
            background: url('images/Metro_Ticket_Counter.jpg') no-repeat center center fixed;
            background-size: cover;
            filter: brightness(0.6) contrast(1.1);
            z-index: -2;
        }

        body::after {
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
            max-width: 600px;
            margin: 2rem auto;
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

        .card h2 {
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

        .card h2 i {
            color: var(--secondary-color);
            font-size: 2.8rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group label i {
            color: var(--primary-color);
            font-size: 1.3rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 15px 20px;
            color: #fff;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            height: auto;
            min-height: 50px;
            line-height: 1.5;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            box-shadow: 0 0 15px rgba(74, 144, 226, 0.3);
            transform: translateY(-2px);
            color: #fff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        /* Dropdown Menu Styles */
        select.form-control {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 20px;
            padding-right: 45px;
        }

        select.form-control option {
            background-color: var(--dropdown-bg);
            color: var(--dropdown-text);
            padding: 12px 15px;
            font-size: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        select.form-control option:hover {
            background-color: var(--dropdown-hover);
            color: #fff;
        }

        select.form-control option:checked {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
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

        /* Dark Mode Adjustments */
        body.dark-mode::before {
            filter: brightness(0.7) contrast(1.1);
        }

        body.dark-mode::after {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4));
        }

        body.dark-mode .card {
            background: rgba(0, 0, 0, 0.6);
        }

        body.dark-mode .form-control {
            background: rgba(255, 255, 255, 0.15);
        }

        body.dark-mode .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .card {
                padding: 30px;
                margin: 20px;
            }

            .card h2 {
                font-size: 2rem;
            }

            .form-group label {
                font-size: 1rem;
            }

            .form-control {
                padding: 10px 15px;
                font-size: 1rem;
            }

            .btn-primary, .btn-secondary {
                padding: 10px 20px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <h2><i class="fas fa-credit-card"></i> Confirm Payment</h2>
                    <form action="payment_success.php" method="POST">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token']) ?>">
                        <div class="form-group">
                            <label for="startLocation"><i class="fas fa-map-marker-alt"></i> Start Location:</label>
                            <input type="text" class="form-control" id="startLocation" name="startLocation" value="<?= htmlspecialchars($startLocation) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="endLocation"><i class="fas fa-map-marker-alt"></i> End Location:</label>
                            <input type="text" class="form-control" id="endLocation" name="endLocation" value="<?= htmlspecialchars($endLocation) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="fare"><i class="fas fa-money-bill-wave"></i> Fare (BDT):</label>
                            <input type="text" class="form-control" id="fare" name="fare" value="<?= htmlspecialchars($fare) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="paymentMethod"><i class="fas fa-credit-card"></i> Payment Method:</label>
                            <select class="form-control" id="paymentMethod" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="bkash">bKash</option>
                                <option value="rocket">Rocket</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                        <div id="paymentFields"></div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Pay Now</button>
                            <a href="metro.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                        </div>
                    </form>
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
                paymentFields.innerHTML = '';

                if (selectedMethod === 'bkash') {
                    paymentFields.innerHTML = `
                        <div class="form-group">
                            <label for="bkashNumber"><i class="fas fa-mobile-alt"></i> bKash Number:</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Enter your bKash number" required>
                        </div>
                        <div class="form-group">
                            <label for="bkashTrxID"><i class="fas fa-lock"></i> PIN:</label>
                            <input type="password" class="form-control" id="pin" name="pin" placeholder="Enter bKash PIN" required>
                        </div>
                    `;
                } else if (selectedMethod === 'rocket') {
                    paymentFields.innerHTML = `
                        <div class="form-group">
                            <label for="rocketNumber"><i class="fas fa-mobile-alt"></i> Rocket Number:</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Enter your Rocket number" required>
                        </div>
                        <div class="form-group">
                            <label for="rocketTrxID"><i class="fas fa-lock"></i> PIN:</label>
                            <input type="password" class="form-control" id="pin" name="pin" placeholder="Enter your Rocket PIN" required>
                        </div>
                    `;
                } else if (selectedMethod === 'card') {
                    paymentFields.innerHTML = `
                        <div class="form-group">
                            <label for="cardNumber"><i class="fas fa-credit-card"></i> Card Number:</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Enter your card number" required>
                        </div>
                        <div class="form-group">
                            <label for="cardExpiry"><i class="fas fa-calendar-alt"></i> Expiry Date:</label>
                            <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YY" required>
                        </div>
                        <div class="form-group">
                            <label for="cardCVV"><i class="fas fa-lock"></i> CVV:</label>
                            <input type="text" class="form-control" id="pin" name="pin" placeholder="Enter CVV" required>
                        </div>
                    `;
                }
            });
        });
    </script>
</body>
</html>