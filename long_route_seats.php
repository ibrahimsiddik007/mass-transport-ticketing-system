<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'long_route.php';
    header('Location: login.php');
    exit;
}

// Get bus ID and journey date from the URL
$bus_id = isset($_GET['bus_id']) ? intval($_GET['bus_id']) : 0;
$journey_date = isset($_GET['journey_date']) ? $_GET['journey_date'] : '';

if (empty($bus_id) || empty($journey_date)) {
    echo "Invalid request. Please go back and select a bus and journey date.";
    exit;
}

// Validate the journey date format (optional)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $journey_date)) {
    echo "Invalid journey date format.";
    exit;
}

// Fetch bus details (optional, for display purposes)
$query = "SELECT bus_name, from_location, to_location, TIME_FORMAT(departure_time, '%h:%i %p') AS departure_time, fare 
          FROM long_route_buses 
          WHERE bus_id = ?";
$stmt = $conn3->prepare($query);
$stmt->bind_param("i", $bus_id);
$stmt->execute();
$result = $stmt->get_result();
$bus = $result->fetch_assoc();

if (!$bus) {
    echo "Bus not found.";
    exit;
}

// Get all seats for this bus
$seats_query = "SELECT s.* 
                FROM long_route_seats s
                WHERE s.bus_id = ?
                ORDER BY s.seat_number";

$stmt = $conn3->prepare($seats_query);
$stmt->bind_param("i", $bus_id);
$stmt->execute();
$seats_result = $stmt->get_result();

// Get all booked seats for this bus on this date
$booked_query = "SELECT seat_numbers 
                 FROM long_route_transactions 
                 WHERE bus_id = ? AND journey_date = ? AND payment_status = 'completed'";
                 
$stmt = $conn3->prepare($booked_query);
$stmt->bind_param("is", $bus_id, $journey_date);
$stmt->execute();
$booked_result = $stmt->get_result();

// Create array of all booked seats
$booked_seats = [];
while ($booking = $booked_result->fetch_assoc()) {
    // Assuming seat_numbers is stored as comma-separated values like "A1,B3,C5"
    $seats = explode(",", $booking['seat_numbers']);
    foreach ($seats as $seat) {
        $booked_seats[] = trim($seat);
    }
}

// Create a 2D array to represent the seating layout
$seating = [];
while ($seat = $seats_result->fetch_assoc()) {
    $letter = substr($seat['seat_number'], 0, 1); // A, B, C, D
    $row = substr($seat['seat_number'], 1); // 0 to 9
    
    if (!isset($seating[$row])) {
        $seating[$row] = [];
    }
    
    // Check if this seat is booked
    $status = in_array($seat['seat_number'], $booked_seats) ? 'booked' : 'available';
    
    $seating[$row][$letter] = [
        'seat_id' => $seat['seat_id'],
        'seat_number' => $seat['seat_number'],
        'status' => $status
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats - Mass Transport Ticketing System</title>
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
        .seat {
            width: 45px;
            height: 45px;
            margin: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
            border: 2px solid #ccc;
        }
        .seat-available {
            background-color: #ffffff;
            color: #333;
        }
        body.dark-mode .seat-available {
            background-color: #333;
            color: #fff;
        }
        .seat-booked {
            background-color: #dc3545;
            color: white;
            cursor: not-allowed;
            pointer-events: none; /* Prevent clicks */
        }
        .seat-selected {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }
        .seat-row {
            margin-bottom: 10px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .aisle {
            width: 20px;
            display: inline-block;
        }
        .row-number {
            width: 20px;
            margin-right: 10px;
            font-weight: bold;
        }
        .bus-front {
            width: 80%;
            height: 50px;
            background-color: #007bff;
            margin: 0 auto 20px;
            border-radius: 10px 10px 0 0;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
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
        <h1 class="text-center mt-4 mb-4">Select Your Seats</h1>
        <div class="card">
            <div class="card-body">
                <h3 class="card-title"><?= htmlspecialchars($bus['bus_name']) ?></h3>
                <p class="card-text">
                    <strong>Route:</strong> <?= htmlspecialchars($bus['from_location']) ?> to <?= htmlspecialchars($bus['to_location']) ?><br>
                    <strong>Departure Time:</strong> <?= htmlspecialchars($bus['departure_time']) ?><br>
                    <strong>Journey Date:</strong> <?= htmlspecialchars($journey_date) ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title text-center">Choose Your Preferred Seats</h5>
            <div class="bus-layout">
                <div class="bus-front">FRONT (Driver)</div>
                
                <?php 
                $letters = ['A', 'B', 'C', 'D']; // Define the seat columns
                for ($row = 0; $row < 11; $row++): ?>
                    <div class="seat-row">
                        <span class="row-number"><?= $row ?></span>
                        
                        <?php foreach ($letters as $index => $letter): ?>
                            <?php if (!isset($seating[$row][$letter])) continue; ?>
                            <?php 
                                $seat = $seating[$row][$letter];
                                $seatClass = $seat['status'] == 'available' ? 'seat-available' : 'seat-booked';
                                $disabledAttr = $seat['status'] == 'booked' ? 'disabled' : '';
                            ?>
                            <?php if ($letter == 'B'): ?>
                                <div class="seat <?= $seatClass ?>" data-seat-id="<?= $seat['seat_id'] ?>" <?= $disabledAttr ?>>
                                    <?= $seat['seat_number'] ?>
                                </div>
                                <div class="aisle"></div>
                            <?php else: ?>
                                <div class="seat <?= $seatClass ?>" data-seat-id="<?= $seat['seat_id'] ?>" <?= $disabledAttr ?>>
                                    <?= $seat['seat_number'] ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
                
                <div class="seat-info mt-4">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="seat seat-available mr-3">A1</div> Available
                        <div class="seat seat-selected mx-3">A1</div> Selected
                        <div class="seat seat-booked mx-3">A1</div> Booked
                    </div>
                </div>
            </div>
            
            <form id="booking-form" action="long_route_payment.php" method="POST">
                <input type="hidden" name="bus_id" value="<?= $bus_id ?>">
                <input type="hidden" name="journey_date" value="<?= htmlspecialchars($journey_date) ?>">
                <input type="hidden" name="selected_seats" id="selected-seats" value="">
                <input type="hidden" name="total_fare" id="total-fare" value="">
                
                <div class="form-group mt-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Selected Seats: <span id="selected-seats-display">None</span></h6>
                            <h6>Total Fare: BDT <span id="total-fare-display">0.00</span></h6>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block mt-3" id="proceed-button" disabled>
                    Proceed to Payment
                </button>
            </form>
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

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('dark-mode') === 'true') {
                document.body.classList.add('dark-mode');
            }
            
            document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
        });
        
        // Seat selection logic
        $(document).ready(function() {
            const farePerSeat = <?= $bus['fare'] ?>; // Get the fare per seat from PHP
            const selectedSeats = []; // Array to store selected seats

            // Handle seat selection
            $('.seat-available').on('click', function() {
                const seatId = $(this).data('seat-id'); // Get the seat ID
                const seatNumber = $(this).text(); // Get the seat number

                if ($(this).hasClass('seat-selected')) {
                    // Deselect the seat
                    $(this).removeClass('seat-selected');

                    // Remove the seat from the selectedSeats array
                    const index = selectedSeats.findIndex(seat => seat.id === seatId);
                    if (index !== -1) {
                        selectedSeats.splice(index, 1);
                    }
                } else {
                    // Select the seat
                    $(this).addClass('seat-selected');
                    selectedSeats.push({
                        id: seatId,
                        number: seatNumber
                    });
                }

                // Update the booking summary
                updateBookingSummary();
            });

            // Function to update the booking summary
            function updateBookingSummary() {
                if (selectedSeats.length > 0) {
                    const seatNumbers = selectedSeats.map(seat => seat.number).join(', ');
                    const totalFare = selectedSeats.length * farePerSeat;

                    // Update the display
                    $('#selected-seats-display').text(seatNumbers);
                    $('#total-fare-display').text(totalFare.toFixed(2));

                    // Update hidden inputs for form submission
                    $('#selected-seats').val(JSON.stringify(selectedSeats));
                    $('#total-fare').val(totalFare);

                    // Enable the proceed button
                    $('#proceed-button').prop('disabled', false);
                } else {
                    // No seats selected
                    $('#selected-seats-display').text('None');
                    $('#total-fare-display').text('0.00');
                    $('#selected-seats').val('');
                    $('#total-fare').val('');

                    // Disable the proceed button
                    $('#proceed-button').prop('disabled', true);
                }
            }
        });
    </script>
</body>
</html>