<?php
session_start();
include 'db.php'; // Include your database connection file
ob_start(); // Start output buffering

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Debug: Output the entered email and password
    echo "Entered email: " . htmlspecialchars($email) . "<br>";
    echo "Entered password: " . htmlspecialchars($password) . "<br>";

    // Validate user credentials
     

    // Check if database connection is established
    if ($conn1->connect_error) {
        die("Connection failed: " . $conn1->connect_error);
    }
    echo "Database connection established.<br>";

    // Prepare and execute the SQL statement
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn1, $sql);
    $num = mysqli_num_rows($result);
    echo "SQL statement executed.<br>";

    if ($num == 1) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Debug: Output fetched values
            echo "Fetched userName: " . htmlspecialchars($row['name']) . "<br>";
            echo "Fetched hashedPassword: " . htmlspecialchars($row['password']) . "<br>";

            // Debug: Output entered password and hashed password before verification
            echo "Entered password: " . htmlspecialchars($password) . "<br>";
            echo "Hashed password from DB: " . htmlspecialchars($row['password']) . "<br>";

            if (password_verify($password, $row['password'])) {
                echo "Password verified.<br>";

                // Set session variables
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['email'] = $email;
                $_SESSION['user_name'] = $row['name'];

                // Redirect to profile page
                header('Location: profile.php');
                exit;
            } else {
                echo "Invalid email or password.<br>";
                $_SESSION['error'] = "Invalid email or password.";
                header('Location: login.php');
                exit;
            }
        }
    } else {
        echo "Invalid email or password.<br>";
        $_SESSION['error'] = "Invalid email or password.";
        header('Location: login.php');
        exit;
    }
}

ob_end_flush(); // Flush the output buffer
?>