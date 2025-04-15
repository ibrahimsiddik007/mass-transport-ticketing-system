<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $name = trim($_POST['name']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    // Validate inputs
    if (empty($name)) {
        $_SESSION['error_message'] = "Station name cannot be empty";
        header('Location: metro_admin.php');
        exit;
    }
    
    // Check if station with same name already exists
    $check_query = "SELECT id FROM metro_stations WHERE s_name = ?";
    $check_stmt = $conn1->prepare($check_query);
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "A station with this name already exists";
        header('Location: metro_admin.php');
        exit;
    }
    
    // Start transaction
    $conn1->begin_transaction();
    
    try {
        // Insert new station
        $insert_query = "INSERT INTO metro_stations (s_name, active) VALUES (?, ?)";
        $insert_stmt = $conn1->prepare($insert_query);
        $insert_stmt->bind_param("si", $name, $active);
        
        if ($insert_stmt->execute()) {
            $new_station_id = $insert_stmt->insert_id;
            
            // Get all other stations to create fare entries
            $stations_query = "SELECT id FROM metro_stations WHERE id != ?";
            $stations_stmt = $conn1->prepare($stations_query);
            $stations_stmt->bind_param("i", $new_station_id);
            $stations_stmt->execute();
            $stations_result = $stations_stmt->get_result();
            
            // Generate fare entries for each station pair with random fares
            while ($station = $stations_result->fetch_assoc()) {
                // Generate random fare between 15 and 50 for each direction
                $fare_to = rand(15, 50);
                $fare_from = rand(15, 50);
                
                $fare_query = "INSERT INTO ticket_routes (start_point,end_point, fare) VALUES (?, ?, ?), (?, ?, ?)";
                $fare_stmt = $conn1->prepare($fare_query);
                $fare_stmt->bind_param("iiiiii", 
                    $new_station_id, $station['id'], $fare_to,  // From new to existing
                    $station['id'], $new_station_id, $fare_from  // From existing to new
                );
                $fare_stmt->execute();
            }
            
            $conn1->commit();
            $_SESSION['success_message'] = "Station added successfully with random fares to all destinations";
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $conn1->rollback();
        $_SESSION['error_message'] = "Failed to add station: " . $e->getMessage();
    }
    
    header('Location: metro_admin.php');
    exit;
}
?>