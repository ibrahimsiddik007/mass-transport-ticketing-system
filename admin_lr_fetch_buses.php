<?php
include 'db.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows_per_page = 10;
$offset = ($page - 1) * $rows_per_page;

// Fetch buses with pagination
$query = "SELECT SQL_CALC_FOUND_ROWS bus_id, bus_name, from_location, to_location, TIME_FORMAT(departure_time, '%h:%i %p') AS departure_time, journey_date, fare, total_seats, bus_type FROM long_route_buses LIMIT $rows_per_page OFFSET $offset";
$result = $conn3->query($query);
$buses = [];
while ($row = $result->fetch_assoc()) {
    $buses[] = $row;
}

// Get total rows for pagination
$total_rows_result = $conn3->query("SELECT FOUND_ROWS() AS total_rows");
$total_rows = $total_rows_result->fetch_assoc()['total_rows'];
$total_pages = ceil($total_rows / $rows_per_page);

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'data' => $buses,
    'total_pages' => $total_pages,
    'current_page' => $page
]);
?>