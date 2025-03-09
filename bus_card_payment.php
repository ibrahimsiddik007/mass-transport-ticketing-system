<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    if (!isset($_SESSION['redirect_to'])) {
        $_SESSION['redirect_to'] = 'bus_card_payment.php';
    }
    header('Location: login.php');
    exit;
}

// Check if origin and destination are set
if (!isset($_GET['origin']) || !isset($_GET['destination'])) {
    header('Location: bus_selected_local.php');
    exit;
}

// Check if payment is already completed
if (isset($_SESSION['payment_completed']) && $_SESSION['payment_completed'] === true) {
    header('Location: bus_select_type.php');
    exit;
}

$origin = $_GET['origin'];
$destination = $_GET['destination'];

// Fetch bus details by joining local_buses and local_routes based on origin and destination
$query = "
    SELECT local_buses.bus_name, local_routes.fare 
    FROM local_buses 
    JOIN local_routes ON local_buses.origin = local_routes.origin AND local_buses.destination = local_routes.destination
    WHERE local_routes.origin = ? AND local_routes.destination = ?
";
$stmt = $conn3->prepare($query);
$stmt->bind_param("ss", $origin, $destination);
$stmt->execute();
$result = $stmt->get_result();
$bus = $result->fetch_assoc();

if (!$bus) {
    echo "No bus found for the selected route.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Payment - Mass Transport Ticketing System</title>
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
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 1px solid #007bff;
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
        .visa-logo {
            width: 50px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1>Card Payment</h1>
        <div class="card">
            <div class="card-body text-center">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" alt="Visa Logo" class="visa-logo">
                <p class="card-text">Bus: <?php echo htmlspecialchars($bus['bus_name']); ?></p>
                <p class="card-text">Fare: <?php echo htmlspecialchars($bus['fare']); ?> BDT</p>
                <form id="paymentForm" method="POST" action="bus_payment_process.php">
                    <div class="form-group">
                        <label for="card_number">Card Number:</label>
                        <input type="text" class="form-control" id="account_number" name="account_number" required>
                    </div>
                    <div class="form-group">
                        <label for="card_expiry">Expiry Date:</label>
                        <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YY" required>
                    </div>
                    <div class="form-group">
                        <label for="card_cvc">CVC:</label>
                        <input type="text" class="form-control" id="pin" name="pin" required>
                    </div>
                    <input type="hidden" name="bus_name" value="<?php echo htmlspecialchars($bus['bus_name']); ?>">
                    <input type="hidden" name="origin" value="<?php echo htmlspecialchars($origin); ?>">
                    <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
                    <input type="hidden" name="payment_method" value="card">
                    <input type="hidden" name="amount" value="<?php echo htmlspecialchars($bus['fare']); ?>">
                    <button type="submit" class="btn btn-primary">Pay with Card</button>
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
        });

        // Note: Ensure there's an element with ID 'dark-mode-toggle' in nav.php or elsewhere
        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
    </script>
</body>
</html>