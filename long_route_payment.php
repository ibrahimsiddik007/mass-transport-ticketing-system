<?php
session_start();
include 'db.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'long_route.php';
    header('Location: login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['bus_id']) || !isset($_POST['selected_seats']) || !isset($_POST['total_fare']) || !isset($_POST['journey_date'])) {
    header('Location: long_route.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$bus_id = $_POST['bus_id'];
$selected_seats = json_decode($_POST['selected_seats'], true);
$total_fare = $_POST['total_fare'];

$journey_date = isset($_POST['journey_date']) ? $_POST['journey_date'] : '';

// Validate the selected seats
if (empty($selected_seats)) {
    header('Location: long_route_seats.php?bus_id=' . $bus_id);
    exit;
}

// Get bus details
$bus_query = "SELECT * FROM long_route_buses WHERE bus_id = ?";
$stmt = $conn3->prepare($bus_query);
$stmt->bind_param("i", $bus_id);
$stmt->execute();
$bus_result = $stmt->get_result();
$bus = $bus_result->fetch_assoc();

if (!$bus) {
    header('Location: long_route.php');
    exit;
}

// Store booking information in session for payment processing
$_SESSION['long_route_booking'] = [
    'user_id' => $user_id,
    'bus_id' => $bus_id,
    'selected_seats' => $selected_seats,
    'total_fare' => $total_fare,
    'journey_date' => $journey_date, // Add this line
    'bus_details' => $bus
];

// Get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn1->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Mass Transport Ticketing System</title>
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
            --button-hover-scale: 1.05;
            --button-active-scale: 0.95;
            --dark-bg: #121212;
            --dark-card-bg: #1e1e1e;
            --dark-text: #ffffff;
            --dark-border: #333;
            --dark-input-bg: #2d2d2d;
            --dark-hover: #3d3d3d;
            --dark-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            --dark-glow: 0 0 15px rgba(74, 144, 226, 0.3);
            --dark-gradient: linear-gradient(135deg, #1a1a1a, #2d2d2d);
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: fadeIn 0.8s ease-out;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.dark-mode {
            background: var(--dark-gradient);
            color: var(--dark-text);
        }

        body.dark-mode .card {
            background-color: var(--dark-card-bg);
            border-color: var(--dark-border);
            box-shadow: var(--dark-shadow);
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.dark-mode .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--dark-glow);
        }

        body.dark-mode .card-title {
            color: var(--dark-text);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .card-title::after {
            background: var(--primary-color);
            box-shadow: 0 0 10px var(--primary-color);
        }

        body.dark-mode .payment-method {
            background-color: var(--dark-card-bg);
            border-color: var(--dark-border);
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.dark-mode .payment-method:hover {
            background-color: var(--dark-hover);
            border-color: var(--primary-color);
            transform: translateX(5px) scale(1.02);
            box-shadow: var(--dark-glow);
        }

        body.dark-mode .payment-method.selected {
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.2), rgba(74, 144, 226, 0.3));
            border-color: var(--primary-color);
            box-shadow: var(--dark-glow);
        }

        body.dark-mode .form-control {
            background-color: var(--dark-input-bg);
            border-color: var(--dark-border);
            color: var(--dark-text);
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.dark-mode .form-control:focus {
            background-color: var(--dark-input-bg);
            border-color: var(--primary-color);
            color: var(--dark-text);
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }

        body.dark-mode .btn-primary {
            background: linear-gradient(135deg, #357abd, var(--primary-color));
            color: var(--dark-text);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.dark-mode .btn-primary:hover {
            background: linear-gradient(135deg, #2d6a9d, #357abd);
            transform: translateY(-3px) scale(var(--button-hover-scale));
            box-shadow: var(--dark-glow);
        }

        body.dark-mode .btn-secondary {
            background: linear-gradient(135deg, #5a6268, #6c757d);
            color: var(--dark-text);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.dark-mode .btn-secondary:hover {
            background: linear-gradient(135deg, #4a5258, #5a6268);
            transform: translateY(-3px) scale(var(--button-hover-scale));
            box-shadow: var(--dark-glow);
        }

        body.dark-mode .important-notes {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(255, 107, 107, 0.15));
            border-left: 4px solid var(--accent-color);
            box-shadow: var(--dark-shadow);
        }

        body.dark-mode .total-amount {
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(74, 144, 226, 0.2));
            color: var(--dark-text);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        body.dark-mode hr {
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .text-muted {
            color: #b0b0b0 !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        /* Enhanced Dark Mode Toggle Button */
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
            box-shadow: var(--card-shadow);
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1) rotate(180deg);
            box-shadow: var(--dark-glow);
        }

        body.dark-mode .dark-mode-toggle {
            background: var(--secondary-color);
            box-shadow: var(--dark-glow);
        }

        body.dark-mode .dark-mode-toggle:hover {
            background: var(--primary-color);
        }

        @media (max-width: 768px) {
            .dark-mode-toggle {
                width: 40px;
                height: 40px;
                bottom: 15px;
                right: 15px;
            }
        }

        /* Smooth Mode Transition */
        body.dark-mode * {
            transition: background-color var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1),
                       color var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1),
                       border-color var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1),
                       box-shadow var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1),
                       transform var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
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

        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all var(--transition-speed) cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            background: white;
            position: relative;
            overflow: hidden;
        }

        .payment-method::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(74, 144, 226, 0.05), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .payment-method:hover::before {
            transform: translateX(100%);
        }

        .payment-method:hover {
            border-color: var(--primary-color);
            transform: translateX(5px) scale(1.02);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .payment-method.selected {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.05), rgba(74, 144, 226, 0.1));
            transform: translateX(5px);
        }

        .payment-method img {
            height: 35px;
            margin-right: 1rem;
            transition: transform var(--transition-speed);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .payment-method:hover img {
            transform: scale(1.1) rotate(5deg);
        }

        .btn {
            position: relative;
            overflow: hidden;
            transition: all var(--transition-speed) cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn::before {
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

        .btn:hover::before {
            transform: translateX(100%);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #357abd);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(var(--button-hover-scale));
            box-shadow: 0 6px 12px rgba(74, 144, 226, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0) scale(var(--button-active-scale));
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-3px) scale(var(--button-hover-scale));
            box-shadow: 0 6px 12px rgba(108, 117, 125, 0.3);
        }

        .btn-secondary:active {
            transform: translateY(0) scale(var(--button-active-scale));
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all var(--transition-speed) cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
            transform: translateY(-2px);
        }

        .important-notes {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.05), rgba(255, 107, 107, 0.1));
            border-left: 4px solid var(--accent-color);
            padding: 1.25rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            animation: fadeIn 0.8s ease-out;
        }

        .important-notes li {
            margin-bottom: 0.75rem;
            position: relative;
            padding-left: 2rem;
            transition: transform var(--transition-speed);
        }

        .important-notes li:hover {
            transform: translateX(5px);
        }

        .important-notes li::before {
            content: '⚠️';
            position: absolute;
            left: 0;
            font-size: 1.2rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .total-amount {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.05), rgba(74, 144, 226, 0.1));
            border-radius: 12px;
            margin: 1.5rem 0;
            animation: pulse 2s infinite;
            position: relative;
            overflow: hidden;
        }

        .total-amount::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .card {
            animation: cardAppear 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            transform-origin: center;
        }

        @keyframes cardAppear {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        body.dark-mode .btn-primary {
            background: linear-gradient(135deg, #357abd, var(--primary-color));
        }

        body.dark-mode .btn-secondary {
            background: linear-gradient(135deg, #5a6268, #6c757d);
        }

        body.dark-mode .payment-method {
            background: #2d2d2d;
            border-color: #333;
        }

        body.dark-mode .payment-method.selected {
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(74, 144, 226, 0.2));
        }

        body.dark-mode .form-control {
            background: #2d2d2d;
            border-color: #333;
            color: white;
        }

        body.dark-mode .important-notes {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(255, 107, 107, 0.15));
        }

        body.dark-mode .total-amount {
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(74, 144, 226, 0.2));
        }

        body.dark-mode .btn-primary {
            background: linear-gradient(135deg, #357abd, var(--primary-color));
            border: none;
        }

        body.dark-mode .btn-primary:hover {
            background: linear-gradient(135deg, #2d6a9d, #357abd);
            transform: translateY(-3px) scale(var(--button-hover-scale));
        }

        body.dark-mode .btn-secondary {
            background: linear-gradient(135deg, #5a6268, #6c757d);
            border: none;
        }

        body.dark-mode .btn-secondary:hover {
            background: linear-gradient(135deg, #4a5258, #5a6268);
            transform: translateY(-3px) scale(var(--button-hover-scale));
        }

        body.dark-mode .text-muted {
            color: #b0b0b0 !important;
        }

        body.dark-mode hr {
            border-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .card-body p {
            color: #ffffff;
        }

        body.dark-mode .card-body strong {
            color: #ffffff;
        }

        @media (max-width: 768px) {
            .btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }

            .payment-method {
                padding: 1rem;
            }

            .payment-method img {
                height: 30px;
            }

            .total-amount {
                font-size: 1.5rem;
                padding: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1 class="text-center mt-4 mb-4">Payment</h1>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Booking Summary</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Bus:</strong> <?= htmlspecialchars($bus['bus_name']) ?></p>
                                <p><strong>From:</strong> <?= htmlspecialchars($bus['from_location']) ?></p>
                                <p><strong>To:</strong> <?= htmlspecialchars($bus['to_location']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Date:</strong> <?= date('l, F j, Y', strtotime($journey_date)) ?></p>
                                <p><strong>Departure:</strong> <?= date('h:i A', strtotime($bus['departure_time'])) ?></p>
                                <p><strong>Bus Type:</strong> <?= htmlspecialchars($bus['bus_type']) ?></p>
                            </div>
                        </div>
                        <hr>
                        <p><strong>Selected Seats:</strong> 
                            <?php 
                            $seat_numbers = array_map(function($seat) { 
                                return $seat['number']; 
                            }, $selected_seats);
                            echo implode(', ', $seat_numbers); 
                            ?>
                        </p>
                        <p><strong>Total Fare:</strong> BDT <?= number_format($total_fare, 2) ?></p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Payment Method</h5>
                        <form id="payment-form" action="long_route_payment_process.php" method="POST">
                            <input type="hidden" name="payment_for" value="long_route">
                            <input type="hidden" name="journey_date" value="<?= htmlspecialchars($journey_date) ?>">
                            
                            <div class="payment-methods">
                                <div class="payment-method d-flex align-items-center" data-method="bkash">
                                    <input type="radio" name="payment_method" value="bkash" id="bkash" required>
                                    <label for="bkash" class="mb-0 ml-2 d-flex align-items-center">
                                        bKash
                                    </label>
                                </div>
                                
                                <div class="payment-method d-flex align-items-center" data-method="rocket">
                                    <input type="radio" name="payment_method" value="rocket" id="rocket" required>
                                    <label for="rocket" class="mb-0 ml-2 d-flex align-items-center">
                                       
                                        Rocket
                                    </label>
                                </div>
                                
                                <div class="payment-method d-flex align-items-center" data-method="card">
                                    <input type="radio" name="payment_method" value="card" id="card" required>
                                    <label for="card" class="mb-0 ml-2 d-flex align-items-center">
                                       
                                        Card
                                    </label>
                                </div>
                            </div>

                            <!-- Dynamic Fields (Hidden by Default) -->
                            <div id="dynamic-fields" class="mt-4">
                                <!-- Account Number (Hidden by Default) -->
                                <div class="form-group" id="account-number-field" style="display: none;">
                                    <label for="account_number">Account Number</label>
                                    <input type="number" class="form-control" id="account_number" name="account_number">
                                </div>

                                <!-- PIN (Hidden by Default) -->
                                <div class="form-group" id="pin-field" style="display: none;">
                                    <label for="pin">PIN</label>
                                    <input type="password" class="form-control" id="pin" name="pin">
                                </div>

                                <!-- Card Information (Hidden by Default) -->
                                <div id="card-fields" style="display: none;">
                                    <div class="form-group">
                                        <label for="card_number">Card Number</label>
                                        <input type="number" class="form-control" id="card_number" name="card_number">
                                    </div>
                                    <div class="form-group">
                                        <label for="expiry_date">Expiry Date</label>
                                        <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                    </div>
                                    <div class="form-group">
                                        <label for="cvv">CVV</label>
                                        <input type="number" class="form-control" id="cvv" name="cvv">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block mt-4">
                                Pay BDT <?= number_format($total_fare, 2) ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Passenger Information</h5>
                        <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                        
                        <hr>
                        
                        <h6>Important Notes:</h6>
                        <ul>
                            <li>Please arrive at least 30 minutes before departure.</li>
                            <li>Carry a valid ID card for verification.</li>
                            <li>No refunds for missed buses.</li>
                            <li>Cancellation policy applies as per terms.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
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
            
            // Payment method selection
            $('.payment-method').click(function() {
                $('.payment-method').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);

                // Show/hide dynamic fields based on payment method
                const method = $(this).data('method');
                if (method === 'card') {
                    $('#card-fields').show();
                    $('#pin-field').hide();
                    $('#account-number-field').hide();
                } else {
                    $('#card-fields').hide();
                    $('#pin-field').show();
                    $('#account-number-field').show();
                }
            });

            const paymentMethods = document.querySelectorAll('.payment-method input[type="radio"]');
            const cardFields = document.getElementById('card-fields');
            const pinField = document.getElementById('pin-field');
            const accountNumberField = document.getElementById('account-number-field');

            paymentMethods.forEach(method => {
                method.addEventListener('change', function () {
                    // Hide all fields by default
                    cardFields.style.display = 'none';
                    pinField.style.display = 'none';
                    accountNumberField.style.display = 'none';

                    // Reset required attributes
                    document.getElementById('card_number').required = false;
                    document.getElementById('expiry_date').required = false;
                    document.getElementById('cvv').required = false;
                    document.getElementById('account_number').required = false;
                    document.getElementById('pin').required = false;

                    if (this.value === 'card') {
                        // Show card fields
                        cardFields.style.display = 'block';

                        // Update required attributes for card fields
                        document.getElementById('card_number').required = true;
                        document.getElementById('expiry_date').required = true;
                        document.getElementById('cvv').required = true;
                    } else {
                        // Show account number and PIN fields (for bKash and Rocket)
                        pinField.style.display = 'block';
                        accountNumberField.style.display = 'block';

                        // Update required attributes for bKash/Rocket fields
                        document.getElementById('account_number').required = true;
                        document.getElementById('pin').required = true;
                    }
                });
            });
        });
    </script>
</body>
</html>