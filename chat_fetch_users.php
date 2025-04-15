<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([]);
    exit;
}

// Debug the query execution
error_log("Admin fetching users with messages");

// Get only users who have sent at least one message
$query = "SELECT DISTINCT u.id, u.name, 
          (SELECT COUNT(*) FROM chat_messages 
           WHERE user_id = u.id AND is_read = 0 AND is_admin = 0) AS unread_count
          FROM users u
          INNER JOIN chat_messages cm ON u.id = cm.user_id
          GROUP BY u.id
          ORDER BY MAX(cm.created_at) DESC, unread_count DESC"; 

$result = $conn1->query($query);

if (!$result) {
    error_log("Error in user query: " . $conn1->error);
    echo json_encode([]);
    exit;
}

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

error_log("Found " . count($users) . " users with messages");
echo json_encode($users);
?>