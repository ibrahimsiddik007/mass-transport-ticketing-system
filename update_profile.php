<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploaded_profile_images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $image_filename = md5($_SESSION['user_id']) . '.jpg';
        $local_image_path = $upload_dir . $image_filename;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $local_image_path)) {
            $stmt = $conn1->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->bind_param("si", $local_image_path, $_SESSION['user_id']);
            $stmt->execute();

            $_SESSION['profile_image'] = $local_image_path;
        } else {
            $_SESSION['error'] = "Failed to upload image.";
            header('Location: profile.php');
            exit;
        }
    }

    if (isset($_POST['address'])) {
        $address = $_POST['address'];
        $stmt = $conn1->prepare("UPDATE users SET address = ? WHERE id = ?");
        $stmt->bind_param("si", $address, $_SESSION['user_id']);
        $stmt->execute();
    }

    if (isset($_POST['phone_number'])) {
        $phone_number = $_POST['phone_number'];
        $stmt = $conn1->prepare("UPDATE users SET phone = ? WHERE id = ?");
        $stmt->bind_param("si", $phone_number, $_SESSION['user_id']);
        $stmt->execute();
    }

    header('Location: profile.php');
} else {
    $_SESSION['error'] = "Invalid request.";
    header('Location: profile.php');
}
?>