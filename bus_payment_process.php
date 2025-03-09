<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}



date_default_timezone_set('Asia/Dhaka');

$user_id = $_SESSION['user_id'];
$origin = $_POST['origin'];
$destination = $_POST['destination'];
$payment_method = $_POST['payment_method'];
$amount = $_POST['amount'];
$bus_name = $_POST['bus_name'];
$payment_time = date('Y-m-d H:i:s');

$_SESSION['payment_completed'] = true;


function generateTransactionId() {
    $characters = 'abcdefghijklmnopqrstuvwxyz123456789';
    $randomString = '';
    for ($i = 0; $i < 7; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return 'txn_' . $randomString;
}

$transaction_id = generateTransactionId(); // Generate a unique transaction ID

// Fetch account details from demo_accounts
$query = "SELECT * FROM demo_accounts WHERE account_number = ? AND account_type = ?";
$stmt = $conn1->prepare($query);
$stmt->bind_param("ss", $_POST['account_number'], $payment_method);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();

if (!$account) {
    echo "Invalid account details: Account not found.";
    exit;
}

if ($account['pin'] !== $_POST['pin']) {
    echo "Invalid account details: Incorrect PIN.";
    exit;
}

// Deduct the amount from the account balance
$new_balance = $account['balance'] - $amount;
if ($new_balance < 0) {
    echo "Insufficient balance.";
    exit;
}

$query = "UPDATE demo_accounts SET balance = ? WHERE account_number = ? AND account_type = ?";
$stmt = $conn1->prepare($query);
$stmt->bind_param("dss", $new_balance, $_POST['account_number'], $payment_method);
$stmt->execute();

// Store the transaction in bus_transactions
$query = "INSERT INTO bus_transactions (transaction_id, user_id, amount, payment_time, origin, destination, bus_name, type, payment_method) VALUES (?, ?, ?, ?, ?, ?,?, 'local', ?)";
$stmt = $conn3->prepare($query);
$stmt->bind_param("sidsssss", $transaction_id, $user_id, $amount, $payment_time, $origin, $destination,$bus_name, $payment_method);
$stmt->execute();



// Redirect to the payment success page
header('Location: bus_payment_success.php?transaction_id=' . $transaction_id);
exit;
?>