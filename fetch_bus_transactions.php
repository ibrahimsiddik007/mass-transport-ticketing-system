<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Records per page
$offset = ($page - 1) * $limit;

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
        // No matching users, return empty result
        header('Content-Type: application/json');
        echo json_encode([
            'transactions' => [],
            'total_pages' => 0,
            'current_page' => $page,
            'total_records' => 0
        ]);
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
    $conditions[] = "DATE(bt.payment_time) = ?"; // Use payment_time instead of transaction_date
    $params[] = $filter_date;
    $types .= 's';
}

// Create WHERE clause
$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Count total records
$count_query = "SELECT COUNT(*) as total FROM bus_transactions bt $where_clause"; // Correct table name

$stmt = $conn3->prepare($count_query);
if (!empty($params)) {
    // Bind parameters without using spread operator
    call_user_func_array([$stmt, 'bind_param'], array_merge([$types], refValues($params)));
}
$stmt->execute();
$count_result = $stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $limit);

// Get transactions for current page
$query = "SELECT bt.id, bt.transaction_id, bt.user_id, bt.origin, bt.destination, bt.bus_name, 
          bt.amount, bt.payment_time, bt.seats, bt.type, bt.payment_method
          FROM bus_transactions bt
          $where_clause
          ORDER BY bt.payment_time DESC
          LIMIT ?, ?";

$stmt = $conn3->prepare($query);

// Add pagination parameters
if (!empty($params)) {
    $all_params = $params;
    $all_params[] = $offset;
    $all_params[] = $limit;
    $all_types = $types . 'ii';
    
    // Bind all parameters without using spread operator
    call_user_func_array([$stmt, 'bind_param'], array_merge([$all_types], refValues($all_params)));
} else {
    // Only pagination parameters
    $stmt->bind_param('ii', $offset, $limit);
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

// Format data for response with usernames
$formatted_transactions = [];
foreach ($transactions as $transaction) {
    // Add username
    $user_id = $transaction['user_id'];
    $transaction['username'] = isset($user_ids[$user_id]) ? $user_ids[$user_id] : 'Unknown User';
    
    // Format the date
    $transaction['transaction_date'] = date('Y-m-d H:i:s', strtotime($transaction['payment_time']));
    
    // Add payment status (based on payment_method if needed)
    $transaction['status'] = !empty($transaction['payment_method']) ? 'completed' : 'pending';
    
    // Remove user_id field as we already have username
    unset($transaction['user_id']);
    
    // Add to array
    $formatted_transactions[] = $transaction;
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode([
    'transactions' => $formatted_transactions,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'total_records' => $total_records
]);

// Helper function to convert parameters to references
function refValues($arr) {
    $refs = [];
    foreach ($arr as $key => $value) {
        $refs[$key] = &$arr[$key];
    }
    return $refs;
}
?>