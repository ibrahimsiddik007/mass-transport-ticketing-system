<?php
include 'db/db_connection.php';

$train_id = $_POST['train_id'];
$seats = explode(',', $_POST['seats']);
$customer_name = $_POST['customer_name'];
$customer_email = $_POST['customer_email'];

foreach ($seats as $seat) {
    // Check if the seat is already reserved or paid
    $check_sql = "SELECT * FROM reservations WHERE train_id = $train_id AND seat_number = $seat AND (status = 'reserved' OR status = 'paid')";
    $check_result = $conn->query($check_sql);
    if ($check_result->num_rows == 0) {
        // Insert new reservation
        $sql = "INSERT INTO reservations (train_id, seat_number, customer_name, customer_email, status) VALUES ($train_id, $seat, '$customer_name', '$customer_email', 'paid')";
        $conn->query($sql);

        // Decrement the total seats in the trains table
        $sql_update_seats = "UPDATE trains SET total_seats = total_seats - 1 WHERE id = $train_id";
        $conn->query($sql_update_seats);
    }
}

header("Location: receipt.php?train_id=$train_id&seats=" . implode(',', $seats));
exit();
?>