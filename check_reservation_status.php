<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo 'error';
    exit;
}

$userId = $_SESSION['user_id'];
$sql = "SELECT seat_number, TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(reservation_time, INTERVAL 15 MINUTE)) AS remaining_time
        FROM reservations
        WHERE user_id = ? AND status = 'pending'";
$stmt = $conn2->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$reservations = [];
while ($row = $result->fetch_assoc()) {
    $reservations[] = $row;
}

echo json_encode($reservations);
?>