<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    if (!isset($_SESSION['redirect_to'])) {
        $_SESSION['redirect_to'] = 'bus_payment_gateway.php';
    }
    header('Location: login.php');
    exit;

    
}




$origin = $_POST['origin'];
$destination = $_POST['destination'];
$payment_gateway = $_POST['payment_gateway'];

// Redirect to the relevant payment page
switch ($payment_gateway) {
    case 'bkash':
        header('Location: bus_bkash_payment.php?origin=' . urlencode($origin) . '&destination=' . urlencode($destination));
        break;
    case 'rocket':
        header('Location: bus_rocket_payment.php?origin=' . urlencode($origin) . '&destination=' . urlencode($destination));
        break;
    case 'card':
        header('Location: bus_card_payment.php?origin=' . urlencode($origin) . '&destination=' . urlencode($destination));
        break;
    default:
        header('Location: bus_selected_local.php');
        break;
}
exit;
?>