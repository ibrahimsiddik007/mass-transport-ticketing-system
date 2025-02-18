<?php
session_start();
require_once 'google_config.php';
include 'db.php'; // Include your database connection file

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    // Get profile info from Google
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    $google_id = $google_account_info->id;
    $email = $google_account_info->email;
    $name = $google_account_info->name;

    // Set default profile image
    $default_profile_image = 'images/default_profile_image.jpg';

    // Check if the user already exists
    $stmt = $conn1->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
    $stmt->bind_param("ss", $google_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Always update user's profile data
        $stmt = $conn1->prepare("UPDATE users SET google_id = ?, name = ? WHERE email = ?");
        $stmt->bind_param("sss", $google_id, $name, $email);
        $stmt->execute();

        // Set session with existing profile image or default if not set
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['profile_image'] = $user['profile_image'] ?: $default_profile_image;
    } else {
        // New user, set default profile image
        $stmt = $conn1->prepare("INSERT INTO users (google_id, email, name, profile_image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $google_id, $email, $name, $default_profile_image);
        $stmt->execute();

        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['profile_image'] = $default_profile_image;
    }

    // Redirect to the intended page or profile page
    if (isset($_SESSION['redirect_to'])) {
        $redirect_to = $_SESSION['redirect_to'];
        unset($_SESSION['redirect_to']);
        header("Location: $redirect_to");
    } else {
        echo '<script>
            const redirectURL = localStorage.getItem("redirectURL");
            if (redirectURL) {
                localStorage.removeItem("redirectURL");
                window.location.href = redirectURL;
            } else {
                window.location.href = "profile.php";
            }
        </script>';
    }
    exit;
} else {
    header('Location: ' . $client->createAuthUrl());
    exit;
}
?>