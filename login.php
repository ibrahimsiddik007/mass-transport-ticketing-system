<?php
session_start();
require_once 'google_config.php';

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['redirect_to'])) {
        $redirect_to = $_SESSION['redirect_to'];
        unset($_SESSION['redirect_to']);
        header("Location: $redirect_to");
    } else {
        header('Location: index.php');
    }
    exit;
}

$url = $client->createAuthUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Sign-In CSS -->
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

        .g-signin2 {
            margin: 0 auto;
            display: block;
        }
        .google-signin-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #4285f4;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        .google-signin-btn:hover {
            background-color: #357ae8;
            transform: scale(1.05);
        }
        .google-signin-btn img {
            margin-right: 10px;
        }
        .login-image {
            max-width: 80%; /* Adjusted to make the image smaller */
            height: auto;
            border-radius: 10px;
            margin-top: -20px; /* Move the image up */
        }
        .login-heading {
            margin-top: 30px; /* Move the heading up */
            margin-bottom: 30px;
        }
        .form-control {
            transition: box-shadow 0.3s ease-in-out;
        }
        .form-control:focus {
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }
        .btn-primary {
            transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .card {
            border: 1px solid #007bff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 123, 255, 0.2);
            transition: box-shadow 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        .card:hover {
            box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
            transform: scale(1.02);
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h2 class="text-center mt-5 login-heading">Login</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <form action="manual_login.php" method="POST">
                            <div class="form-group">
                                <label for="email">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body text-center">
                        <form action="<?= $url ?>" method="POST">
                            <button type="submit" class="google-signin-btn btn-block">
                                <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo">
                                Sign in with Google
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-none d-md-block">
                <img src="images/login/login image.jpg" alt="Login Image" class="login-image" style="max-width: 70%; max-height: 400px; margin-top: 20px;">
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentTheme = localStorage.getItem('theme') || 'light';

            if (currentTheme === 'dark') {
                document.body.classList.add('dark-mode');
            }
        });
    </script>
    
</body>
</html>