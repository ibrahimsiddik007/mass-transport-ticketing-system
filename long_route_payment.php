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
        .payment-method {
            cursor: pointer;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        .payment-method:hover, .payment-method.selected {
            border-color: #007bff;
            background-color: rgba(0, 123, 255, 0.1);
        }
        body.dark-mode .payment-method:hover, 
        body.dark-mode .payment-method.selected {
            border-color: #bb86fc;
            background-color: rgba(187, 134, 252, 0.1);
        }
        .payment-method img {
            height: 30px;
            margin-right: 10px;
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
        body.dark-mode .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
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
                                        <img src="images/bkash.png" alt="bKash">
                                        bKash
                                    </label>
                                </div>
                                
                                <div class="payment-method d-flex align-items-center" data-method="rocket">
                                    <input type="radio" name="payment_method" value="rocket" id="rocket" required>
                                    <label for="rocket" class="mb-0 ml-2 d-flex align-items-center">
                                        <img src="images/rocket.png" alt="Rocket">
                                        Rocket
                                    </label>
                                </div>
                                
                                <div class="payment-method d-flex align-items-center" data-method="card">
                                    <input type="radio" name="payment_method" value="card" id="card" required>
                                    <label for="card" class="mb-0 ml-2 d-flex align-items-center">
                                        <img src="images/card.png" alt="Card">
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
        // Dark mode toggle
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('dark-mode', document.body.classList.contains('dark-mode'));
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (localStorage.getItem('dark-mode') === 'true') {
                document.body.classList.add('dark-mode');
            }
            
            document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
            
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