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
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #50c878;
            --accent-color: #ff6b6b;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            --transition-speed: 0.3s;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/Metro_Ticket_Counter.jpg') no-repeat center center fixed;
            background-size: cover;
            filter: brightness(0.7) contrast(1.2);
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6));
            z-index: -1;
        }

        body {
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            animation: fadeIn 1.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .confirmation-box {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
            animation: slideIn 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .confirmation-box::before {
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

        .confirmation-box h2 {
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 30px;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .confirmation-box h2 i {
            font-size: 2.5rem;
            color: var(--secondary-color);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .ticket-details {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            animation: fadeIn 0.8s ease-out 0.3s backwards;
        }

        .ticket-details p {
            font-size: 1.2rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
        }

        .ticket-details p i {
            font-size: 1.4rem;
            color: var(--primary-color);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .btn-primary, .btn-danger {
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            border: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #357abd);
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--accent-color), #d93025);
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-primary::before, .btn-danger::before {
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

        .btn-primary:hover, .btn-danger:hover {
            transform: translateY(-5px) scale(1.02);
        }

        .btn-primary:hover::before, .btn-danger:hover::before {
            transform: translateX(100%);
        }

        .btn-primary i, .btn-danger i {
            margin-right: 10px;
            animation: float 2s ease-in-out infinite;
        }

        .btn-primary:hover {
            box-shadow: 0 8px 25px rgba(74, 144, 226, 0.4);
        }

        .btn-danger:hover {
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .confirmation-box {
                padding: 25px;
                margin: 20px;
            }

            .confirmation-box h2 {
                font-size: 1.8rem;
            }

            .ticket-details {
                padding: 20px;
            }

            .ticket-details p {
                font-size: 1.1rem;
            }

            .btn-primary, .btn-danger {
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
            <div class="col-md-8 col-lg-6">
                <div class="confirmation-box mt-5">
                    <h2><i class="fas fa-ticket-alt"></i> Confirm Ticket</h2>
                    <div class="ticket-details">
                        <p><i class="fas fa-map-marker-alt"></i> <strong>Start Location:</strong> <?= htmlspecialchars($startLocation) ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <strong>End Location:</strong> <?= htmlspecialchars($endLocation) ?></p>
                        <p><i class="fas fa-money-bill-wave"></i> <strong>Fare:</strong> <?= htmlspecialchars($fare) ?> BDT</p>
                    </div>
                    <form action="payment.php" method="POST" class="text-center">
                        <input type="hidden" name="startLocation" value="<?= htmlspecialchars($startLocation) ?>">
                        <input type="hidden" name="endLocation" value="<?= htmlspecialchars($endLocation) ?>">
                        <input type="hidden" name="fare" value="<?= htmlspecialchars($fare) ?>">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Proceed to Payment</button>
                        <a href="metro.php" class="btn btn-danger ml-3"><i class="fas fa-times"></i> Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>