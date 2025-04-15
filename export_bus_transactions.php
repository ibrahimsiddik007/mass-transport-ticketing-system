<?php
session_start();
include 'db.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Get filter values
$search_user = isset($_GET['search_user']) ? $_GET['search_user'] : '';
$filter_route = isset($_GET['filter_route']) ? $_GET['filter_route'] : '';
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';

// Build query conditions
$conditions = [];
$params = [];
$types = '';

// First, get user data from conn1 if searching by username
$user_ids = [];
if (!empty($search_user)) {
    $user_query = "SELECT id, name FROM users WHERE name LIKE ?";
    $user_stmt = $conn1->prepare($user_query);
    $search_param = "%$search_user%";
    $user_stmt->bind_param('s', $search_param);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    while ($user_row = $user_result->fetch_assoc()) {
        $user_ids[$user_row['id']] = $user_row['name'];
    }
    
    if (empty($user_ids)) {
        // No matching users, return empty CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bus_transactions_export_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Transaction ID', 'Username', 'Origin', 'Destination', 'Bus Name', 'Amount', 'Seats', 'Payment Date', 'Type', 'Payment Method']);
        fclose($output);
        exit;
    }
    
    // Add user IDs to the conditions
    $conditions[] = "bt.user_id IN (" . implode(',', array_keys($user_ids)) . ")";
}

if (!empty($filter_route)) {
    list($origin, $destination) = explode(' - ', $filter_route);
    $conditions[] = "bt.origin = ? AND bt.destination = ?";
    $params[] = $origin;
    $params[] = $destination;
    $types .= 'ss';
}

if (!empty($filter_date)) {
    $conditions[] = "DATE(bt.payment_time) = ?";
    $params[] = $filter_date;
    $types .= 's';
}

// Create WHERE clause
$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get all transactions matching criteria
$query = "SELECT bt.id, bt.transaction_id, bt.user_id, bt.origin, bt.destination, bt.bus_name, 
          bt.amount, bt.payment_time, bt.seats, bt.type, bt.payment_method
          FROM bus_transactions bt
          $where_clause
          ORDER BY bt.payment_time DESC";

$stmt = $conn3->prepare($query);
if (!empty($params)) {
    // Bind parameters without using spread operator
    call_user_func_array([$stmt, 'bind_param'], array_merge([$types], refValues($params)));
}
$stmt->execute();
$result = $stmt->get_result();

// Get all user IDs from transactions to fetch usernames
$transaction_user_ids = [];
$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transaction_user_ids[] = $row['user_id'];
    $transactions[] = $row;
}

// Get usernames from users table in conn1 (if not already fetched)
if (!empty($transaction_user_ids) && empty($user_ids)) {
    $user_ids_string = implode(',', array_unique($transaction_user_ids));
    $users_query = "SELECT id, name FROM users WHERE id IN ($user_ids_string)";
    $users_result = $conn1->query($users_query);
    
    while ($user_row = $users_result->fetch_assoc()) {
        $user_ids[$user_row['id']] = $user_row['name'];
    }
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="bus_transactions_export_' . date('Y-m-d') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV header row
fputcsv($output, [
    'ID', 'Transaction ID', 'Username', 'Origin', 'Destination', 'Bus Name',
    'Amount', 'Seats', 'Payment Date', 'Type', 'Payment Method'
]);

// Add data rows
foreach ($transactions as $transaction) {
    $user_id = $transaction['user_id'];
    $username = isset($user_ids[$user_id]) ? $user_ids[$user_id] : 'Unknown User';
    
    fputcsv($output, [
        $transaction['id'],
        $transaction['transaction_id'],
        $username,
        $transaction['origin'],
        $transaction['destination'],
        $transaction['bus_name'],
        $transaction['amount'],
        $transaction['seats'],
        $transaction['payment_time'],
        $transaction['type'],
        $transaction['payment_method']
    ]);
}

// Close the output stream
fclose($output);
exit;

// Helper function to convert parameters to references
function refValues($arr) {
    $refs = [];
    foreach ($arr as $key => $value) {
        $refs[$key] = &$arr[$key];
    }
    return $refs;
}
?>