<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $startLocation = $_POST['startLocation'];
    $endLocation = $_POST['endLocation'];
    $mobileNumber = $_POST['mobile_number'];
    $amount = $_POST['amount'];
    $pin = $_POST['pin'];
    $transactionID = uniqid('txn_'); // Generate a unique transaction ID
    $timestamp = date('Y-m-d H:i:s');

    // Simulate payment processing delay
    sleep(5); // Delay for 5 seconds

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

    // Insert transaction into the database
    $sql = "INSERT INTO transactions (start_location, end_location, mobile_number, fare, transaction_id, created_at) VALUES ('$startLocation', '$endLocation', '$mobileNumber', '$amount', '$transactionID', '$timestamp')";
    if ($conn->query($sql) === TRUE) {
        // Redirect to a receipt page
        header("Location: receipt.php?startLocation=$startLocation&endLocation=$endLocation&fare=$amount&created_at=$timestamp");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>