<?php
session_start();
require_once 'db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Check if email exists
    $stmt = $conn1->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Generate 6-digit OTP
        $otp = rand(100000, 999999);
        
        // Store OTP in session with timestamp
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp_timestamp'] = time();
        
        // Send OTP via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'masstransportsystem@gmail.com'; // Replace with your email
            $mail->Password = 'vsez xczk yqfm mdbx'; // Replace with your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            $mail->setFrom('your-email@gmail.com', 'Mass Transport System');
            $mail->addAddress($email, $user['name']);
            
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #3498db;'>Password Reset Request</h2>
                    <p>Hello {$user['name']},</p>
                    <p>You have requested to reset your password. Please use the following OTP to proceed:</p>
                    <div style='background: #f8f9fa; padding: 20px; text-align: center; margin: 20px 0;'>
                        <h1 style='color: #2ecc71; margin: 0;'>{$otp}</h1>
                    </div>
                    <p>This OTP will expire in 10 minutes.</p>
                    <p>If you did not request this password reset, please ignore this email.</p>
                    <p>Best regards,<br>Mass Transport System Team</p>
                </div>
            ";
            
            $mail->send();
            $_SESSION['success'] = "OTP has been sent to your email address.";
            header("Location: reset_password.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to send OTP. Please try again later.";
        }
    } else {
        $_SESSION['error'] = "Email address not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Mass Transport System</title>
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

        .forgot-card {
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

        .forgot-heading {
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

        .forgot-heading i {
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
            .forgot-card {
                padding: 30px;
                margin: 20px;
            }

            .forgot-heading {
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
    <div class="forgot-card">
        <h2 class="forgot-heading"><i class="fas fa-key"></i> Forgot Password</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Send OTP</button>
        </form>

        <div class="back-to-login">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 