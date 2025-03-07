<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['reserved_seats']) || !isset($_SESSION['route_id'])) {
    header("Location: bus.php");
    exit;
}

$route_id = $_SESSION['route_id'];
$seat_numbers = $_SESSION['reserved_seats'];

// Get route details
$query = "SELECT r.id, r.origin, r.destination, r.fare, b.bus_name 
          FROM routes r 
          JOIN buses b ON b.id = ($route_id % 3 + 1) 
          WHERE r.id = $route_id";
$result = mysqli_query($conn3, $query);
$route = mysqli_fetch_assoc($result);

// Calculate the remaining time
$start_time = new DateTime();
$end_time = new DateTime($start_time->format('Y-m-d H:i:s'));
$end_time->add(new DateInterval('PT15M'));
$remaining_time = $end_time->getTimestamp() - $start_time->getTimestamp();

// If user clicks "Confirm Payment"
if (isset($_POST['confirm_payment'])) {
    foreach ($seat_numbers as $seat_number) {
        // Confirm the reservation
        $confirm_query = "UPDATE reservations 
                          SET is_confirmed = 1 
                          WHERE route_id = $route_id AND seat_number = $seat_number";
        mysqli_query($conn3, $confirm_query);
    }
    $message = "Payment confirmed! Your seats are reserved.";
    $message_type = "success";
    unset($_SESSION['reserved_seats']);
    unset($_SESSION['route_id']);
}

// If user clicks "Cancel Reservation"
if (isset($_POST['cancel_reservation'])) {
    foreach ($seat_numbers as $seat_number) {
        // Cancel the reservation
        $cancel_query = "DELETE FROM reservations 
                         WHERE route_id = $route_id AND seat_number = $seat_number AND is_confirmed = 0";
        mysqli_query($conn3, $cancel_query);
    }
    $message = "Reservation canceled.";
    $message_type = "error";
    unset($_SESSION['reserved_seats']);
    unset($_SESSION['route_id']);
    header("Location: bus.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Pending - <?php echo $route['bus_name']; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            animation: fadeIn 2s ease-in-out;
        }
        h1 {
            margin-top: 20px;
            text-align: center;
            color: #007bff;
            animation: fadeIn 2s ease-in-out;
        }
        p {
            text-align: center;
            color: #6c757d;
            animation: fadeIn 2s ease-in-out;
        }
        .card {
            margin: 20px auto;
            animation: fadeIn 2s ease-in-out;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
        }
        .message {
            text-align: center;
            margin-top: 20px;
        }
        .message.error {
            color: #dc3545;
        }
        .message.success {
            color: #28a745;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
    <script>
        // Countdown timer
        var remainingTime = <?php echo $remaining_time; ?>;
        function updateTimer() {
            var minutes = Math.floor(remainingTime / 60);
            var seconds = remainingTime % 60;
            document.getElementById('timer').innerText = minutes + "m " + seconds + "s";
            remainingTime--;
            if (remainingTime < 0) {
                document.getElementById('timer').innerText = "Reservation expired!";
                document.getElementById('confirm-payment-form').style.display = 'none';
                document.getElementById('cancel-reservation-form').style.display = 'none';
            }
        }
        setInterval(updateTimer, 1000);
    </script>
</head>
<body>
    <div class="container">
        <h1>Reservation Pending - <?php echo $route['bus_name']; ?></h1>
        <div class="card">
            <div class="card-body">
                <p>Bus: <b><?php echo $route['bus_name']; ?></b></p>
                <p>Trip: <b><?php echo $route['origin']; ?> to <?php echo $route['destination']; ?></b></p>
                <p>Fare: <b><?php echo $route['fare']; ?> BDT</b></p>
                <p>Reserved Seats: <b><?php echo implode(', ', $seat_numbers); ?></b></p>
                <p>Time remaining to confirm payment: <b id="timer"></b></p>

                <!-- Show message if there is one -->
                <?php if (isset($message)) { ?>
                    <p class="message <?php echo $message_type; ?>"><?php echo $message; ?></p>
                <?php } ?>

                <form method="POST" id="confirm-payment-form">
                    <button type="submit" name="confirm_payment" class="btn btn-success btn-block">Confirm Payment</button>
                </form>

                <form method="POST" id="cancel-reservation-form" class="mt-3">
                    <button type="submit" name="cancel_reservation" class="btn btn-danger btn-block">Cancel Reservation</button>
                </form>

                <p class="mt-3"><a href="bus.php">Back to Routes</a></p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>