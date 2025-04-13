<?php
session_start();
include 'db.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'long_route.php';
    header('Location: login.php');
    exit;
}

// Ensure route and journey_date are provided
if (!isset($_GET['route']) || !isset($_GET['journey_date'])) {
    header('Location: long_route.php');
    exit;
}

// Parse route (from-to)
$route = explode('-', $_GET['route']);
if (count($route) != 2) {
    header('Location: long_route.php');
    exit;
}

$from_location = $route[0];
$to_location = $route[1];
$journey_date = $_GET['journey_date'];

// Get all buses for the selected route without time filtering
$buses_query = "SELECT b.bus_id, b.bus_name, b.from_location, b.to_location, 
                TIME_FORMAT(b.departure_time, '%H:%i') as departure_time_24h,
                TIME_FORMAT(b.departure_time, '%h:%i %p') as departure_time, 
                b.fare, b.bus_type, b.total_seats
                FROM long_route_buses b
                WHERE b.from_location = ? AND b.to_location = ?
                ORDER BY b.departure_time";

$stmt = $conn3->prepare($buses_query);
$stmt->bind_param("ss", $from_location, $to_location);
$stmt->execute();
$buses_result = $stmt->get_result();

// Group buses by time periods for the filter
$morning_buses = [];    // Before 12 PM
$afternoon_buses = [];  // 12 PM - 5 PM
$evening_buses = [];    // 5 PM - 9 PM
$night_buses = [];      // After 9 PM

while ($bus = $buses_result->fetch_assoc()) {
    $time_24h = $bus['departure_time_24h'];
    $hour = (int)substr($time_24h, 0, 2);
    
    if ($hour < 12) {
        $morning_buses[] = $bus;
    } elseif ($hour < 17) {
        $afternoon_buses[] = $bus;
    } elseif ($hour < 21) {
        $evening_buses[] = $bus;
    } else {
        $night_buses[] = $bus;
    }
}

// Reset result pointer
$buses_result->data_seek(0);

// Count buses in each time period for the filter badges
$morning_count = count($morning_buses);
$afternoon_count = count($afternoon_buses);
$evening_count = count($evening_buses);
$night_count = count($night_buses);
$total_buses = $buses_result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Buses - Mass Transport Ticketing System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color var(--transition-speed), color var(--transition-speed);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: all var(--transition-speed);
            margin-bottom: 1.5rem;
            overflow: hidden;
            animation: slideIn 0.6s ease-out forwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .card-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .bus-card {
            transition: all var(--transition-speed);
            border: 1px solid rgba(74, 144, 226, 0.1);
        }

        .bus-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .time-filters .btn-group {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .time-filters .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: all var(--transition-speed);
            position: relative;
            overflow: hidden;
            border: 2px solid var(--primary-color);
            background: white;
            color: var(--primary-color);
        }

        .time-filters .btn:hover {
            background-color: rgba(74, 144, 226, 0.1);
            transform: translateY(-2px);
        }

        .time-filters .btn.active {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
        }

        .badge {
            padding: 0.35em 0.65em;
            border-radius: 6px;
            font-weight: 600;
            transition: all var(--transition-speed);
        }

        .badge-info {
            background-color: rgba(74, 144, 226, 0.1);
            color: var(--primary-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all var(--transition-speed);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            background-color: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all var(--transition-speed);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
        }

        .bus-item {
            transition: all var(--transition-speed);
            animation: fadeIn 0.6s ease-out forwards;
        }

        .bus-item:nth-child(1) { animation-delay: 0.1s; }
        .bus-item:nth-child(2) { animation-delay: 0.2s; }
        .bus-item:nth-child(3) { animation-delay: 0.3s; }
        .bus-item:nth-child(4) { animation-delay: 0.4s; }
        .bus-item:nth-child(5) { animation-delay: 0.5s; }
        .bus-item:nth-child(6) { animation-delay: 0.6s; }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .time-filters .btn-group {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }
            
            .time-filters .btn {
                width: 100%;
                margin-right: 0;
            }
        }

        /* Dark Mode Styles */
        body.dark-mode {
            --background-color: #121212;
            --text-color: #e8eaed;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .card {
            background-color: #1e1e1e;
            border-color: #333;
        }

        body.dark-mode .bus-card {
            background-color: #1e1e1e;
            border-color: #333;
        }

        body.dark-mode .time-filters .btn {
            background-color: #2d2d2d;
            border-color: #333;
            color: #e8eaed;
        }

        body.dark-mode .time-filters .btn:hover {
            background-color: rgba(74, 144, 226, 0.2);
        }

        body.dark-mode .time-filters .btn.active {
            background-color: var(--primary-color);
            color: white;
        }

        body.dark-mode .badge-info {
            background-color: rgba(74, 144, 226, 0.2);
            color: #ffffff;
        }

        /* Dark Mode Toggle Button */
        .dark-mode-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all var(--transition-speed);
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1);
        }

        body.dark-mode .dark-mode-toggle {
            background: var(--secondary-color);
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1 class="text-center mt-4 mb-4">Available Buses</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Journey Details</h5>
                <p class="card-text">
                    <strong>From:</strong> <?= htmlspecialchars($from_location) ?><br>
                    <strong>To:</strong> <?= htmlspecialchars($to_location) ?><br>
                    <strong>Date:</strong> <?= date('l, F j, Y', strtotime($journey_date)) ?>
                </p>
                <a href="long_route.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Change Selection</a>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Filter by Departure Time</h5>
                <div class="time-filters mt-3">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-primary time-filter active" data-time="all">
                            All Times <span class="badge badge-light"><?= $total_buses ?></span>
                        </button>
                        <button type="button" class="btn btn-outline-primary time-filter" data-time="morning">
                            Morning <span class="badge badge-light"><?= $morning_count ?></span>
                        </button>
                        <button type="button" class="btn btn-outline-primary time-filter" data-time="afternoon">
                            Afternoon <span class="badge badge-light"><?= $afternoon_count ?></span>
                        </button>
                        <button type="button" class="btn btn-outline-primary time-filter" data-time="evening">
                            Evening <span class="badge badge-light"><?= $evening_count ?></span>
                        </button>
                        <button type="button" class="btn btn-outline-primary time-filter" data-time="night">
                            Night <span class="badge badge-light"><?= $night_count ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($buses_result->num_rows > 0): ?>
            <div class="row" id="buses-container">
                <?php while ($bus = $buses_result->fetch_assoc()): 
                    $time_24h = $bus['departure_time_24h'];
                    $hour = (int)substr($time_24h, 0, 2);
                    
                    $time_period = "";
                    if ($hour < 12) {
                        $time_period = "morning";
                    } elseif ($hour < 17) {
                        $time_period = "afternoon";
                    } elseif ($hour < 21) {
                        $time_period = "evening";
                    } else {
                        $time_period = "night";
                    }
                ?>
                    <div class="col-md-6 mb-4 bus-item" data-time="<?= $time_period ?>">
                        <div class="card bus-card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($bus['bus_name']) ?></h5>
                                <p class="card-text">
                                    <span class="badge badge-info"><?= $time_period ?></span><br>
                                    <strong>Departure:</strong> <?= $bus['departure_time'] ?><br>
                                    <strong>Bus Type:</strong> <?= htmlspecialchars($bus['bus_type']) ?><br>
                                    <strong>Fare:</strong> BDT <?= number_format($bus['fare'], 2) ?>
                                </p>
                                <a href="long_route_seats.php?bus_id=<?= $bus['bus_id'] ?>&journey_date=<?= urlencode($journey_date) ?>" 
                                   class="btn btn-primary btn-block">Select Seats</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No buses available for the selected route. Please try a different route.
            </div>
            <a href="long_route.php" class="btn btn-primary">Go Back</a>
        <?php endif; ?>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Dark mode toggle functionality
        function toggleDarkMode() {
            const body = document.body;
            const isDarkMode = body.classList.contains('dark-mode');
            
            if (isDarkMode) {
                body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            } else {
                body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            }
            
            // Update icon
            const icon = document.querySelector('.dark-mode-toggle i');
            if (icon) {
                icon.className = isDarkMode ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        // Check for saved theme preference
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const body = document.body;
            
            if (savedTheme === 'dark') {
                body.classList.add('dark-mode');
            } else {
                body.classList.remove('dark-mode');
            }

            // Add dark mode toggle button if it doesn't exist
            if (!document.querySelector('.dark-mode-toggle')) {
                const toggleButton = document.createElement('button');
                toggleButton.className = 'dark-mode-toggle';
                toggleButton.innerHTML = '<i class="fas fa-moon"></i>';
                toggleButton.onclick = toggleDarkMode;
                document.body.appendChild(toggleButton);
            }

            // Update initial icon
            const icon = document.querySelector('.dark-mode-toggle i');
            if (icon) {
                icon.className = body.classList.contains('dark-mode') ? 'fas fa-sun' : 'fas fa-moon';
            }
        });

        // Time filter functionality
        $(document).ready(function() {
            $('.time-filter').click(function() {
                // Update active button
                $('.time-filter').removeClass('active');
                $(this).addClass('active');
                
                const selectedTime = $(this).data('time');
                
                if (selectedTime === 'all') {
                    // Show all buses
                    $('.bus-item').show();
                } else {
                    // Hide all buses first
                    $('.bus-item').hide();
                    // Show only buses that match the selected time period
                    $(`.bus-item[data-time="${selectedTime}"]`).show();
                }
                
                // Show message if no buses match the filter
                if ($('.bus-item:visible').length === 0) {
                    if ($('#no-buses-message').length === 0) {
                        $('#buses-container').after('<div id="no-buses-message" class="alert alert-info">No buses available for the selected time period.</div>');
                    }
                } else {
                    $('#no-buses-message').remove();
                }
            });
        });
    </script>
</body>
</html>