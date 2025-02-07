<?php
session_start();
include 'db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        if (isset($_SESSION['redirect_to'])) {
            $redirect_to = $_SESSION['redirect_to'];
            unset($_SESSION['redirect_to']);
            header("Location: $redirect_to");
        } else {
            header('Location: profile.php');
        }
        exit;
    } else {
        echo "Invalid email or password.";
    }
}
?>