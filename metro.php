<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Set the redirect URL to metro.php only if it's not already set
    if (!isset($_SESSION['redirect_to'])) {
        $_SESSION['redirect_to'] = 'metro.php';
    }
    header('Location: login.php');
    exit;
}

// Reset payment completed flag
$_SESSION['payment_completed'] = false;

// Check if active column exists in metro_stations table
$checkColumnQuery = "SHOW COLUMNS FROM metro_stations LIKE 'active'";
$columnResult = $conn1->query($checkColumnQuery);

// If active column doesn't exist, add it
if ($columnResult->num_rows == 0) {
    $alterQuery = "ALTER TABLE metro_stations ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1";
    $conn1->query($alterQuery);
}

// Fetch only ACTIVE station names
$stations = [];
$sql = "SELECT s_name FROM metro_stations WHERE active = 1";
$result = $conn1->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $stations[] = $row['s_name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Metro Ticket</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/metro_ticket.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #50c878;
            --accent-color: #ff6b6b;
            --glass-bg: rgba(0, 0, 0, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            --transition-speed: 0.3s;
            --dropdown-bg: #2a2a2a;
            --dropdown-text: #ffffff;
            --dropdown-hover: #3a3a3a;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/Metro_Ticket_Counter.jpg') no-repeat center center fixed;
            background-size: cover;
            filter: brightness(0.6) contrast(1.1);
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.5));
            z-index: -1;
        }

        body {
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: fadeIn 1.5s ease-out;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .ticket-form {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--glass-shadow);
            animation: slideIn 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            max-width: 600px;
            margin: 2rem auto;
        }

        .ticket-form::before {
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

        .ticket-form h2 {
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

        .ticket-form h2 i {
            color: var(--secondary-color);
            font-size: 2.8rem;
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
            padding: 15px 20px;
            color: #fff;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            box-shadow: 0 0 15px rgba(74, 144, 226, 0.3);
            transform: translateY(-2px);
            color: #fff;
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

        .warning {
            background: rgba(255, 193, 7, 0.2);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 12px;
            padding: 15px;
            margin-top: 25px;
            color: #ffc107;
            text-align: center;
            animation: slideIn 0.5s ease-out;
        }

        .warning p {
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1rem;
        }

        .warning i {
            font-size: 1.2rem;
            animation: pulse 1s infinite;
        }

        /* UI Autocomplete Styling */
        .ui-autocomplete {
            background: rgba(30, 30, 30, 0.95) !important;
            border-radius: 12px !important;
            border: 1px solid var(--glass-border) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5) !important;
            padding: 10px !important;
            max-height: 250px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .ui-autocomplete .ui-menu-item {
            padding: 5px !important;
        }

        .ui-autocomplete .ui-menu-item-wrapper {
            padding: 10px 15px !important;
            color: #fff !important;
            border-radius: 8px !important;
            transition: all 0.2s ease;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .ticket-form {
                padding: 30px;
                margin: 20px;
            }

            .ticket-form h2 {
                font-size: 2rem;
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

            .warning p {
                font-size: 0.9rem;
            }
        }

        /* Dark Mode Adjustments */
        body.dark-mode::before {
            filter: brightness(0.7) contrast(1.1);
        }

        body.dark-mode::after {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4));
        }

        body.dark-mode .ticket-form {
            background: rgba(0, 0, 0, 0.6);
        }

        body.dark-mode .form-control {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        body.dark-mode .form-control:focus {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="ticket-form">
                    <h2><i class="fas fa-ticket-alt"></i> Purchase Metro Ticket</h2>
                    <form action="confirm_metro_ticket.php" method="POST">
                        <div class="form-group">
                            <label for="startLocation"><i class="fas fa-map-marker-alt"></i> Start Location</label>
                            <input type="text" class="form-control" id="startLocation" name="startLocation" placeholder="Type to search stations..." required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="endLocation"><i class="fas fa-map-marker-alt"></i> End Location</label>
                            <input type="text" class="form-control" id="endLocation" name="endLocation" placeholder="Type to search stations..." required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="fare"><i class="fas fa-money-bill-wave"></i> Fare</label>
                            <input type="number" class="form-control" id="fare" name="fare" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-ticket-alt"></i> Purchase Ticket</button>
                        <div class="warning">
                            <p><i class="fas fa-exclamation-triangle"></i> Warning: This ticket is valid for the next 8 hours since the purchase.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Create an array of available stations for autocomplete
            var availableStations = <?php echo json_encode($stations); ?>;

            // Initialize autocomplete for start location input
            $("#startLocation").autocomplete({
                source: availableStations,
                minLength: 1,
                select: function(event, ui) {
                    updateFare();
                }
            });

            // Initialize autocomplete for end location input
            $("#endLocation").autocomplete({
                source: availableStations,
                minLength: 1,
                select: function(event, ui) {
                    updateFare();
                }
            });

            // Function to update the fare when start or end locations change
            function updateFare() {
                var startLocation = $('#startLocation').val();
                var endLocation = $('#endLocation').val();
                
                if (startLocation && endLocation && availableStations.includes(startLocation) && availableStations.includes(endLocation)) {
                    $.ajax({
                        url: 'get_metro_fare.php',
                        type: 'POST',
                        data: {
                            start_point: startLocation,
                            end_point: endLocation
                        },
                        success: function(response) {
                            $('#fare').val(response);
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                }
            }

            // Call updateFare when inputs change
            $('#startLocation, #endLocation').on('change keyup', function() {
                updateFare();
            });
            
            // Validate form on submit
            $('form').submit(function(e) {
                var startLocation = $('#startLocation').val();
                var endLocation = $('#endLocation').val();
                
                // Check if entered stations are valid
                if (!availableStations.includes(startLocation)) {
                    alert('Please select a valid start station from the suggestions');
                    e.preventDefault();
                    return false;
                }
                
                if (!availableStations.includes(endLocation)) {
                    alert('Please select a valid end station from the suggestions');
                    e.preventDefault();
                    return false;
                }
                
                if (startLocation === endLocation) {
                    alert('Start and end stations cannot be the same');
                    e.preventDefault();
                    return false;
                }
                
                if (!$('#fare').val()) {
                    alert('Unable to calculate fare. Please try again.');
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>