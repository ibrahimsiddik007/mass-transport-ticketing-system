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
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            animation: fadeIn 2s ease-in-out;
        }
        body.dark-mode {
            background-color: #121212;
            color: #ffffff;
        }
        .card {
            margin: 20px auto;
            animation: fadeIn 2s ease-in-out;
            transition: transform 0.3s, box-shadow 0.3s;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        body.dark-mode .card {
            background: rgba(18, 18, 18, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .bus-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s, transform 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .time-filters .btn-group {
            flex-wrap: wrap;
        }

        .time-filters .btn {
            margin-bottom: 5px;
            transition: all 0.3s;
        }

        .time-filters .btn.active {
            background-color: #007bff;
            color: white;
        }

        .badge {
            margin-left: 5px;
        }

        .bus-item {
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .time-filters .btn-group {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                grid-gap: 5px;
            }
            
            .time-filters .btn {
                border-radius: 4px !important;
                margin-right: 0;
            }
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
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('dark-mode', document.body.classList.contains('dark-mode'));
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('dark-mode') === 'true') {
                document.body.classList.add('dark-mode');
            }
        });

        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
    </script>
    <script>
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