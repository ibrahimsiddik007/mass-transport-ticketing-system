<?php
include 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request
error_log("Fetching seats for bus_id: " . (isset($_GET['bus_id']) ? $_GET['bus_id'] : 'not set'));

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['bus_id'])) {
    $bus_id = intval($_GET['bus_id']);

    // Fetch all seats for the bus
    // Option 1: If the seat status is stored directly in the seats table
    $query = "SELECT seat_id, seat_number, status 
              FROM long_route_seats
              WHERE bus_id = ?
              ORDER BY seat_number";
    
    $stmt = $conn3->prepare($query);
    $stmt->bind_param("i", $bus_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the above query doesn't work because status isn't directly in the seats table,
    // uncomment this alternative query that joins with transactions
    /*
    $query = "SELECT s.seat_id, s.seat_number, 
              CASE WHEN t.transaction_id IS NOT NULL THEN 'booked' ELSE 'available' END AS status
              FROM long_route_seats s
              LEFT JOIN long_route_transactions t ON s.seat_id = t.seat_id 
              WHERE s.bus_id = ?
              ORDER BY s.seat_number";
    
    $stmt = $conn3->prepare($query);
    $stmt->bind_param("i", $bus_id);
    $stmt->execute();
    $result = $stmt->get_result();
    */

    $seats = [];
    while ($row = $result->fetch_assoc()) {
        $seats[] = $row;
    }

    // Log the result count
    error_log("Found " . count($seats) . " seats for bus_id: " . $bus_id);

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['seats' => $seats]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request']);
    exit;
}
?>