<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    if (!isset($_SESSION['redirect_to'])) {
        $_SESSION['redirect_to'] = 'bus_select_type.php';
    }
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Bus Type - Mass Transport Ticketing System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            animation: fadeIn 2s ease-in-out;
        }
        body.dark-mode {
            background-color: #121212;
            color: #ffffff;
        }
        h1 {
            margin-top: 20px;
            text-align: center;
            color: #007bff;
            animation: fadeIn 2s ease-in-out;
        }
        body.dark-mode h1 {
            color: #bb86fc;
        }
        .card {
            margin: 20px auto;
            animation: fadeIn 2s ease-in-out;
            transition: transform 0.3s, box-shadow 0.3s;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        body.dark-mode .card {
            background: rgba(18, 18, 18, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
        }
        body.dark-mode .card:hover {
            box-shadow: 0 0 30px rgba(187, 134, 252, 0.4);
        }
        .card-body {
            color: #000000;
        }
        body.dark-mode .card-body {
            color: #ffffff;
        }
        .card-title, .card-text {
            color: inherit;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            transform: scale(1.05);
        }
        body.dark-mode .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
        }
        body.dark-mode .btn-primary:hover {
            background-color: #9a67ea;
            border-color: #9a67ea;
            transform: scale(1.05);
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1>Select Bus Type</h1>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Long Route Across Bangladesh</h5>
                        <a href="long_route.php" class="btn btn-primary">Select</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Inside Dhaka Local Routes</h5>
                        <a href="local_route.php" class="btn btn-primary">Select</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <p class="card-text">Travel across different cities in Bangladesh.
                            Currently available routes are: 
                            <ul>
                                <li>Dhaka to Chittagong</li>
                                <li>Dhaka to Sylhet</li>
                                <li>Dhaka to Khulna</li>
                                <li>Dhaka to Rajshahi</li>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <p class="card-text">Travel within Dhaka city.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('dark-mode', document.body.classList.contains('dark-mode'));
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('dark-mode') === 'true') {
                document.body.classList.add('dark-mode');
            }
        });

        // Note: Ensure there's an element with ID 'dark-mode-toggle' in nav.php or elsewhere
        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
    </script>
</body>
</html>