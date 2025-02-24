<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    echo 'error: user not logged in';
    exit;
}

if (!isset($_POST['train']) || !isset($_POST['date']) || !isset($_POST['ticket_count'])) {
    echo 'error: missing parameters';
    exit;
}

$user_id = $_SESSION['user_id'];
$train_id = $_POST['train'];
$date = $_POST['date'];
$ticket_count = (int)$_POST['ticket_count'];

// Debugging statements
echo "User ID: $user_id<br>";
echo "Train ID: $train_id<br>";
echo "Date: $date<br>";
echo "Ticket Count: $ticket_count<br>";

// Fetch compartments and seats for the selected train
$compartments_query = "SELECT compartment_id, total_seats FROM compartments WHERE train_id = ? ORDER BY compartment_id ASC";
$compartments_stmt = $conn2->prepare($compartments_query);
$compartments_stmt->bind_param("i", $train_id);
$compartments_stmt->execute();
$compartments_result = $compartments_stmt->get_result();

$seats_reserved = [];
$needed = $ticket_count;

while ($needed > 0 && $compartment = $compartments_result->fetch_assoc()) {
    $compartment_id = $compartment['compartment_id'];
    $total_seats = $compartment['total_seats'];

    // Fetch the number of reserved seats in this compartment for the specified date
    $reserved_seats_query = "SELECT COUNT(*) as reserved_seats FROM reservations WHERE train_id = ? AND compartment_id = ? AND reservation_date = ? AND status != 'canceled'";
    $reserved_seats_stmt = $conn2->prepare($reserved_seats_query);
    $reserved_seats_stmt->bind_param("iis", $train_id, $compartment_id, $date);
    $reserved_seats_stmt->execute();
    $reserved_seats_result = $reserved_seats_stmt->get_result();
    $reserved_seats_row = $reserved_seats_result->fetch_assoc();
    $reserved_seats = $reserved_seats_row['reserved_seats'];

    // Check if there are enough available seats
    $available_seats = $total_seats - $reserved_seats;
    if ($available_seats >= $needed) {
        // Find available seats in this compartment
        $seat_query = "SELECT seat_number FROM seats WHERE compartment_id = ? AND seat_number NOT IN (SELECT seat_number FROM reservations WHERE train_id = ? AND compartment_id = ? AND reservation_date = ? AND status != 'canceled') ORDER BY seat_number ASC";
        $seat_stmt = $conn2->prepare($seat_query);
        $seat_stmt->bind_param("iiis", $compartment_id, $train_id, $compartment_id, $date);
        $seat_stmt->execute();
        $seat_result = $seat_stmt->get_result();

        while ($seat_row = $seat_result->fetch_assoc()) {
            if ($needed <= 0) break;
            $seat_number = $seat_row['seat_number'];
            $seats_reserved[] = $seat_number;
            $needed--;
        }
    } else {
        // Not enough seats in this compartment, reserve as many as possible and move to the next compartment
        $seat_query = "SELECT seat_number FROM seats WHERE compartment_id = ? AND seat_number NOT IN (SELECT seat_number FROM reservations WHERE train_id = ? AND compartment_id = ? AND reservation_date = ? AND status != 'canceled') ORDER BY seat_number ASC";
        $seat_stmt = $conn2->prepare($seat_query);
        $seat_stmt->bind_param("iiis", $compartment_id, $train_id, $compartment_id, $date);
        $seat_stmt->execute();
        $seat_result = $seat_stmt->get_result();

        while ($seat_row = $seat_result->fetch_assoc()) {
            if ($needed <= 0) break;
            $seat_number = $seat_row['seat_number'];
            $seats_reserved[] = $seat_number;
            $needed--;
        }
    }
}

// Debugging statements
echo "Seats Reserved: " . implode(', ', $seats_reserved) . "<br>";

if (count($seats_reserved) < $ticket_count) {
    echo 'error: not enough seats available';
    exit;
}

// Insert reservations into the database
foreach ($seats_reserved as $seat_number) {
    $insert_query = "INSERT INTO reservations (user_id, train_id, compartment_id, seat_number, reservation_date, reservation_time, status, expiry_time) VALUES (?, ?, ?, ?, ?, NOW(), 'pending', DATE_ADD(NOW(), INTERVAL 15 MINUTE))";
    $insert_stmt = $conn2->prepare($insert_query);
    $insert_stmt->bind_param("iiiss", $user_id, $train_id, $compartment_id, $seat_number, $date);
    $insert_stmt->execute();
}

// Debugging statement
echo "Reservations inserted successfully<br>";

//Comment out the redirect to reservation status page for debugging
header('Location: reservation_status.php');
exit;
?>