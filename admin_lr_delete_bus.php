<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bus_id'])) {
    $bus_id = (int)$_POST['bus_id'];
    
    // Check if there are any transactions for this bus
    $check_query = "SELECT COUNT(*) as count FROM long_route_transactions WHERE bus_id = $bus_id";
    $result = $conn3->query($check_query);
    $transaction_count = $result->fetch_assoc()['count'];
    
    if ($transaction_count > 0) {
        // Notify admin about existing transactions
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Cannot delete bus with existing transactions. Please cancel the transactions first.']);
        exit;
    }
    
    // Delete seats first (due to foreign key constraint)
    $stmt = $conn3->prepare("DELETE FROM long_route_seats WHERE bus_id = ?");
    $stmt->bind_param("i", $bus_id);
    $stmt->execute();
    
    // Delete the bus
    $stmt = $conn3->prepare("DELETE FROM long_route_buses WHERE bus_id = ?");
    $stmt->bind_param("i", $bus_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Bus and related seats deleted successfully.']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Failed to delete bus or bus not found.']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Invalid request.']);
}
?>