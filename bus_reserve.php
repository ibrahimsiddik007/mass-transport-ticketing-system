<?php
session_start();
// Include the database connection
include 'db.php';

// Check if a route ID is given
if (!isset($_GET['route_id'])) {
    header("Location: bus.php"); // Go back if no route
    exit;
}

$route_id = $_GET['route_id'];

// Get route and bus details
$query = "SELECT r.id, r.origin, r.destination, r.fare, b.bus_name, b.capacity 
          FROM routes r 
          JOIN buses b ON b.id = ($route_id % 3 + 1) 
          WHERE r.id = $route_id";
$result = mysqli_query($conn3, $query);
$route = mysqli_fetch_assoc($result);

// If route doesn’t exist, go back
if (!$route) {
    header("Location: bus.php");
    exit;
}

// Clean up old reservations (15 minutes = 900 seconds)
$cleanup_query = "DELETE FROM reservations 
                  WHERE route_id = $route_id 
                  AND is_confirmed = 0 
                  AND TIMESTAMPDIFF(SECOND, reserved_at, NOW()) > 900";
mysqli_query($conn3, $cleanup_query);

// Get all reserved seats
$seats_query = "SELECT seat_number 
                FROM reservations 
                WHERE route_id = $route_id 
                AND (is_confirmed = 1 OR TIMESTAMPDIFF(HOUR, reserved_at, NOW()) < 5)";
$seats_result = mysqli_query($conn3, $seats_query);
$reserved_seats = [];
while ($row = mysqli_fetch_assoc($seats_result)) {
    $reserved_seats[] = $row['seat_number'];
}

// If user clicks "Reserve Seat"
if (isset($_POST['reserve'])) {
    $seat_numbers = $_POST['seat_numbers'];
    $user_name = $_POST['user_name'];
    $just_reserved = false;

    foreach ($seat_numbers as $seat_number) {
        // Check if seat is taken
        if (in_array($seat_number, $reserved_seats)) {
            $message = "Sorry, seat $seat_number is already taken!";
            $message_type = "error";
            break;
        } else {
            // Save the reservation
            $save_query = "INSERT INTO reservations (route_id, seat_number, user_name, reserved_at, is_confirmed) 
                           VALUES ($route_id, $seat_number, '$user_name', NOW(), 0)";
            mysqli_query($conn3, $save_query);

            // Calculate the 5-hour window
            $start_time = date('Y-m-d H:i:s'); // Now
            $end_time = date('Y-m-d H:i:s', strtotime($start_time . ' + 5 hours'));

            $message = "Seats reserved for 15 minutes! Travel between $start_time and $end_time. Pay soon!";
            $message_type = "success";
            $reserved_seats[] = $seat_number;
            $just_reserved = true; // Show payment button
        }
    }
}

// If user clicks "Confirm Payment"
if (isset($_POST['confirm_payment'])) {
    $seat_numbers = $_POST['confirm_seat_numbers'];

    foreach ($seat_numbers as $seat_number) {
        // Confirm the reservation
        $confirm_query = "UPDATE reservations 
                          SET is_confirmed = 1 
                          WHERE route_id = $route_id AND seat_number = $seat_number";
        mysqli_query($conn3, $confirm_query);

        // Get the reservation time
        $time_query = "SELECT reserved_at 
                       FROM reservations 
                       WHERE route_id = $route_id AND seat_number = $seat_number";
        $time_result = mysqli_query($conn3, $time_query);
        $start_time = mysqli_fetch_assoc($time_result)['reserved_at'];
        $end_time = date('Y-m-d H:i:s', strtotime($start_time . ' + 5 hours'));

        $message = "Paid {$route['fare']} BDT for seat $seat_number! Travel between $start_time and $end_time.";
        $message_type = "success";
    }
}

// Set up seat layout
$total_seats = $route['capacity'];
$seats_per_side = 2;
$rows = ceil($total_seats / ($seats_per_side * 2));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Seat - <?php echo $route['bus_name']; ?></title>
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
        .seat-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .bus-layout {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .seat-row {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }
        .seat {
            margin: 0 5px;
        }
        .seat input {
            display: none;
        }
        .seat span {
            display: inline-block;
            width: 30px;
            height: 30px;
            background-color: #007bff;
            color: #fff;
            text-align: center;
            line-height: 30px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }
        .seat input:checked + span {
            background-color: #28a745;
        }
        .seat span:hover {
            background-color: #0056b3;
        }
        .seat span.disabled {
            background-color: #dc3545;
            cursor: not-allowed;
        }
        .aisle {
            width: 20px;
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
</head>
<body>
    <div class="container">
        <h1>Book a Seat - Dhaka Buses</h1>
        <div class="card">
            <div class="card-body">
                <p>Bus: <b><?php echo $route['bus_name']; ?></b></p>
                <p>Trip: <b><?php echo $route['origin']; ?> to <?php echo $route['destination']; ?></b></p>
                <p>Fare: <b><?php echo $route['fare']; ?> BDT</b></p>
                <p>Travel anytime in 5 hours after reserving!</p>

                <!-- Show message if there is one -->
                <?php if (isset($message)) { ?>
                    <p class="message <?php echo $message_type; ?>"><?php echo $message; ?></p>
                <?php } ?>

                <h2>Choose a Seat</h2>
                <form method="POST">
                    <div class="seat-container">
                        <div class="bus-layout">
                            <?php 
                            // Loop through rows of seats
                            for ($row = 0; $row < $rows; $row++) {
                                echo '<div class="seat-row">';
                                
                                // Left side seats
                                for ($i = 0; $i < $seats_per_side; $i++) {
                                    $seat_num = ($row * 4) + $i + 1;
                                    if ($seat_num > $total_seats) break;
                                    $is_taken = in_array($seat_num, $reserved_seats);
                                    ?>
                                    <label class="seat">
                                        <input type="checkbox" name="seat_numbers[]" value="<?php echo $seat_num; ?>" 
                                            <?php if ($is_taken) echo 'disabled'; ?>>
                                        <span class="<?php if ($is_taken) echo 'disabled'; ?>"><?php echo $seat_num; ?></span>
                                    </label>
                                    <?php
                                }
                                echo '<div class="aisle"></div>';

                                // Right side seats
                                for ($i = 0; $i < $seats_per_side; $i++) {
                                    $seat_num = ($row * 4) + $seats_per_side + $i + 1;
                                    if ($seat_num > $total_seats) break;
                                    $is_taken = in_array($seat_num, $reserved_seats);
                                    ?>
                                    <label class="seat">
                                        <input type="checkbox" name="seat_numbers[]" value="<?php echo $seat_num; ?>" 
                                            <?php if ($is_taken) echo 'disabled'; ?>>
                                        <span class="<?php if ($is_taken) echo 'disabled'; ?>"><?php echo $seat_num; ?></span>
                                    </label>
                                    <?php
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" name="user_name" class="form-control" placeholder="Your Name" required>
                    </div>
                    <button type="submit" name="reserve" class="btn btn-primary btn-block">Book Seat</button>
                </form>

                <!-- Show payment button if seat is just reserved -->
                <?php if (isset($just_reserved)) { ?>
                    <form method="POST" class="mt-3">
                        <?php foreach ($seat_numbers as $seat_number) { ?>
                            <input type="hidden" name="confirm_seat_numbers[]" value="<?php echo $seat_number; ?>">
                        <?php } ?>
                        <button type="submit" name="confirm_payment" class="btn btn-success btn-block">Pay Now (<?php echo $route['fare']; ?> BDT per seat)</button>
                    </form>
                <?php } ?>

                <p class="mt-3"><a href="index.php">Back to Routes</a></p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Refresh every 30 seconds to update seats
        setInterval(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>