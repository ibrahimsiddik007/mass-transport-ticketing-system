<?php
session_start();
include 'db.php';

// Log the received data
error_log(print_r($_POST, true));

if (!isset($_SESSION['user_id']) || !isset($_POST['seats']) || !isset($_POST['trainId']) || !isset($_POST['date']) || !isset($_POST['compartment'])) {
    echo 'error';
    exit;
}

$userId = $_SESSION['user_id'];
$seats = $_POST['seats'];
$trainId = $_POST['trainId'];
$date = $_POST['date'];
$compartment = $_POST['compartment'];

// Check if the compartment exists
$stmt = $conn2->prepare("SELECT compartment_id FROM compartments WHERE compartment_id = ?");
if (!$stmt) {
    error_log("Prepare failed: (" . $conn2->errno . ") " . $conn2->error);
    echo 'error: prepare failed';
    exit;
}
$stmt->bind_param("i", $compartment);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo 'error: invalid compartment';
    exit;
}

$stmt->close();

foreach ($seats as $seat) {
    // Insert with status 'pending', plus reservation_time = now().
    $stmt = $conn2->prepare("INSERT INTO reservations (user_id, train_id, compartment_id, seat_number, reservation_date, reservation_time, status)
                             VALUES (?, ?, ?, ?, ?, NOW(), 'pending')");
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn2->errno . ") " . $conn2->error);
        echo 'error: prepare failed';
        exit;
    }
    $stmt->bind_param("iiiiss", $userId, $trainId, $compartment, $seat, $date);
    if (!$stmt->execute()) {
        error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        echo 'error: execute failed';
        exit;
    }
    $stmt->close();
}
echo 'ok';
?>