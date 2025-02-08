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

// Fetch station names
$stations = [];
$sql = "SELECT s_name FROM stations";
$result = $conn->query($sql);
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
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/metro_ticket.css">
    <style>
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/Metro_Ticket_Counter.jpg') no-repeat center center fixed;
            background-size: cover;
            background-color: rgba(0, 0, 0, 0.9); /* Add a black overlay with 50% opacity */
            z-index: -1;
        }
        body {
            color: #fff;
            animation: fadeIn 2s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .ticket-form {
            background: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            animation: slideIn 1s ease-in-out;
        }
        @keyframes slideIn {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .form-group label {
            color: #fff;
            font-weight: bold;
        }
        .btn-primary {
            transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .warning {
            color: #ffc107;
        }
        .warning p {
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="ticket-form mt-5">
                    <h2 class="text-center"><i class="fas fa-ticket-alt"></i> Purchase Metro Ticket</h2>
                    <form action="confirm_metro_ticket.php" method="POST">
                        <div class="form-group">
                            <label for="startLocation"><i class="fas fa-map-marker-alt"></i> Start Location</label>
                            <select class="form-control" id="startLocation" name="startLocation" required>
                                <?php foreach ($stations as $station): ?>
                                    <option value="<?= $station ?>"><?= $station ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="endLocation"><i class="fas fa-map-marker-alt"></i> End Location</label>
                            <select class="form-control" id="endLocation" name="endLocation" required>
                                <?php foreach ($stations as $station): ?>
                                    <option value="<?= $station ?>"><?= $station ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fare"><i class="fas fa-money-bill-wave"></i> Fare</label>
                            <input type="number" class="form-control" id="fare" name="fare" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-ticket-alt"></i> Purchase Ticket</button>
                        <div class="warning text-center mt-3">
                            <p><i class="fas fa-exclamation-triangle"></i> Warning: This ticket is valid for the next 8 hours since the purchase.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            function updateFare() {
                var startLocation = $('#startLocation').val();
                var endLocation = $('#endLocation').val();
                if (startLocation && endLocation) {
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

            $('#startLocation, #endLocation').change(updateFare);
        });
    </script>
</body>
</html>