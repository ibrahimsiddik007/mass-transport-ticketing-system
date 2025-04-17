<?php
session_start();
require_once 'db.php';

// Check if OTP is set in session
if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

// Check if OTP has expired (10 minutes)
if (time() - $_SESSION['otp_timestamp'] > 600) {
    unset($_SESSION['reset_otp']);
    unset($_SESSION['reset_email']);
    unset($_SESSION['otp_timestamp']);
    $_SESSION['error'] = "OTP has expired. Please request a new one.";
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['otp']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
        $otp = $_POST['otp'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify OTP
        if ($otp == $_SESSION['reset_otp']) {
            // Validate password
            if ($password === $confirm_password) {
                if (strlen($password) >= 8) {
                    // Hash the new password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Update password in database
                    $stmt = $conn1->prepare("UPDATE users SET password = ? WHERE email = ?");
                    $stmt->bind_param("ss", $hashed_password, $_SESSION['reset_email']);
                    
                    if ($stmt->execute()) {
                        // Clear session variables
                        unset($_SESSION['reset_otp']);
                        unset($_SESSION['reset_email']);
                        unset($_SESSION['otp_timestamp']);
                        
                        $_SESSION['success'] = "Password has been reset successfully. Please login with your new password.";
                        header("Location: login.php");
                        exit();
                    } else {
                        $_SESSION['error'] = "Failed to reset password. Please try again.";
                    }
                } else {
                    $_SESSION['error'] = "Password must be at least 8 characters long.";
                }
            } else {
                $_SESSION['error'] = "Passwords do not match.";
            }
        } else {
            $_SESSION['error'] = "Invalid OTP.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Mass Transport System</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --accent-color: #e74c3c;
            --text-color: #333;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --transition-speed: 0.3s;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .reset-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .reset-heading {
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

        .reset-heading i {
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
            transition: all var(--transition-speed) ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #357abd, var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4);
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

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: var(--secondary-color);
            text-decoration: none;
            transition: color var(--transition-speed) ease;
        }

        .back-to-login a:hover {
            color: #fff;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .reset-card {
                padding: 30px;
                margin: 20px;
            }

            .reset-heading {
                font-size: 1.8rem;
            }

            .form-group label {
                font-size: 1rem;
            }

            .form-control {
                padding: 10px 15px;
                font-size: 1rem;
            }

            .btn-primary {
                padding: 10px 20px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <h2 class="reset-heading"><i class="fas fa-lock"></i> Reset Password</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="otp"><i class="fas fa-key"></i> Enter OTP</label>
                <input type="text" class="form-control" id="otp" name="otp" placeholder="Enter the 6-digit OTP" required maxlength="6" pattern="[0-9]{6}">
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> New Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required minlength="8">
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Reset Password</button>
        </form>

        <div class="back-to-login">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Auto-focus the OTP input
        document.getElementById('otp').focus();
        
        // Validate OTP input to only allow numbers
        document.getElementById('otp').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html> 