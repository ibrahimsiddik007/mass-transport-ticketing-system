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


// Clear the payment completed flag
unset($_SESSION['payment_completed']);


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Bus Type - Mass Transport Ticketing System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #007bff, #00bfff);
            --secondary-gradient: linear-gradient(135deg, #6c757d, #343a40);
            --dark-gradient: linear-gradient(135deg, #121212, #1a1a1a);
            
            /* Light mode variables */
            --bg-color: rgba(255, 255, 255, 0.9);
            --text-color: #333;
            --card-bg: rgba(255, 255, 255, 0.85);
            --card-border: rgba(255, 255, 255, 0.2);
            --nav-bg: rgba(255, 255, 255, 0.9);
            --nav-text: #333;
            --btn-hover: #0056b3;
        }

        /* Dark mode variables */
        [data-theme="dark"] {
            --bg-color: rgba(0, 0, 0, 0.9);
            --text-color: #fff;
            --card-bg: rgba(0, 0, 0, 0.7);
            --card-border: rgba(255, 255, 255, 0.1);
            --nav-bg: rgba(0, 0, 0, 0.8);
            --nav-text: #fff;
            --btn-hover: #00bfff;
        }

        body {
            background: url('images/bus_background.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            position: relative;
            animation: fadeIn 1.5s ease-in-out;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--bg-color);
            opacity: 0.8;
            z-index: -1;
            transition: all 0.3s ease;
        }

        .container {
            padding-top: 2rem;
            animation: fadeInUp 1s ease-out;
        }

        h1 {
            color: #fff;
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            animation: fadeInDown 1s ease-out;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 15px;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            background: var(--card-bg);
        }

        .card-body {
            padding: 2rem;
            color: var(--text-color);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-color);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            transition: color 0.3s ease;
        }

        .card-text {
            color: var(--text-color);
            opacity: 0.9;
            line-height: 1.6;
            transition: color 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 25px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--btn-hover);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 123, 255, 0.3);
        }

        .btn-primary::after {
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

        .btn-primary:hover::after {
            transform: translateX(100%);
        }

        ul {
            list-style-type: none;
            padding-left: 0;
        }

        ul li {
            padding: 0.5rem 0;
            position: relative;
            padding-left: 1.5rem;
            color: var(--text-color);
            transition: color 0.3s ease;
        }

        ul li::before {
            content: '✓';
            color: #00bfff;
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .route-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #00bfff;
            text-shadow: 0 0 10px rgba(0, 191, 255, 0.5);
        }

        .card-header {
            background: rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dark-mode-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--card-bg);
            backdrop-filter: blur(5px);
            border: 1px solid var(--card-border);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text-color);
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1);
            background: var(--card-bg);
        }

        /* Remove tick marks from navbar */
        .navbar-nav .nav-link::before {
            display: none !important;
        }
        
        .navbar-nav .nav-item::before {
            display: none !important;
        }

        /* Remove any other potential tick mark styles */
        .nav-item .active::before,
        .nav-link::before {
            display: none !important;
        }

        /* Remove footer styles */
        .footer {
            display: none !important;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1 class="animate__animated animate__fadeInDown">Select Bus Type</h1>
        <div class="row">
            <div class="col-md-6">
                <div class="card animate__animated animate__fadeInLeft">
                    <div class="card-header">
                        <i class="fas fa-bus-alt route-icon"></i>
                        <h5 class="card-title">Long Route Across Bangladesh</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Travel across different cities in Bangladesh. Currently available routes are:</p>
                        <ul>
                            <li>Dhaka to Chittagong</li>
                            <li>Dhaka to Sylhet</li>
                            <li>Dhaka to Khulna</li>
                            <li>Dhaka to Rajshahi</li>
                        </ul>
                        <div class="text-center mt-4">
                            <a href="long_route.php" class="btn btn-primary">Select Route</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card animate__animated animate__fadeInRight">
                    <div class="card-header">
                        <i class="fas fa-city route-icon"></i>
                        <h5 class="card-title">Inside Dhaka Local Routes</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Travel within Dhaka city. No route-based ticketing. Customers will be able to get into any bus to their desired destination within 3 hours of purchase.</p>
                        <div class="text-center mt-4">
                            <a href="bus_select_local_route.php" class="btn btn-primary">Select Local Route</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check for saved theme preference or default to light
            const currentTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', currentTheme);

            // Create and append dark mode toggle button if it doesn't exist
            if (!document.querySelector('.dark-mode-toggle')) {
                const toggle = document.createElement('button');
                toggle.className = 'dark-mode-toggle';
                toggle.innerHTML = '<i class="fas fa-moon"></i>';
                document.body.appendChild(toggle);

                // Toggle theme
                toggle.addEventListener('click', function() {
                    const currentTheme = document.documentElement.getAttribute('data-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    
                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    
                    // Update toggle icon
                    toggle.innerHTML = `<i class="fas fa-${newTheme === 'dark' ? 'moon' : 'sun'}"></i>`;
                });

                // Set initial icon
                toggle.innerHTML = `<i class="fas fa-${currentTheme === 'dark' ? 'moon' : 'sun'}"></i>`;
            }

            // Add animation to cards when they come into view
            const cards = document.querySelectorAll('.card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease-out';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>