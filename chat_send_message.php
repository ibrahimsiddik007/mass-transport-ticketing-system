<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo 'error: not authenticated';
    exit;
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
$message = $_POST['message'];
$is_admin = isset($_POST['is_admin']) ? $_POST['is_admin'] : 0;
$target_user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;

if ($user_id === null && $admin_id === null) {
    echo 'error: no user or admin id';
    exit;
}

if ($is_admin) {
    $user_id = $target_user_id; // Set user_id to the target user ID when the message is sent by admin
    $name = "system"; // Set name to "system" for admin messages
} else {
    // Fetch the user's name from the database
    $query = "SELECT name FROM users WHERE id = ?";
    $stmt = $conn1->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $name = $user['name'];
}

$query = "INSERT INTO chat_messages (user_id, message, is_admin, name) VALUES (?, ?, ?, ?)";
$stmt = $conn1->prepare($query);
$stmt->bind_param("isis", $user_id, $message, $is_admin, $name);
if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error: ' . $stmt->error;
}
?>