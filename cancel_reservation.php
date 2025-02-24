<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo 'error';
    exit;
}

$userId = $_SESSION['user_id'];

// Delete pending reservations
$query = "DELETE FROM reservations WHERE user_id = ? AND status = 'pending' AND expiry_time > NOW()";
$stmt = $conn2->prepare($query);
$stmt->bind_param("i", $userId);
if ($stmt->execute()) {
    echo 'ok';
} else {
    echo 'error';
}

$stmt->close();
?>