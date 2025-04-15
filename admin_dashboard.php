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
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #50c878;
            --accent-color: #ff6b6b;
            --background-color: #f8f9fa;
            --text-color: #2c3e50;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --dark-bg: #121212;
            --dark-card-bg: #1e1e1e;
            --dark-text: #ffffff;
            --dark-border: #333;
            --dashboard-bg: rgba(255, 255, 255, 0.95);
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .infinity-squares {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, rgb(48, 150, 163) 0%, #c3cfe2 100%);
            overflow: hidden;
        }

        .square {
            position: absolute;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 800px;
            width: 90%;
            padding: 20px;
        }

        .dashboard-container {
            background: var(--dashboard-bg);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            padding: 2rem;
            position: relative;
            animation: slideIn 0.6s ease-out forwards;
            backdrop-filter: blur(10px);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-container h3 {
            margin-bottom: 2rem;
            color: var(--text-color);
            font-weight: 600;
            text-align: center;
            position: relative;
        }

        .dashboard-container h3::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .list-group {
            margin-top: 2rem;
            animation: fadeIn 0.8s ease-out 0.4s backwards;
        }

        .list-group-item {
            border: none;
            margin-bottom: 1rem;
            padding: 1.25rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.8);
            transition: all var(--transition-speed);
            position: relative;
            overflow: hidden;
        }

        .list-group-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-color);
            transform: scaleY(0);
            transition: transform var(--transition-speed);
        }

        .list-group-item:hover {
            transform: translateX(10px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .list-group-item:hover::before {
            transform: scaleY(1);
        }

        .list-group-item-action {
            color: var(--text-color);
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .list-group-item-action i {
            color: var(--primary-color);
            font-size: 1.2rem;
            transition: all var(--transition-speed);
        }

        .list-group-item:hover .list-group-item-action i {
            transform: scale(1.2);
            color: var(--secondary-color);
        }

        .logout-link {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all var(--transition-speed);
            z-index: 10;
            background: linear-gradient(135deg, var(--accent-color), #d93025);
            color: white;
            border: none;
        }

        .logout-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 107, 107, 0.3);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .dashboard-container {
                padding: 1.5rem;
            }
            
            .list-group-item {
                padding: 1rem;
            }
            
            .logout-link {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="infinity-squares" id="infinitySquares"></div>
    <div class="container">
        <div class="dashboard-container">
            <a href="admin_dashboard.php?logout=true" class="btn btn-danger logout-link">Logout</a>
            <h3 class="text-center">Admin Dashboard</h3>
            <div class="list-group">
                <a href="metro_admin.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-subway"></i> Metro Management
                </a>
                <a href="bus_admin_long_route.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-bus"></i> Long Route Bus Management
                </a>
                <a href="admin_local_bus.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-bus-alt"></i> Local Route Bus Management
                </a>
                <a href="admin_train_management.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-train"></i> Train Management
                </a>
                <a href="admin_chat.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-comments"></i> Chat
                </a>
                <a href="admin_contacts.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-envelope"></i> Contact Us Section Message
                </a>
                <a href="admin_reviews.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-star"></i> Review Management
                </a>
            </div>
        </div>
    </div>

    <script>
        // Create infinity squares
        function createSquares() {
            const container = document.getElementById('infinitySquares');
            const squareCount = 20;
            
            for (let i = 0; i < squareCount; i++) {
                const square = document.createElement('div');
                square.className = 'square';
                square.style.left = `${Math.random() * 100}%`;
                square.style.animationDelay = `${Math.random() * 15}s`;
                square.style.animationDuration = `${10 + Math.random() * 10}s`;
                container.appendChild(square);
            }
        }

        // Initialize squares
        createSquares();
    </script>
</body>
</html>