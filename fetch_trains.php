<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_GET['start_point']) || !isset($_GET['end_point'])) {
    echo json_encode([]);
    exit;
}

$startPoint = $_GET['start_point'];
$endPoint = $_GET['end_point'];

// Fetch trains based on start and end points
$query = "SELECT * FROM trains WHERE start_point = ? AND end_point = ?";
$stmt = $conn2->prepare($query);
$stmt->bind_param("ss", $startPoint, $endPoint);
$stmt->execute();
$result = $stmt->get_result();

$trains = [];
while ($row = $result->fetch_assoc()) {
    $trains[] = $row;
}

$stmt->close();

echo json_encode($trains);
?>