<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch pending reservation details
$query = "SELECT * FROM reservations WHERE user_id = ? AND status = 'pending' AND expiry_time > NOW()";
$stmt = $conn2->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$reservations = [];
while ($row = $result->fetch_assoc()) {
    $reservations[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Status</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('images/Railway Background Image.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff;
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
        }

        .card {
            background-color: rgba(0, 0, 0, 0.8);
            border: none;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0, 123, 255, 0.3);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 0 40px rgba(0, 123, 255, 0.5);
        }

        .timer {
            font-size: 2.5rem;
            color: #ff6b6b;
            font-weight: bold;
            text-shadow: 0 0 10px rgba(255, 107, 107, 0.5);
            animation: pulse 1.5s infinite;
            font-family: 'Digital', monospace;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                text-shadow: 0 0 10px rgba(255, 107, 107, 0.5);
            }
            50% {
                transform: scale(1.1);
                text-shadow: 0 0 20px rgba(255, 107, 107, 0.8);
            }
            100% {
                transform: scale(1);
                text-shadow: 0 0 10px rgba(255, 107, 107, 0.5);
            }
        }

        .btn-primary, .btn-secondary {
            border-radius: 15px;
            padding: 12px 30px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(45deg, #2196F3, #1976D2);
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(45deg, #6c757d, #495057);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
        }

        .btn-primary:hover, .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .btn-primary:active, .btn-secondary:active {
            transform: translateY(1px);
        }

        .container {
            margin-top: 100px;
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-content {
            background-color: rgba(0, 0, 0, 0.9);
            color: #fff;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .modal-header, .modal-footer {
            border: none;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .close {
            color: #fff;
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .close:hover {
            opacity: 1;
            transform: rotate(90deg);
        }

        .card-body {
            padding: 2.5rem;
        }

        .card-body h2 {
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(45deg, #fff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .card-body p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .mt-3 {
            margin-top: 2rem !important;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        
        <div class="card mx-auto" style="max-width: 600px;">
            <div class="card-body text-center">
            <h2>Reservation Done!!!</h2>
                <p>Your reservation is pending. Please pay within the below minutes or the seats will be released.</p>
                <p>Time left: <span id="time-left" class="timer">15:00</span></p>
                <div class="mt-3">
                    <button type="button" class="btn btn-primary" id="payNowBtn">Pay Now</button>
                    <button type="button" class="btn btn-secondary" id="cancelReservationBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timeLeftDisplay = document.getElementById('time-left');
            let timer;

            function startTimer(duration) {
                let timer = duration, minutes, seconds;
                setInterval(function() {
                    minutes = parseInt(timer / 60, 10);
                    seconds = parseInt(timer % 60, 10);

                    minutes = minutes < 10 ? "0" + minutes : minutes;
                    seconds = seconds < 10 ? "0" + seconds : seconds;

                    timeLeftDisplay.textContent = minutes + ":" + seconds;

                    if (--timer < 0) {
                        clearInterval(timer);
                        alert('Reservation time expired!');
                        cancelReservationBtn.click();
                    }
                }, 1000);
            }

            // Fetch remaining time from the server
            $.ajax({
                url: 'check_pending_reservation.php',
                method: 'GET',
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.status === 'pending') {
                        const remainingTime = data.remaining_time;
                        startTimer(remainingTime);
                    } else {
                        alert('No pending reservation found.');
                        window.location.href = 'train.php';
                    }
                }
            });

            const payNowBtn = document.getElementById('payNowBtn');
            const cancelReservationBtn = document.getElementById('cancelReservationBtn');

            payNowBtn.addEventListener('click', function() {
                window.location.href = 'train_payment.php';
            });

            cancelReservationBtn.addEventListener('click', function() {
                $.ajax({
                    url: 'cancel_reservation.php',
                    method: 'POST',
                    success: function(response) {
                        if (response === 'ok') {
                            alert('Reservation cancelled.');
                            window.location.href = 'train.php';
                        } else {
                            alert('Cancellation failed.');
                        }
                    }
                });
            });
        });
    </script>
    
</body>
</html>