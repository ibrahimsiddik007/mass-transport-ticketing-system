<?php
session_start();
include 'db.php';

$userId = $_SESSION['user_id'];

// Check for pending reservations
$query = "SELECT *, TIMESTAMPDIFF(SECOND, NOW(), expiry_time) as remaining_time FROM reservations WHERE user_id = ? AND status = 'pending' AND expiry_time > NOW()";
$stmt = $conn2->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $reservation = $result->fetch_assoc();
    echo json_encode(['status' => 'pending', 'remaining_time' => $reservation['remaining_time']]);
} else {
    // Release expired reservations
    $query = "UPDATE reservations SET status = 'canceled' WHERE user_id = ? AND status = 'pending' AND expiry_time <= NOW()";
    $stmt = $conn2->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    // Delete expired reservations
    $query = "DELETE FROM reservations WHERE user_id = ? AND status = 'canceled' AND expiry_time <= NOW()";
    $stmt = $conn2->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    echo json_encode(['status' => 'none']);
}

$stmt->close();
?>