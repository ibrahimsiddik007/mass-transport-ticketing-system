<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Metro Ticket</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets\css\metro_ticket.css">
</head>
<body>
    <?php include 'F:\Personal\Mass Transport Ticketing System\nav.php'; ?>

    <?php
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "mass transport ticketing system";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch station names
    $sql = "SELECT s_name FROM stations";
    $result = $conn->query($sql);
    $stations = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $stations[] = $row['s_name'];
        }
    }
    $conn->close();
    ?>

    <div class="container">
        <div class="ticket-form">
            <h2>Purchase Metro Ticket</h2>
            <form action="payment.php" method="POST">
    <div class="form-group">
        <label for="startLocation">Start Location</label>
        <select class="form-control" id="startLocation" name="startLocation">
            <?php foreach ($stations as $station): ?>
                <option value="<?php echo $station; ?>"><?php echo $station; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="endLocation">End Location</label>
        <select class="form-control" id="endLocation" name="endLocation">
            <?php foreach ($stations as $station): ?>
                <option value="<?php echo $station; ?>"><?php echo $station; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Purchase Ticket</button>
    <div class="warning">
        <p>Warning: This ticket is valid for the next 8 hours since the purchase.</p>
    </div>
</form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>