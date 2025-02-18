<?php
// Initialize the session
session_start();
include 'db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $google_id = $_POST['google_id'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $profile_image = $_POST['profile_image'];

    // Check if the user already exists
    $stmt = $conn1->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
    $stmt->bind_param("ss", $google_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // User exists, update Google ID if necessary
        if (empty($user['google_id'])) {
            $stmt = $conn->prepare("UPDATE users SET google_id = ? WHERE email = ?");
            $stmt->bind_param("ss", $google_id, $email);
            $stmt->execute();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        echo json_encode(['new_user' => false]);
    } else {
        // New user, insert into database
        $stmt = $conn1->prepare("INSERT INTO users (google_id, email, name, profile_image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $google_id, $email, $name, $profile_image);
        $stmt->execute();
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        echo json_encode(['new_user' => true, 'user_id' => $stmt->insert_id]);
    }
}
?>