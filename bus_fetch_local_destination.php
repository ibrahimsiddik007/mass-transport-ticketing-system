<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_GET['origin'])) {
    echo json_encode([]);
    exit;
}

$origin = $_GET['origin'];

// Fetch destinations based on the selected origin
$query = "SELECT DISTINCT destination FROM local_routes WHERE origin = ?";
$stmt = $conn3->prepare($query);
$stmt->bind_param("s", $origin);
$stmt->execute();
$result = $stmt->get_result();

$destinations = [];
while ($row = $result->fetch_assoc()) {
    $destinations[] = $row['destination'];
}

echo json_encode($destinations);
?>