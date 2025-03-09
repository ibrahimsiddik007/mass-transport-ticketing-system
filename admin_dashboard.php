<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mass Transport Ticketing System</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .dashboard-container h3 {
            margin-bottom: 20px;
            text-align: center;
            color: #007bff;
        }
        .dashboard-container .list-group-item {
            margin-bottom: 10px;
            transition: background-color 0.3s, transform 0.3s;
        }
        .dashboard-container .list-group-item:hover {
            background-color: #e9ecef;
            transform: scale(1.02);
        }
        .logout-link {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-container">
            <a href="admin_dashboard.php?logout=true" class="btn btn-danger logout-link">Logout</a>
            <h3 class="text-center">Admin Dashboard</h3>
            <div class="list-group">
                <a href="admin_bus.php" class="list-group-item list-group-item-action">Bus Management</a>
                <a href="admin_train.php" class="list-group-item list-group-item-action">Train Management</a>
                <a href="admin_metro.php" class="list-group-item list-group-item-action">Metro Management</a>
                <a href="admin_chat.php" class="list-group-item list-group-item-action">Chat</a>
                <a href="admin_contacts.php" class="list-group-item list-group-item-action">Contact Us Section Message</a>
                <a href="admin_reviews.php" class="list-group-item list-group-item-action">Review Management</a>
            </div>
        </div>
    </div>
</body>
</html>