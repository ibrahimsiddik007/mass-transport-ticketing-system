<?php
session_start();
require_once 'google_config.php';
include 'db.php'; // Include your database connection file

if (isset($_POST['phone']) && isset($_SESSION['google_id'])) {
    $google_id = $_SESSION['google_id'];
    $email = $_SESSION['email'];
    $name = $_SESSION['name'];
    $profile_image = $_SESSION['profile_image'];
    $phone = $_POST['phone'];

    // Insert new user into database
    $stmt = $conn->prepare("INSERT INTO users (google_id, email, name, profile_image, phone) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("sssss", $google_id, $email, $name, $profile_image, $phone);
    if ($stmt->execute() === false) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['profile_image'] = $profile_image;

    // Clear temporary session variables
    unset($_SESSION['google_id']);
    unset($_SESSION['email']);
    unset($_SESSION['name']);
    unset($_SESSION['profile_image']);

    session_destroy();
    echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
} else {
    echo "<script>alert('Error: Form not submitted or session expired.'); window.location.href='register.php';</script>";
}

?>