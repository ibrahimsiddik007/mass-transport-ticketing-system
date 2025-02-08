<?php
require_once 'google_config.php';
$google_login_url = $client->createAuthUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-light: #ffffff;
            --bg-dark: #2c2c2c;
            --text-light: #000000;
            --text-dark: #e0e0e0;
            --hover-light: #007bff;
            --hover-dark: #007bff;
            --card-bg-dark: #3a3a3a;
            --footer-bg-dark: #3a3a3a;
            --notice-bg-dark: #444444;
            --carousel-caption-bg-dark: rgba(255, 255, 255, 0.7);
            --carousel-caption-text-dark: #000000;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-light);
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        .dark-mode {
            --bg-light: var(--bg-dark);
            --text-light: var(--text-dark);
        }

        .card {
            transition: transform 0.9s, margin 0.9s;
        }

        .card:hover {
            transform: scale(1.07);
            transition: transform 0.1s;
            margin: 5px;
        }

        .dark-mode .card {
            background-color: var(--card-bg-dark);
            color: var(--text-dark);
        }

        .footer {
            background-color: #343a40;
            color: #ffffff;
        }

        .footer.dark-mode {
            background-color: var(--footer-bg-dark);
            color: var(--text-dark);
        }

        .text-center.mt-5 {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h2 class="text-center mt-5">Register</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="manual_register.php" method="POST">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Register</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body text-center">
                        <a href="<?= $google_login_url ?>" class="btn btn-danger">
                            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" style="width: 20px; height: 20px;">
                            Sign in with Google
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const currentTheme = localStorage.getItem('theme') || 'light';

            if (currentTheme === 'dark') {
                document.body.classList.add('dark-mode');
            }

            themeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                const theme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
                localStorage.setItem('theme', theme);
            });
        });
    </script>
</body>
</html>