<?php
include 'db/db_connection.php';
include 'nav.php';

$train_id = $_GET['train_id'];

// Fetch reserved seats from the reservations table
$sql_reservations = "SELECT seat_number FROM reservations WHERE train_id = $train_id AND (status = 'reserved' OR status = 'paid')";
$result_reservations = $conn->query($sql_reservations);
$reserved_seats = [];
while($row = $result_reservations->fetch_assoc()) {
    $reserved_seats[] = (int)$row['seat_number']; // Ensure seat numbers are integers
}
?>

<div class="container">
    <h2>Select a Seat</h2>
    <div id="seat-map">
        <!-- Seat map will be generated here -->
    </div>
    <div id="timer" style="display: none;">
        <p>Time left: <span id="time-left">15:00</span></p>
    </div>
    <button id="confirm-seat" class="btn btn-success" disabled>Confirm Seat</button>
</div>

<script>
    const reservedSeats = <?php echo json_encode($reserved_seats); ?>;
    const totalSeats = 100; // Fixed total seats
    const seatMap = document.getElementById('seat-map');
    let timer;
    let timeLeft = 15 * 60; // 15 minutes in seconds

    function startTimer() {
        document.getElementById('timer').style.display = 'block';
        timer = setInterval(function() {
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('time-left').innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            if (timeLeft <= 0) {
                clearInterval(timer);
                alert('Time is up! Reservation canceled.');
                window.location.href = 'routes.php';
            }
        }, 1000);
    }

    for (let i = 1; i <= totalSeats; i++) {
        const seat = document.createElement('div');
        seat.classList.add('seat');
        seat.innerText = i;
        if (reservedSeats.includes(i)) {
            seat.classList.add('reserved');
            seat.style.pointerEvents = 'none'; // Disable click event for reserved seats
        }
        seat.addEventListener('click', function() {
            if (!seat.classList.contains('reserved')) {
                seat.classList.toggle('selected');
                document.getElementById('confirm-seat').disabled = false;
                if (!timer) {
                    startTimer();
                }
            }
        });
        seatMap.appendChild(seat);
    }

    document.getElementById('confirm-seat').addEventListener('click', function() {
        const selectedSeats = document.querySelectorAll('.seat.selected');
        if (selectedSeats.length > 0) {
            const seatNumbers = Array.from(selectedSeats).map(seat => seat.innerText);
            window.location.href = `payment.php?train_id=<?php echo $train_id; ?>&seats=${seatNumbers.join(',')}`;
        } else {
            alert('Please select a seat.');
        }
    });
</script>

<style>
    #seat-map {
        display: grid;
        grid-template-columns: repeat(10, 1fr);
        gap: 10px;
    }

    .seat {
        width: 30px;
        height: 30px;
        background-color: #ccc;
        text-align: center;
        line-height: 30px;
        cursor: pointer;
    }

    .seat.selected {
        background-color: #6c757d;
    }

    .seat.reserved {
        background-color: #dc3545;
        cursor: not-allowed;
    }
</style>