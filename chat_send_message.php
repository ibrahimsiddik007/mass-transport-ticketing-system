<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo 'error: not authenticated';
    exit;
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
$message = $_POST['message'];
$is_admin = isset($_POST['is_admin']) ? $_POST['is_admin'] : 0;

// If admin is sending a message, we need the target user_id
if ($admin_id !== null && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id']; // Target user for admin messages
}

if ($user_id === null) {
    echo 'error: no user id specified';
    exit;
}

// Insert the message - using user_id for both user messages and admin messages to a specific user
$query = "INSERT INTO chat_messages (user_id, message, is_admin, is_read, created_at) 
          VALUES (?, ?, ?, 0, NOW())";
$stmt = $conn1->prepare($query);
$stmt->bind_param("isi", $user_id, $message, $is_admin);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error: ' . $stmt->error;
}
?>