<?php
session_start();
include 'db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn1, $_POST['name']);
    $email = mysqli_real_escape_string($conn1, $_POST['email']);
    $phone = mysqli_real_escape_string($conn1, $_POST['phone']);
    $message = mysqli_real_escape_string($conn1, $_POST['message']);
    $created_at = date('Y-m-d H:i:s');

    $query = "INSERT INTO contacts (name, email, phone, message, created_at) VALUES ('$name', '$email', '$phone', '$message', '$created_at')";
    if (mysqli_query($conn1, $query)) {
        echo 'success';
    } else {
        echo 'error';
    }
}
?>