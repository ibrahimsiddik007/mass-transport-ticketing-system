<?php
session_start();
include 'db.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'long_route.php';
    header('Location: login.php');
    exit;
}

// Get unique from locations
$from_locations_query = "SELECT DISTINCT from_location FROM long_route_buses ORDER BY from_location";
$from_locations_result = $conn3->query($from_locations_query);
$from_locations = [];
while ($row = $from_locations_result->fetch_assoc()) {
    $from_locations[] = $row['from_location'];
}

// Get unique to locations
$to_locations_query = "SELECT DISTINCT to_location FROM long_route_buses ORDER BY to_location";
$to_locations_result = $conn3->query($to_locations_query);
$to_locations = [];
while ($row = $to_locations_result->fetch_assoc()) {
    $to_locations[] = $row['to_location'];
}

// Get unique routes (from-to combinations) - keep this for fallback
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
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #50c878;
            --accent-color: #ff6b6b;
            --background-color: #f8f9fa;
            --text-color: #2c3e50;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color var(--transition-speed), color var(--transition-speed);
        }

        /* Light Mode Styles (explicit) */
        body:not(.dark-mode) {
            --background-color: #f8f9fa;
            --text-color: #2c3e50;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body:not(.dark-mode) .card {
            background-color: white;
            border-color: #e9ecef;
        }

        body:not(.dark-mode) .card-title {
            color: var(--primary-color);
        }

        body:not(.dark-mode) .card-title::after {
            background: var(--secondary-color);
        }

        body:not(.dark-mode) .form-control {
            background-color: white;
            border-color: #e9ecef;
            color: var(--text-color);
        }

        body:not(.dark-mode) .form-control:focus {
            background-color: white;
            border-color: var(--primary-color);
            color: var(--text-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }

        body:not(.dark-mode) .list-group-item {
            background-color: #f8f9fa;
            border-color: #e9ecef;
            color: var(--text-color);
        }

        body:not(.dark-mode) .list-group-item:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateX(10px);
        }

        body:not(.dark-mode) select.form-control option {
            background-color: white;
            color: var(--text-color);
        }

        body:not(.dark-mode) select.form-control option:hover {
            background-color: var(--primary-color);
            color: white;
        }

        body:not(.dark-mode) .btn-primary {
            background-color: var(--primary-color);
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
        }

        body:not(.dark-mode) .btn-primary:hover {
            background-color: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(74, 144, 226, 0.4);
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

        body.dark-mode .card-title {
            color: var(--secondary-color);
        }

        body.dark-mode .card-title::after {
            background: var(--primary-color);
        }

        body.dark-mode .form-control {
            background-color: #2d2d2d;
            border-color: #333;
            color: #e8eaed;
        }

        body.dark-mode .form-control:focus {
            background-color: #2d2d2d;
            border-color: var(--primary-color);
            color: #e8eaed;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }

        body.dark-mode .list-group-item {
            background-color: #2d2d2d;
            border-color: #333;
            color: #e8eaed;
        }

        body.dark-mode .list-group-item:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateX(10px);
        }

        body.dark-mode select.form-control option {
            background-color: #2d2d2d;
            color: #e8eaed;
        }

        body.dark-mode select.form-control option:hover {
            background-color: var(--primary-color);
            color: white;
        }

        body.dark-mode .btn-primary {
            background-color: var(--primary-color);
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
        }

        body.dark-mode .btn-primary:hover {
            background-color: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(74, 144, 226, 0.4);
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
            transform: scale(1.1) rotate(180deg);
        }

        body.dark-mode .dark-mode-toggle {
            background: var(--secondary-color);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .dark-mode-toggle:hover {
            background: var(--primary-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
            overflow: hidden;
            height: 100%;
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

        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: border-color var(--transition-speed), box-shadow var(--transition-speed);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
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
        }

        .btn-primary:hover {
            background-color: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
        }

        .list-group-item {
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            background-color: #f8f9fa;
            transition: all var(--transition-speed);
            cursor: pointer;
        }

        .list-group-item:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateX(10px);
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .list-group-item {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .list-group-item:nth-child(1) { animation-delay: 0.1s; }
        .list-group-item:nth-child(2) { animation-delay: 0.2s; }
        .list-group-item:nth-child(3) { animation-delay: 0.3s; }
        .list-group-item:nth-child(4) { animation-delay: 0.4s; }
        .list-group-item:nth-child(5) { animation-delay: 0.5s; }
        .list-group-item:nth-child(6) { animation-delay: 0.6s; }
        .list-group-item:nth-child(7) { animation-delay: 0.7s; }
        .list-group-item:nth-child(8) { animation-delay: 0.8s; }

        /* Responsive Design */
        @media (max-width: 768px) {
            .card-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .container {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
        }

        /* Custom Select Styling */
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath d='M7 10l5 5 5-5z' fill='%234a90e2'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
            padding-right: 40px;
            cursor: pointer;
            color: var(--text-color);
            font-size: 1rem;
            line-height: 1.5;
            background-color: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all var(--transition-speed);
            height: auto;
            min-height: 45px;
        }

        select.form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
            outline: none;
        }

        select.form-control option {
            padding: 10px;
            color: var(--text-color);
            background-color: white;
        }

        select.form-control option:hover {
            background-color: var(--primary-color);
            color: white;
        }

        select.form-control option:checked {
            background-color: var(--primary-color);
            color: white;
        }

        /* Form Group Enhancement */
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
            font-size: 1rem;
            transition: all var(--transition-speed);
        }

        .form-group:focus-within label {
            color: var(--primary-color);
        }

        /* Custom Select Container */
        .select-container {
            position: relative;
            width: 100%;
        }

        .select-container::after {
            content: '';
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid var(--primary-color);
            pointer-events: none;
        }
        
        /* UI Autocomplete Styling */
        .ui-autocomplete {
            background: var(--background-color) !important;
            border-radius: 8px !important;
            border: 1px solid #e9ecef !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
            padding: 10px !important;
            max-height: 250px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1050 !important;
        }

        body.dark-mode .ui-autocomplete {
            background: #2d2d2d !important;
            border-color: #333 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
        }

        .ui-autocomplete .ui-menu-item {
            padding: 5px !important;
        }

        .ui-autocomplete .ui-menu-item-wrapper {
            padding: 10px 15px !important;
            color: var(--text-color) !important;
            border-radius: 8px !important;
            transition: all 0.2s ease;
        }

        body.dark-mode .ui-autocomplete .ui-menu-item-wrapper {
            color: #e8eaed !important;
        }

        .ui-autocomplete .ui-menu-item-wrapper.ui-state-active {
            background: var(--primary-color) !important;
            border: none !important;
            margin: 0 !important;
            color: white !important;
        }

        .ui-helper-hidden-accessible {
            display: none !important;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1 class="text-center mt-4 mb-4">Intercity Bus Ticket Booking</h1>
        
        <div class="card-grid">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Select Route and Date</h3>
                    <form action="long_route_buses.php" method="GET">
                        <div class="form-group">
                            <label for="fromLocation"><i class="fas fa-map-marker-alt"></i> From:</label>
                            <input type="text" class="form-control" id="fromLocation" name="fromLocation" placeholder="Type departure city..." required autocomplete="off">
                        </div>
                        
                        <div class="form-group">
                            <label for="toLocation"><i class="fas fa-map-marker-alt"></i> To:</label>
                            <input type="text" class="form-control" id="toLocation" name="toLocation" placeholder="Type destination city..." required autocomplete="off">
                        </div>
                        
                        <!-- Hidden input to store the route format needed for backend -->
                        <input type="hidden" id="route" name="route" value="">
                        
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
            
            <div class="card">
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
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
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

        $(document).ready(function() {
            // Create arrays of available locations for autocomplete
            var fromLocations = <?php echo json_encode($from_locations); ?>;
            var toLocations = <?php echo json_encode($to_locations); ?>;
            
            // Initialize autocomplete for from location
            $("#fromLocation").autocomplete({
                source: fromLocations,
                minLength: 1,
                select: function(event, ui) {
                    updateRouteValue();
                }
            });
            
            // Initialize autocomplete for to location
            $("#toLocation").autocomplete({
                source: toLocations,
                minLength: 1,
                select: function(event, ui) {
                    updateRouteValue();
                }
            });
            
            // Update the hidden route field when locations change
            function updateRouteValue() {
                var fromLocation = $('#fromLocation').val();
                var toLocation = $('#toLocation').val();
                
                if (fromLocation && toLocation) {
                    $('#route').val(fromLocation + '-' + toLocation);
                }
            }
            
            // Trigger updateRouteValue when inputs change
            $('#fromLocation, #toLocation').on('change keyup', function() {
                updateRouteValue();
            });
            
            // When journey date changes
            $('#journey_date').change(function() {
                const selectedDate = $(this).val();
                console.log(`Date selected: ${selectedDate}`);
            });
            
            // Add route label click handlers
            $('.list-group-item').click(function() {
                var routeText = $(this).text();
                var parts = routeText.split(' to ');
                
                if (parts.length === 2) {
                    $('#fromLocation').val(parts[0].trim());
                    $('#toLocation').val(parts[1].trim());
                    updateRouteValue();
                }
            });
            
            // Validate form before submission
            $('form').submit(function(e) {
                var fromLocation = $('#fromLocation').val();
                var toLocation = $('#toLocation').val();
                
                if (!fromLocation || !fromLocations.includes(fromLocation)) {
                    alert('Please select a valid departure city');
                    e.preventDefault();
                    return false;
                }
                
                if (!toLocation || !toLocations.includes(toLocation)) {
                    alert('Please select a valid destination city');
                    e.preventDefault();
                    return false;
                }
                
                if (fromLocation === toLocation) {
                    alert('Departure and destination cities cannot be the same');
                    e.preventDefault();
                    return false;
                }
                
                if (!$('#journey_date').val()) {
                    alert('Please select a journey date');
                    e.preventDefault();
                    return false;
                }
                
                // Update the route value one final time before submission
                $('#route').val(fromLocation + '-' + toLocation);
                return true;
            });
        });
    </script>
</body>
</html>