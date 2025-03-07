<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['admin_id'])) {
    echo json_encode([]);
    exit;
}

// Fetch all users who have sent messages and count unread messages
$query = "SELECT users.id, users.name, 
                 SUM(CASE WHEN chat_messages.is_read = 0 AND chat_messages.is_admin = 0 THEN 1 ELSE 0 END) AS unread_count 
          FROM chat_messages 
          JOIN users ON chat_messages.user_id = users.id 
          WHERE chat_messages.is_admin = 0 
          GROUP BY users.id, users.name 
          ORDER BY users.name ASC";
$result = $conn1->query($query);

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
?>