<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['new_messages' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Query to count unread messages for the user
$query = "SELECT COUNT(*) AS unread_count FROM chat_messages WHERE user_id = ? AND is_read = 0 AND is_admin = 1";
$stmt = $conn1->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['new_messages' => $row['unread_count']]);
exit;
?>