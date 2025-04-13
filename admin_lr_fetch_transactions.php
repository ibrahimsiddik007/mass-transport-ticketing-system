<?php
include 'db.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows_per_page = 10;
$offset = ($page - 1) * $rows_per_page;

// STEP 1: First, fetch all users from conn1 (users database)
$users = [];
$result_users = $conn1->query("SELECT id, name FROM users");
while ($user_row = $result_users->fetch_assoc()) {
    $users[$user_row['id']] = $user_row['name'];
}

// STEP 2: Now fetch transactions from conn3 WITHOUT joining with users
$query = "SELECT SQL_CALC_FOUND_ROWS t.*, b.bus_name, b.from_location, b.to_location, t.journey_date 
          FROM long_route_transactions t 
          JOIN long_route_buses b ON t.bus_id = b.bus_id 
          ORDER BY t.payment_time DESC 
          LIMIT $rows_per_page OFFSET $offset";

$result = $conn3->query($query);
$transactions = [];
while ($row = $result->fetch_assoc()) {
    // STEP 3: Manually add the username from our users array
    $row['username'] = isset($users[$row['user_id']]) ? $users[$row['user_id']] : 'Unknown User';
    $transactions[] = $row;
}

// Get total rows for pagination
$total_rows_result = $conn3->query("SELECT FOUND_ROWS() AS total_rows");
$total_rows = $total_rows_result->fetch_assoc()['total_rows'];
$total_pages = ceil($total_rows / $rows_per_page);

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'data' => $transactions,
    'total_pages' => $total_pages,
    'current_page' => $page
]);
?>