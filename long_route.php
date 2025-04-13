<?php
session_start();
include 'db.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'long_route.php';
    header('Location: login.php');
    exit;
}

// Get unique routes (from-to combinations)
$routes_query = "SELECT DISTINCT from_location, to_location FROM long_route_buses ORDER BY from_location";
$routes_result = $conn3->query($routes_query);

// Get unique journey dates (future dates only)
$today = date('Y-m-d');
$dates_query = "SELECT DISTINCT journey_date FROM long_route_buses WHERE journey_date >= '$today' ORDER BY journey_date";
$dates_result = $conn3->query($dates_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Long Route Bus</title>
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
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1 class="text-center mt-4 mb-4">Intercity Bus Ticket Booking</h1>
        
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Select Route and Date</h3>
                <form action="long_route_buses.php" method="GET">
                    <div class="form-group">
                        <label for="route">Select Route:</label>
                        <select class="form-control" id="route" name="route" required>
                            <option value="">-- Select a Route --</option>
                            <?php while ($route = $routes_result->fetch_assoc()): ?>
                                <option value="<?= $route['from_location'] ?>-<?= $route['to_location'] ?>">
                                    <?= $route['from_location'] ?> to <?= $route['to_location'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Add date selection for next 7 days -->
                    <div class="form-group">
                        <label for="journey_date">Journey Date</label>
                        <select class="form-control" id="journey_date" name="journey_date" required>
                            <?php
                            // Generate next 7 days
                            for ($i = 0; $i < 7; $i++) {
                                $date = date('Y-m-d', strtotime("+$i days"));
                                $formatted_date = date('l, F j, Y', strtotime("+$i days"));
                                echo "<option value=\"$date\">$formatted_date</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Find Buses</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Available Routes</h5>
                <ul class="list-group">
                    <li class="list-group-item">Dhaka to Chittagong</li>
                    <li class="list-group-item">Dhaka to Sylhet</li>
                    <li class="list-group-item">Dhaka to Khulna</li>
                    <li class="list-group-item">Dhaka to Rajshahi</li>
                    <li class="list-group-item">Chittagong to Dhaka</li>
                    <li class="list-group-item">Sylhet to Dhaka</li>
                    <li class="list-group-item">Khulna to Dhaka</li>
                    <li class="list-group-item">Rajshahi to Dhaka</li>
                </ul>
            </div>
        </div>
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

        // Note: Ensure there's an element with ID 'dark-mode-toggle' in nav.php
        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
        
        // JavaScript for route selection UI enhancements
        $(document).ready(function() {
            // Highlight available routes when selecting
            $('#route').change(function() {
                const selectedRoute = $(this).val();
                if (selectedRoute) {
                    console.log(`Route selected: ${selectedRoute}`);
                }
            });
            
            // When journey date changes
            $('#journey_date').change(function() {
                const selectedDate = $(this).val();
                console.log(`Date selected: ${selectedDate}`);
            });
            
            // Validate form before submission
            $('form').submit(function() {
                if (!$('#route').val()) {
                    alert('Please select a route');
                    return false;
                }

                if (!$('#journey_date').val()) {
                    alert('Please select a journey date');
                    return false;
                }

                return true; // Allow form submission
            });
        });

        // JavaScript to populate departure times based on route
        $(document).ready(function() {
            // When route changes, update departure times
            $('#route').change(function() {
                const routeValue = $(this).val();
                const journeyDate = $('#journey_date').val();
                
                if (routeValue) {
                    // Parse the route value which is in format "from-to"
                    const [fromLocation, toLocation] = routeValue.split('-');
                    
                    console.log(`Looking for buses from ${fromLocation} to ${toLocation} on ${journeyDate}`);
                }
            });

            // Function to handle the View Seats button click
            $(document).on('click', '.view-seats', function () {
                const busId = $(this).data('bus-id');
                const journeyDate = $('#journey_date').val(); // Get selected journey date
                const busName = $(this).closest('tr').find('td:nth-child(2)').text();
                
                // Update modal title with bus name and date
                $('#seatMapModalLabel').text(`Bus Seat Map - ${busName} (${journeyDate})`);
                
                $('#seatMapModal').modal('show'); // Show the modal
            });
        });
    </script>
</body>
</html>