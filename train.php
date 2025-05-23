<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Set the redirect URL to train.php only if it's not already set
    if (!isset($_SESSION['redirect_to'])) {
        $_SESSION['redirect_to'] = 'train.php';
    }
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Check for pending reservations
$query = "SELECT * FROM reservations WHERE user_id = ? AND status = 'pending' AND expiry_time > NOW()";
$stmt = $conn2->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Redirect to reservation status page if there is a pending reservation
    header('Location: reservation_status.php');
    exit;
}

$stmt->close();

// Reset payment completed flag
$_SESSION['payment_completed'] = false;

// Fetch available stations from the database
$stations = [];
$sql = "SELECT DISTINCT start_point FROM trains UNION SELECT DISTINCT end_point FROM trains";
$result = $conn2->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $stations[] = $row['start_point'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Train Reservation</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-light: #ffffff;
            --bg-dark: #2c2c2c;
            --text-light: #000000;
            --text-dark: #e0e0e0;
            --hover-light: #007bff;
            --hover-dark: #007bff;
            --card-bg-dark: rgba(58, 58, 58, 0.8);
            --footer-bg-dark: #3a3a3a;
            --notice-bg-dark: #444444;
            --primary-gradient: linear-gradient(135deg, #007bff, #00bfff);
            --secondary-gradient: linear-gradient(135deg, #6c757d, #343a40);
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("images/Railway Background Image.jpg");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: -1;
            animation: fadeIn 2s ease-in-out;
        }

        body {
            color: #fff;
            animation: fadeIn 1.5s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .card {
            transition: all 0.5s ease;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
        }

        .card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            background: rgba(0, 0, 0, 0.5);
        }

        .form-control {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(0, 0, 0, 0.4);
            border-color: #007bff;
            box-shadow: 0 0 15px rgba(0, 123, 255, 0.3);
            transform: scale(1.02);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 123, 255, 0.3);
        }

        .btn-primary::after {
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

        .btn-primary:hover::after {
            transform: translateX(100%);
        }

        .notice-container {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            margin: 20px 0;
            animation: slideInDown 1s ease-out;
        }

        .notice {
            display: inline-block;
            animation: scroll 15s linear infinite;
            padding: 15px;
            color: #fff;
        }

        @keyframes scroll {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        .form-group label {
            color: #fff;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-group:focus-within label {
            color: #007bff;
            transform: translateY(-5px);
        }

        select.form-control option {
            background: #2c2c2c;
            color: #fff;
        }

        .container {
            animation: fadeInUp 1s ease-out;
        }

        .navbar {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInDown 1s ease-out;
        }

        .nav-link {
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #007bff;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .dark-mode-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(5px);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            animation: fadeIn 1s ease-out;
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1);
            background: rgba(0, 0, 0, 0.5);
        }

        .footer {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: auto;
            animation: fadeInUp 1s ease-out;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

      <!-- Notice Section -->
      <div class="notice-container">
        <div class="notice">
            <strong>Notice:</strong> Please make sure that your payment is done under 15 minutes otherwise you will not be able to confirm your seat.
        </div>
    </div>


    <div class="container mt-5">
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <form id="trainForm" action="reserve_seats.php" method="POST">
                            <div class="form-group">
                                <label for="buy_ticket" style="font-size: 1.5rem;">Buy/Reserve Ticket</label>
                                <br>
                                <label for="start_point">Start Point</label>
                                <select class="form-control" id="start_point" name="start_point" required>
                                    <option value="">Select Start Point</option>
                                    <?php foreach ($stations as $station): ?>
                                        <option value="<?php echo htmlspecialchars($station); ?>">
                                            <?php echo htmlspecialchars($station); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="end_point">End Point</label>
                                <select class="form-control" id="end_point" name="end_point" required>
                                    <option value="">Select End Point</option>
                                    <?php foreach ($stations as $station): ?>
                                        <option value="<?php echo htmlspecialchars($station); ?>">
                                            <?php echo htmlspecialchars($station); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="train">Select Train</label>
                                <select class="form-control" id="train" name="train" required>
                                    <option value="">Select Train</option>
                                    <!-- Trains will be populated based on start and end points -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="date">Select Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label for="ticket_count">Number of Tickets</label>
                                <input type="number" class="form-control" id="ticket_count" name="ticket_count" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Reserve Seats</button>
                        </form>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>



    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentTheme = localStorage.getItem('theme') || 'light';

            if (currentTheme === 'dark') {
                document.body.classList.add('dark-mode');
            }

            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    document.body.classList.toggle('dark-mode');
                    const theme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
                    localStorage.setItem('theme', theme);
                });
            }

            const startPointSelect = document.getElementById('start_point');
            const endPointSelect = document.getElementById('end_point');
            const trainSelect = document.getElementById('train');

            function fetchTrains() {
                const startPoint = startPointSelect.value;
                const endPoint = endPointSelect.value;

                if (startPoint && endPoint) {
                    $.ajax({
                        url: 'fetch_trains.php',
                        method: 'GET',
                        data: {
                            start_point: startPoint,
                            end_point: endPoint
                        },
                        success: function(response) {
                            const trains = JSON.parse(response);
                            trainSelect.innerHTML = '<option value="">Select Train</option>';
                            trains.forEach(train => {
                                const option = document.createElement('option');
                                option.value = train.train_id;
                                option.textContent = `${train.train_name} (${train.start_point} to ${train.end_point} at ${train.departure_time})`;
                                trainSelect.appendChild(option);
                            });
                        }
                    });
                }
            }

            startPointSelect.addEventListener('change', fetchTrains);
            endPointSelect.addEventListener('change', fetchTrains);

            const selectSeatsBtn = document.getElementById('selectSeatsBtn');
            const seatSelectionCard = document.getElementById('seatSelectionCard');
            const seats = document.querySelectorAll('.seat');
            const reserveBtn = document.getElementById('reserveBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const timer = document.getElementById('timer');
            let selectedSeats = [];
            let countdown;

            selectSeatsBtn.addEventListener('click', function() {
                seatSelectionCard.style.display = 'block';
            });

            seats.forEach(seat => {
                seat.addEventListener('click', function() {
                    if (!seat.classList.contains('reserved')) {
                        seat.classList.toggle('selected');
                        const seatNumber = seat.getAttribute('data-seat');
                        if (seat.classList.contains('selected')) {
                            selectedSeats.push(seatNumber);
                            if (selectedSeats.length === 1) {
                                timer.style.display = 'block';
                                startTimer(15 * 60, timer);
                            }
                        } else {
                            selectedSeats = selectedSeats.filter(s => s !== seatNumber);
                            if (selectedSeats.length === 0) {
                                clearInterval(countdown);
                                timer.style.display = 'none';
                            }
                        }
                    }
                });
            });

            reserveBtn.addEventListener('click', function() {
                if (selectedSeats.length > 0) {
                    const postData = {
                        seats: selectedSeats,
                        trainId: $('#train').val(),
                        date: $('#date').val(),
                        compartment: $('#compartment').val()
                    };
                    console.log(postData); // Log the data being sent
                    $.ajax({
                        url: 'reserve_seats.php',
                        method: 'POST',
                        data: postData,
                        success: function(response) {
                            if (response === 'ok') {
                                clearInterval(countdown);
                                timer.style.display = 'none';
                                alert('Seats reserved. Proceed to payment.');
                                window.location.href = 'reservation_status.php';
                            } else {
                                alert('Reservation failed.');
                            }
                        }
                    });
                } else {
                    alert('Please select at least one seat.');
                }
            });

            cancelBtn.addEventListener('click', function() {
                $.ajax({
                    url: 'cancel_reservation.php',
                    method: 'POST',
                    success: function(response) {
                        if (response === 'ok') {
                            clearInterval(countdown);
                            timer.style.display = 'none';
                            alert('Reservation cancelled.');
                            selectedSeats.forEach(seatNumber => {
                                const seat = document.querySelector(`.seat[data-seat="${seatNumber}"]`);
                                seat.classList.remove('selected');
                            });
                            selectedSeats = [];
                            seatSelectionCard.style.display = 'none';
                        } else {
                            alert('Cancellation failed.');
                        }
                    }
                });
            });

            function startTimer(duration, display) {
                let timer = duration, minutes, seconds;
                countdown = setInterval(function() {
                    minutes = parseInt(timer / 60, 10);
                    seconds = parseInt(timer % 60, 10);

                    minutes = minutes < 10 ? "0" + minutes : minutes;
                    seconds = seconds < 10 ? "0" + seconds : seconds;

                    display.textContent = minutes + ":" + seconds;

                    if (--timer < 0) {
                        clearInterval(countdown);
                        alert('Reservation time expired!');
                        cancelBtn.click();
                    }
                }, 1000);
            }

            function checkReservationStatus() {
                $.ajax({
                    url: 'check_reservation_status.php',
                    method: 'GET',
                    success: function(response) {
                        const reservations = JSON.parse(response);
                        if (reservations.length > 0) {
                            const remainingTime = reservations[0].remaining_time;
                            startTimer(remainingTime, timer);
                            selectedSeats = reservations.map(r => r.seat_number);
                            selectedSeats.forEach(seatNumber => {
                                const seat = document.querySelector(`.seat[data-seat="${seatNumber}"]`);
                                seat.classList.add('selected');
                            });
                            seatSelectionCard.style.display = 'block';
                        }
                    }
                });
            }

            checkReservationStatus();
        });
    </script>
</body>
</html>