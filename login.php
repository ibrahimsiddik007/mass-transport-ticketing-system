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
            animation: fadeIn 1.5s ease-out;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/login/login image.jpg') no-repeat center center fixed;
            background-size: cover;
            filter: brightness(0.4) contrast(1.2);
            z-index: -2;
        }

        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.9), rgba(22, 33, 62, 0.9));
            z-index: -1;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--glass-shadow);
            animation: slideIn 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 500px;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(80, 200, 120, 0.1));
            pointer-events: none;
            animation: shine 3s infinite linear;
        }

        @keyframes shine {
            0% { background-position: -100% 0; }
            100% { background-position: 200% 0; }
        }

        @keyframes slideIn {
            from { transform: translateY(50px) scale(0.95); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        .login-heading {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 30px;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .login-heading i {
            font-size: 2.8rem;
            color: var(--secondary-color);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
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
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 12px 20px;
            color: #fff;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            box-shadow: 0 0 15px rgba(74, 144, 226, 0.3);
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
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .btn-primary:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 8px 25px rgba(74, 144, 226, 0.4);
        }

        .btn-primary:hover::before {
            transform: translateX(100%);
        }

        .btn-primary i {
            margin-right: 10px;
            animation: float 2s ease-in-out infinite;
        }

        .google-signin-btn {
            background: linear-gradient(135deg, #4285f4, #357ae8);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(66, 133, 244, 0.3);
        }

        .google-signin-btn:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 8px 25px rgba(66, 133, 244, 0.4);
        }

        .google-signin-btn img {
            width: 24px;
            height: 24px;
            animation: pulse 2s infinite;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-card {
                padding: 30px;
                margin: 20px;
            }

            .login-heading {
                font-size: 2rem;
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
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="login-container">
        <div class="login-card">
            <h2 class="login-heading"><i class="fas fa-user-circle"></i> Login</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
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
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-sign-in-alt"></i> Sign In</button>
            </form>

            <div class="mt-4">
                <form action="<?= $url ?>" method="POST">
                    <button type="submit" class="google-signin-btn btn-block">
                        <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo">
                        Sign in with Google
                    </button>
                </form>
            </div>
        </div>
    </div>

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