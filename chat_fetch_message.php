<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;

if ($user_id !== null) {
    // Fetch messages for the logged-in user
    $query = "SELECT chat_messages.*, users.name 
              FROM chat_messages 
              JOIN users ON chat_messages.user_id = users.id 
              WHERE chat_messages.user_id = ? OR (chat_messages.is_admin = 1 AND chat_messages.user_id = ?)
              ORDER BY chat_messages.created_at ASC";
    $stmt = $conn1->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);

    // Mark messages as read for the user
    $update_query = "UPDATE chat_messages SET is_read = 1 WHERE user_id = ? AND is_read = 0 AND is_admin = 1";
    $update_stmt = $conn1->prepare($update_query);
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
} elseif ($admin_id !== null && isset($_GET['user_id'])) {
    // Fetch messages for a specific user when the admin is logged in
    $target_user_id = $_GET['user_id'];
    $query = "SELECT chat_messages.*, users.name 
              FROM chat_messages 
              JOIN users ON chat_messages.user_id = users.id 
              WHERE chat_messages.user_id = ? OR (chat_messages.is_admin = 1 AND chat_messages.user_id = ?)
              ORDER BY chat_messages.created_at ASC";
    $stmt = $conn1->prepare($query);
    $stmt->bind_param("ii", $target_user_id, $target_user_id);

    // Mark messages as read for the admin
    $update_query = "UPDATE chat_messages SET is_read = 1 WHERE user_id = ? AND is_read = 0 AND is_admin = 0";
    $update_stmt = $conn1->prepare($update_query);
    $update_stmt->bind_param("i", $target_user_id);
    $update_stmt->execute();
} else {
    echo json_encode([]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
?>