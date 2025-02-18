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

// Reset payment completed flag
$_SESSION['payment_completed'] = false;

// Fetch available trains from the database
$trains = [];
$sql = "SELECT * FROM trains";
$result = $conn2->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $trains[] = $row;
    }
}

// Fetch available stations from the database
$stations = [];
$sql = "SELECT * FROM stations";
$result = $conn2->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $stations[] = $row;
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
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-light);
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
            background-image: url("images/Railway Background Image.jpg");
            background-size: cover;
            background-position: center;
        }

        .dark-mode {
            --bg-light: var(--bg-dark);
            --text-light: var(--text-dark);
        }

        .card {
            transition: transform 0.9s, margin 0.9s;
            background-color: var(--card-bg-dark);
            color: var(--text-dark);
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 123, 255, 0.2);
        }

        .card:hover {
            transform: scale(1.07);
            transition: transform 0.7s;
            box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
        }

        .dark-mode .card {
            background-color: var(--card-bg-dark);
            color: var(--text-dark);
        }

        .footer {
            background-color: #343a40;
            color: #ffffff;
        }

        .footer.dark-mode {
            background-color: var(--footer-bg-dark);
            color: var(--text-dark);
        }

        .text-center.mt-5 {
            margin-bottom: 30px;
        }

        .form-control {
            transition: box-shadow 0.3s ease-in-out;
            background-color: rgba(255, 255, 255, 0.8);
            border: none;
            border-radius: 10px;
        }

        .form-control:focus {
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
            background-color: rgba(255, 255, 255, 1);
        }

        .btn-primary {
            transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .navbar {
            background-color: var(--bg-light);
            transition: background-color 0.3s ease-in-out;
        }

        .navbar .nav-link {
            color: var(--text-light);
            position: relative;
            transition: color 0.3s ease-in-out;
            margin-right: 15px;
        }

        .navbar .nav-link:hover,
        .navbar .nav-item.active .nav-link {
            color: var(--hover-light);
        }

        .navbar .nav-link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -5px;
            width: 100%;
            height: 3px;
            background-color: var(--hover-light);
            transform: scaleX(0);
            transition: transform 0.3s ease-in-out;
        }

        .navbar .nav-link:hover::after,
        .navbar .nav-item.active .nav-link::after {
            transform: scaleX(1);
        }

        .dark-mode .navbar {
            background-color: var(--bg-dark);
        }

        .dark-mode .navbar .nav-link {
            color: var(--text-dark);
        }

        .dark-mode .navbar .nav-link:hover,
        .dark-mode .navbar .nav-item.active .nav-link {
            color: var(--hover-dark);
        }

        .dark-mode .navbar .nav-link::after {
            background-color: var(--hover-dark);
        }

        .profile-img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }

        #navbarTitle {
            color: var(--text-light);
            transition: color 0.3s ease-in-out;
        }

        .dark-mode #navbarTitle {
            color: var(--text-dark);
        }

        .seat {
            width: 30px;
            height: 30px;
            margin: 5px;
            background-color: #007bff;
            color: #fff;
            text-align: center;
            line-height: 30px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
            margin-bottom: 20px; /* Add space below the seat card */
        }

        .seat.selected {
            background-color: #28a745;
        }

        .seat.reserved {
            background-color: #dc3545;
            cursor: not-allowed;
        }

        .seat:hover:not(.reserved) {
            background-color: #0056b3;
        }

        .timer {
            font-size: 1.5rem;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center" style="color: var(--text-light);">Buy Train Tickets</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <form id="trainForm" action="train_reservation.php" method="POST">
                            <div class="form-group">
                                <label for="train">Select Train</label>
                                <select class="form-control" id="train" name="train" required>
                                    <?php foreach ($trains as $train): ?>
                                        <option value="<?php echo htmlspecialchars($train['train_id']); ?>">
                                            <?php echo htmlspecialchars($train['train_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="date">Select Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label for="compartment">Select Compartment</label>
                                <select class="form-control" id="compartment" name="compartment" required>
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?php echo $i; ?>">Compartment <?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="station">Select Destination Station</label>
                                <select class="form-control" id="station" name="station" required>
                                    <?php foreach ($stations as $station): ?>
                                        <option value="<?php echo htmlspecialchars($station['id']); ?>">
                                            <?php echo htmlspecialchars($station['station_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary btn-block" id="selectSeatsBtn">Select Seats</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card" id="seatSelectionCard" style="display: none;">
                    <div class="card-body">
                        <h5 class="card-title">Select Your Seats</h5>
                        <div class="d-flex flex-wrap">
                            <?php for ($i = 1; $i <= 50; $i++): ?>
                                <div class="seat" data-seat="<?php echo $i; ?>"><?php echo $i; ?></div>
                            <?php endfor; ?>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-success" id="reserveBtn">Reserve</button>
                            <button type="button" class="btn btn-danger" id="cancelBtn">Cancel</button>
                        </div>
                        <div class="mt-3 timer" id="timer" style="display: none;">15:00</div>
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
                            window.location.href = 'payment_gateway.php';
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