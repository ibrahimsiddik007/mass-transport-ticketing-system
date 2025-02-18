<?php
session_start();
include 'db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = trim($_POST['password']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $profile_image = 'images\default_profile_account_photo.jpg';

    // Check if the user already exists
    $stmt = $conn1->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        echo "User already exists!";
    } else {
        // New user, insert into database
        $stmt = $conn1->prepare("INSERT INTO users (name, email, password, phone, address,profile_image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $password, $phone, $address, $profile_image);
        $stmt->execute();
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['profile_image'] = 'images\default_profile_account_photo.jpg';

        header('Location: profile.php');
    }
}
?>