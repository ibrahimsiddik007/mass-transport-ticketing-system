<?php
include 'db.php';

// Check if the user is logged in as admin
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete all transactions
        $conn3->query("DELETE FROM long_route_transactions");

        // Reset all seats to available
        $conn3->query("UPDATE long_route_seats SET status = 'available'");

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'All transactions have been cleared and seats reset successfully.']);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
