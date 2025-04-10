<?php
include 'db.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows_per_page = 10;
$offset = ($page - 1) * $rows_per_page;

// Fetch compartments with pagination
$compartments = [];
$query = "SELECT * FROM compartments LIMIT $rows_per_page OFFSET $offset";
$result = $conn2->query($query);
while ($row = $result->fetch_assoc()) {
    $compartments[] = $row;
}

// Get total rows for pagination
$total_rows_result = $conn2->query("SELECT COUNT(*) AS total_rows FROM compartments");
$total_rows = $total_rows_result->fetch_assoc()['total_rows'];
$total_pages = ceil($total_rows / $rows_per_page);

header('Content-Type: application/json');
echo json_encode([
    'data' => $compartments,
    'total_pages' => $total_pages,
    'current_page' => $page
]);
?>