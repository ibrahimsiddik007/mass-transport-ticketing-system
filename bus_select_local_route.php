<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    if (!isset($_SESSION['redirect_to'])) {
        $_SESSION['redirect_to'] = 'bus_select_local_route.php';
    }
    header('Location: login.php');
    exit;
}

// Fetch all origins
$query = "SELECT DISTINCT origin FROM local_routes";
$result = $conn3->query($query);
$origins = [];
while ($row = $result->fetch_assoc()) {
    $origins[] = $row['origin'];
}

$_SESSION['payment_completed'] = false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Local Route - Mass Transport Ticketing System</title>
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
        h1 {
            margin-top: 20px;
            text-align: center;
            color: #007bff;
            animation: fadeIn 2s ease-in-out;
        }
        body.dark-mode h1 {
            color: #bb86fc;
        }
        .card {
            margin: 20px auto;
            animation: fadeIn 2s ease-in-out;
            transition: transform 0.3s, box-shadow 0.3s;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 300px; /* Make the card smaller */
        }
        body.dark-mode .card {
            background: rgba(18, 18, 18, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
        }
        body.dark-mode .card:hover {
            box-shadow: 0 0 30px rgba(187, 134, 252, 0.4);
        }
        .card-body {
            color: #000000;
        }
        body.dark-mode .card-body {
            color: #ffffff;
        }
        .card-title, .card-text {
            color: inherit;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            transform: scale(1.05);
        }
        body.dark-mode .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
        }
        body.dark-mode .btn-primary:hover {
            background-color: #9a67ea;
            border-color: #9a67ea;
            transform: scale(1.05);
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .icon {
            font-size: 50px;
            color: #007bff;
            animation: bounce 2s infinite;
        }
        body.dark-mode .icon {
            color: #bb86fc;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1>Select Route (inside Dhaka only)</h1>
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-bus icon"></i>
                <form id="routeForm" method="POST" action="bus_selected_local.php">
                    <div class="form-group">
                        <label for="origin">Select Origin:</label>
                        <select class="form-control" id="origin" name="origin" required>
                            <option value="">Select Origin</option>
                            <?php foreach ($origins as $origin) { ?>
                                <option value="<?php echo htmlspecialchars($origin); ?>"><?php echo htmlspecialchars($origin); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="destination">Select Destination:</label>
                        <select class="form-control" id="destination" name="destination" required>
                            <option value="">Select Destination</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Next</button>
                </form>
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

            $('#origin').on('change', function() {
                const origin = $(this).val();
                if (origin) {
                    $.ajax({
                        url: 'bus_fetch_local_destination.php',
                        method: 'GET',
                        data: { origin: origin },
                        dataType: 'json',
                        success: function(data) {
                            $('#destination').empty().append('<option value="">Select Destination</option>');
                            data.forEach(function(destination) {
                                $('#destination').append('<option value="' + destination + '">' + destination + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching destinations:', error);
                        }
                    });
                } else {
                    $('#destination').empty().append('<option value="">Select Destination</option>');
                }
            });
        });

        // Note: Ensure there's an element with ID 'dark-mode-toggle' in nav.php or elsewhere
        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
    </script>
</body>
</html>