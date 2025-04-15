<?php
session_start();
include 'db.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['station_id'])) {
    $station_id = (int)$_POST['station_id'];
    
    // Start transaction
    $conn1->begin_transaction();
    
    try {
        // Delete fares related to this station
        $fare_query = "DELETE FROM ticket_routes WHERE start_point = ? OR end_point = ?";
        $fare_stmt = $conn1->prepare($fare_query);
        $fare_stmt->bind_param("ii", $station_id, $station_id);
        $fare_stmt->execute();
        
        // Delete the station
        $station_query = "DELETE FROM metro_stations WHERE id = ?";
        $station_stmt = $conn1->prepare($station_query);
        $station_stmt->bind_param("i", $station_id);
        $station_stmt->execute();
        
        // Commit transaction
        $conn1->commit();
        
        $_SESSION['success_message'] = "Station and all related fare mappings deleted successfully";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error_message'] = "Failed to delete station: " . $e->getMessage();
    }
    
    header('Location: metro_admin.php');
    exit;
} else {
    $_SESSION['error_message'] = "Invalid request";
    header('Location: metro_admin.php');
    exit;
}
?>