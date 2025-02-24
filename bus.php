<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    if (!isset($_SESSION['redirect_to'])) {
        $_SESSION['redirect_to'] = 'bus.php';
    }
    header('Location: login.php');
    exit;
}

// Reset payment completed flag
$_SESSION['payment_completed'] = false;
$pdo = $conn3;

// Get unique destinations for the dropdown
$destinations_stmt = $pdo->query("SELECT DISTINCT destination FROM routes ORDER BY destination");
$destinations = $destinations_stmt->fetch_all(MYSQLI_ASSOC);

// Initialize filters
$destination_filter = isset($_GET['destination']) ? $_GET['destination'] : '';

// Build the query with filters
$query = "
    SELECT r.id, b.bus_name, b.capacity, r.origin, r.destination, r.fare
    FROM routes r
    JOIN buses b ON b.id = (SELECT id FROM buses WHERE r.id % 3 + 1 = buses.id LIMIT 1)
    WHERE 1=1
";

if ($destination_filter) {
    $query .= " AND r.destination = '$destination_filter'";
}

$query .= " ORDER BY r.origin, r.destination";

$stmt = $pdo->query($query);
$routes = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bus - Mass Transport Ticketing System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #000000; /* Default light mode text color */
            font-family: Arial, sans-serif;
            animation: fadeIn 2s ease-in-out;
        }
        body.dark-mode {
            background-color: #121212;
            color: #ffffff; /* Dark mode text color */
        }
        h1 {
            margin-top: 20px;
            text-align: center;
            color: #007bff; /* Light mode h1 color */
            animation: fadeIn 2s ease-in-out;
        }
        body.dark-mode h1 {
            color: #bb86fc; /* Dark mode h1 color */
        }
        p {
            text-align: center;
            color: #6c757d; /* Light mode p color */
            animation: fadeIn 2s ease-in-out;
        }
        body.dark-mode p {
            color: #b0b0b0; /* Dark mode p color */
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
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
        }
        body.dark-mode .card {
            background: rgba(18, 18, 18, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card-body {
            color: #000000; /* Light mode card text */
        }
        body.dark-mode .card-body {
            color: #ffffff; /* Dark mode card text */
        }
        .card-title, .card-text {
            color: inherit; /* Inherit from .card-body */
        }
        .form-row {
            animation: fadeIn 2s ease-in-out;
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

        @keyframes slideIn {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <h1>Dhaka City Bus Routes</h1>
    <p>Reserve a seat and travel within 5 hours from your reservation time!</p>
    <div class="container">
        <form method="GET" class="mb-4">
            <div class="form-row">
                <div class="form-group col-md-10">
                    <label for="destination">Destination</label>
                    <select id="destination" name="destination" class="form-control">
                        <option value="">Choose...</option>
                        <?php foreach ($destinations as $destination): ?>
                            <option value="<?php echo $destination['destination']; ?>" <?php if ($destination['destination'] == $destination_filter) echo 'selected'; ?>>
                                <?php echo $destination['destination']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary btn-block">Search</button>
                </div>
            </div>
        </form>
        <div class="row">
            <?php if (count($routes) > 0): ?>
                <?php foreach ($routes as $route): ?>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $route['bus_name']; ?></h5>
                                <p class="card-text"><strong>Origin:</strong> <?php echo $route['origin']; ?></p>
                                <p class="card-text"><strong>Destination:</strong> <?php echo $route['destination']; ?></p>
                                <p class="card-text"><strong>Fare:</strong> BDT <?php echo number_format($route['fare'], 2); ?></p>
                                <a href="bus_reserve.php?route_id=<?php echo $route['id']; ?>" class="btn btn-primary">Reserve Seat</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">No routes found for the selected destination.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

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

        // Note: Ensure there's an element with ID 'dark-mode-toggle' in nav.php or elsewhere
        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
    </script>
</body>
</html>