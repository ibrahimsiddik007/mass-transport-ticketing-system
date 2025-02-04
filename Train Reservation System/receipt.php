<?php
include 'db/db_connection.php';
include 'phpqrcode/qrlib.php';

$train_id = $_GET['train_id'];
$seats = explode(',', $_GET['seats']);

$sql = "SELECT * FROM reservations WHERE train_id = $train_id AND seat_number IN (" . implode(',', $seats) . ")";
$result = $conn->query($sql);
$reservation = $result->fetch_assoc();

$qr_data = "Train ID: $train_id\nSeats: " . implode(',', $seats) . "\nName: " . $reservation['customer_name'] . "\nEmail: " . $reservation['customer_email'];
QRcode::png($qr_data, 'qrcode.png');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Receipt</h2>
        <p>Train ID: <?php echo $train_id; ?></p>
        <p>Seats: <?php echo implode(',', $seats); ?></p>
        <p>Name: <?php echo $reservation['customer_name']; ?></p>
        <p>Email: <?php echo $reservation['customer_email']; ?></p>
        <img src="qrcode.png" alt="QR Code">
        <br>
        <a href="routes.php" class="btn btn-primary">Back to Routes</a>
    </div>
</body>
</html>