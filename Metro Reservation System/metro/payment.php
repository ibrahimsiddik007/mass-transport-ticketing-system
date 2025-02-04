<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>bKash Payment Gateway</title>
    <link rel="stylesheet" href="assets/css/payment.css">
</head>
<body>
    <?php
    $startLocation = $_POST['startLocation'];
    $endLocation = $_POST['endLocation'];

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

    // Fetch fare from the database
    $sql = "SELECT fare FROM ticket_routes WHERE start_point = '$startLocation' AND end_point = '$endLocation'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $fare = $row['fare'];
    } else {
        die("No fare found for the selected route.");
    }

    $conn->close();
    ?>
    <div class="bkash-container">
        <div class="bkash-header">
            <img src="assets\logos\bkash logo.png" alt="bKash Logo" class="bkash-logo">
            <h2 class="bkash-title">bKash Payment Gateway</h2>
        </div>
        <div class="bkash-body">
            <form action="process_payment.php" method="POST">
                <div class="input-group">
                    <label for="mobile-number">Mobile Number</label>
                    <input type="text" id="mobile-number" name="mobile_number" placeholder="01XXXXXXXXX" required>
                </div>
                <div class="input-group">
                    <label for="amount">Amount (BDT)</label>
                    <input type="number" id="amount" name="amount" value="<?php echo $fare; ?>" readonly>
                </div>
                <div class="input-group">
                    <label for="pin">PIN</label>
                    <input type="password" id="pin" name="pin" placeholder="Enter PIN" required>
                </div>
                <input type="hidden" name="startLocation" value="<?php echo $startLocation; ?>">
                <input type="hidden" name="endLocation" value="<?php echo $endLocation; ?>">
                <button type="submit" class="pay-button">Pay Now</button>
            </form>
        </div>
        <div class="bkash-footer">
            <p>Powered by bKash</p>
        </div>
    </div>
</body>
</html>