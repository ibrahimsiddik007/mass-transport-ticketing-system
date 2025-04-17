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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #50c878;
            --accent-color: #ff6b6b;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            --transition-speed: 0.3s;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            padding: 0;
            margin: 0;
        }

        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            margin-top: 60px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            transition: transform var(--transition-speed) ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .login-heading {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 30px;
            color: #fff;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .login-heading i {
            font-size: 2.2rem;
            color: var(--secondary-color);
        }

        .form-group {
            margin-bottom: 25px;
            transition: transform var(--transition-speed) ease;
        }

        .form-group:focus-within {
            transform: translateX(5px);
        }

        .form-group label {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group label i {
            color: var(--primary-color);
            font-size: 1.3rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 12px 20px;
            color: #fff;
            font-size: 1.1rem;
            width: 100%;
            transition: all var(--transition-speed) ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.3);
            border-color: var(--primary-color);
            box-shadow: 0 0 15px rgba(74, 144, 226, 0.3);
            outline: none;
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #357abd);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all var(--transition-speed) ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #357abd, var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4);
        }

        .google-signin-btn {
            background: linear-gradient(135deg, #4285f4, #357ae8);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            transition: all var(--transition-speed) ease;
        }

        .google-signin-btn:hover {
            background: linear-gradient(135deg, #357ae8, #4285f4);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(66, 133, 244, 0.4);
        }

        .google-signin-btn img {
            width: 24px;
            height: 24px;
        }

        .alert {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 12px;
            color: #fff;
            padding: 15px;
            margin-bottom: 25px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: var(--secondary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all var(--transition-speed) ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .forgot-password a:hover {
            color: #fff;
            transform: translateY(-2px);
        }

        .forgot-password a i {
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 30px;
                margin: 20px;
            }

            .login-heading {
                font-size: 1.8rem;
            }

            .form-group label {
                font-size: 1rem;
            }

            .form-control {
                padding: 10px 15px;
                font-size: 1rem;
            }

            .btn-primary, .google-signin-btn {
                padding: 10px 20px;
                font-size: 1rem;
            }

            .forgot-password a {
                font-size: 0.85rem;
            }
        }

        .alert.success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid rgba(46, 204, 113, 0.3);
            color: #fff;
            animation: slideIn 0.5s ease-out;
        }

        .alert.success::before {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 10px;
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="login-container">
        <div class="login-card">
            <h2 class="login-heading"><i class="fas fa-user-circle"></i> Login</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form action="manual_login.php" method="POST">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn-primary"><i class="fas fa-sign-in-alt"></i> Sign In</button>
            </form>

            <div class="forgot-password">
                <a href="forgot_password.php"><i class="fas fa-question-circle"></i> Forgot Password?</a>
            </div>

            <div class="mt-4">
                <form action="<?= $url ?>" method="POST">
                    <button type="submit" class="google-signin-btn">
                        <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo">
                        Sign in with Google
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>