<?php
include 'db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $startLocation = $_POST['start_point'];
    $endLocation = $_POST['end_point'];

    // Calculate fare based on start and end locations
    $stmt = $conn->prepare("SELECT fare FROM ticket_routes WHERE start_point = ? AND end_point = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $startLocation, $endLocation);
        $stmt->execute();
        $stmt->bind_result($fare);
        $stmt->fetch();
        $stmt->close();
        echo $fare;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>