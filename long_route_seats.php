<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'long_route.php';
    header('Location: login.php');
    exit;
}

// Get bus ID and journey date from the URL
$bus_id = isset($_GET['bus_id']) ? intval($_GET['bus_id']) : 0;
$journey_date = isset($_GET['journey_date']) ? $_GET['journey_date'] : '';

if (empty($bus_id) || empty($journey_date)) {
    echo "Invalid request. Please go back and select a bus and journey date.";
    exit;
}

// Validate the journey date format (optional)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $journey_date)) {
    echo "Invalid journey date format.";
    exit;
}

// Fetch bus details (optional, for display purposes)
$query = "SELECT bus_name, from_location, to_location, TIME_FORMAT(departure_time, '%h:%i %p') AS departure_time, fare 
          FROM long_route_buses 
          WHERE bus_id = ?";
$stmt = $conn3->prepare($query);
$stmt->bind_param("i", $bus_id);
$stmt->execute();
$result = $stmt->get_result();
$bus = $result->fetch_assoc();

if (!$bus) {
    echo "Bus not found.";
    exit;
}

// Get all seats for this bus
$seats_query = "SELECT s.* 
                FROM long_route_seats s
                WHERE s.bus_id = ?
                ORDER BY s.seat_number";

$stmt = $conn3->prepare($seats_query);
$stmt->bind_param("i", $bus_id);
$stmt->execute();
$seats_result = $stmt->get_result();

// Get all booked seats for this bus on this date
$booked_query = "SELECT seat_numbers 
                 FROM long_route_transactions 
                 WHERE bus_id = ? AND journey_date = ? AND payment_status = 'completed'";
                 
$stmt = $conn3->prepare($booked_query);
$stmt->bind_param("is", $bus_id, $journey_date);
$stmt->execute();
$booked_result = $stmt->get_result();

// Create array of all booked seats
$booked_seats = [];
while ($booking = $booked_result->fetch_assoc()) {
    // Assuming seat_numbers is stored as comma-separated values like "A1,B3,C5"
    $seats = explode(",", $booking['seat_numbers']);
    foreach ($seats as $seat) {
        $booked_seats[] = trim($seat);
    }
}

// Create a 2D array to represent the seating layout
$seating = [];
while ($seat = $seats_result->fetch_assoc()) {
    $letter = substr($seat['seat_number'], 0, 1); // A, B, C, D
    $row = substr($seat['seat_number'], 1); // 0 to 9
    
    if (!isset($seating[$row])) {
        $seating[$row] = [];
    }
    
    // Check if this seat is booked
    $status = in_array($seat['seat_number'], $booked_seats) ? 'booked' : 'available';
    
    $seating[$row][$letter] = [
        'seat_id' => $seat['seat_id'],
        'seat_number' => $seat['seat_number'],
        'status' => $status
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats - Mass Transport Ticketing System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #50c878;
            --accent-color: #ff6b6b;
            --background-color: #f8f9fa;
            --text-color: #2c3e50;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --button-hover-scale: 1.05;
            --button-active-scale: 0.95;
            --seat-size: 50px;
            --seat-gap: 5px;
            --row-height: 60px;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: fadeIn 0.8s ease-out;
            overflow-x: hidden;
            transition: background-color var(--transition-speed), color var(--transition-speed);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: radial-gradient(circle at center, rgba(74, 144, 226, 0.1) 0%, transparent 70%);
            pointer-events: none;
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0% { opacity: 0.3; }
            50% { opacity: 0.1; }
            100% { opacity: 0.3; }
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            transition: all var(--transition-speed);
            margin-bottom: 1.5rem;
            overflow: hidden;
            animation: slideIn 0.6s ease-out forwards;
            position: relative;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0));
            pointer-events: none;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px) rotate(-1deg);
            }
            to {
                opacity: 1;
                transform: translateX(0) rotate(0);
            }
        }

        .card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-body {
            padding: 1.5rem;
            position: relative;
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            position: relative;
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 2px solid rgba(74, 144, 226, 0.2);
        }

        .card-title i {
            font-size: 1.4rem;
            color: var(--secondary-color);
            animation: iconFloat 2s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .card-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        /* Bus Layout Styles */
        .bus-layout {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            transform-style: preserve-3d;
            perspective: 1000px;
            animation: fadeIn 1s ease-out;
        }

        .bus-front {
            width: 80%;
            height: 60px;
            margin: 0 auto 30px;
            border-radius: 20px 20px 0 0;
            position: relative;
            background: linear-gradient(135deg, var(--primary-color), #1a73e8);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transform: rotateX(15deg);
            transform-origin: bottom;
            transition: transform 0.3s;
            overflow: hidden;
        }

        .bus-front::before {
            content: "🚌";
            font-size: 24px;
            margin-right: 10px;
            animation: busFloat 3s ease-in-out infinite;
        }

        @keyframes busFloat {
            0%, 100% { transform: translateY(0) rotate(0); }
            50% { transform: translateY(-5px) rotate(2deg); }
        }

        .bus-front::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .bus-layout:hover .bus-front {
            transform: rotateX(0deg) scale(1.02);
        }

        .seat-row {
            margin-bottom: 15px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: slideIn 0.5s ease-out forwards;
            opacity: 0;
            position: relative;
        }

        .seat-row::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1), transparent);
            bottom: -7px;
        }

        .row-number {
            width: 24px;
            height: 24px;
            margin-right: 15px;
            font-weight: bold;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.3s;
            position: relative;
        }

        .row-number::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .row-number:hover::after {
            opacity: 1;
        }

        .seat {
            width: var(--seat-size);
            height: var(--seat-size);
            margin: var(--seat-gap);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .seat::before {
            content: '';
            position: absolute;
            top: 5px;
            left: 5px;
            right: 5px;
            height: 10px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            z-index: 0;
            transition: all 0.3s;
        }

        .seat::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 8px;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: seatShine 3s infinite;
        }

        @keyframes seatShine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .seat-available {
            background: linear-gradient(145deg, #ffffff, #f0f0f0);
            color: var(--text-color);
            border: 2px solid #ddd;
        }

        .seat-available:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border-color: var(--primary-color);
        }

        .seat-available:active {
            transform: translateY(0) scale(0.95);
        }

        .seat-selected {
            background: linear-gradient(145deg, var(--secondary-color), #2d9348);
            color: white;
            border: 2px solid #2d9348;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(52, 168, 83, 0.4);
            animation: selectedPulse 2s infinite;
        }

        @keyframes selectedPulse {
            0%, 100% { transform: translateY(-3px) scale(1); }
            50% { transform: translateY(-3px) scale(1.05); }
        }

        .seat-selected:hover {
            background: linear-gradient(145deg, #2d9348, var(--secondary-color));
        }

        .seat-booked {
            background: linear-gradient(145deg, var(--accent-color), #d93025);
            color: white;
            border: 2px solid #d93025;
            opacity: 0.8;
            cursor: not-allowed;
            pointer-events: none;
            transform: scale(0.95);
            position: relative;
        }

        .seat-booked::before {
            content: '✕';
            position: absolute;
            font-size: 20px;
            color: white;
            opacity: 0.5;
        }

        .aisle {
            width: 20px;
            display: inline-block;
            position: relative;
        }

        .aisle::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 2px;
            background: linear-gradient(to bottom, transparent, rgba(0, 0, 0, 0.1), transparent);
            transform: translateX(-50%);
        }

        .seat-info {
            background: rgba(0, 0, 0, 0.03);
            padding: 15px;
            border-radius: 12px;
            margin-top: 25px;
            animation: fadeIn 1s ease-out 1s forwards;
            opacity: 0;
            position: relative;
            overflow: hidden;
            border-top: 2px solid rgba(74, 144, 226, 0.2);
        }

        .seat-info::before {
            content: '';
            position: absolute;
            top: -2px;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .seat-info .seat {
            width: 40px;
            height: 40px;
            font-size: 12px;
            margin-right: 10px;
            transform: none !important;
        }

        /* Booking Form Styles */
        #booking-form {
            animation: slideInUp 0.8s ease-out 0.5s forwards;
            opacity: 0;
            transform: translateY(20px);
            position: relative;
        }

        @keyframes slideInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Flash Effect */
        @keyframes seatFlash {
            0% { box-shadow: 0 0 0 rgba(52, 168, 83, 0); }
            50% { box-shadow: 0 0 30px rgba(52, 168, 83, 0.8); }
            100% { box-shadow: 0 0 0 rgba(52, 168, 83, 0); }
        }

        .seat-flash {
            animation: seatFlash 0.6s ease-out;
        }

        /* Dark Mode Styles */
        body.dark-mode {
            --background-color: #121212;
            --text-color: #e8eaed;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .card {
            background-color: #1e1e1e;
            border-color: #333;
        }

        body.dark-mode .seat-available {
            background: linear-gradient(145deg, #3c3c3c, #292929);
            color: #e8eaed;
            border: 2px solid #5f6368;
        }

        body.dark-mode .seat-info {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.08));
        }

        body.dark-mode .aisle::before {
            background: linear-gradient(to bottom, transparent, rgba(255, 255, 255, 0.1), transparent);
        }

        body.dark-mode .row-number {
            background: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .bus-front {
            background: linear-gradient(135deg, #357abd, var(--primary-color));
        }

        /* Dark Mode Toggle Button */
        .dark-mode-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all var(--transition-speed);
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1);
        }

        body.dark-mode .dark-mode-toggle {
            background: var(--secondary-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            :root {
                --seat-size: 40px;
                --seat-gap: 3px;
                --row-height: 50px;
            }
            
            .row-number {
                width: 20px;
                height: 20px;
                font-size: 10px;
            }

            .bus-front {
                height: 50px;
                font-size: 0.9rem;
            }

            .bus-front::before {
                font-size: 20px;
            }
        }

        /* Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeOut 0.5s ease-out 1s forwards;
        }

        body.dark-mode .loading-overlay {
            background: rgba(0, 0, 0, 0.9);
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid var(--primary-color);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }

        /* Enhanced Button Styles */
        .btn {
            position: relative;
            overflow: hidden;
            transition: all var(--transition-speed) cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .btn:hover::before {
            transform: translateX(100%);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #357abd);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(var(--button-hover-scale));
            box-shadow: 0 6px 12px rgba(74, 144, 226, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0) scale(var(--button-active-scale));
        }

        /* Enhanced Seat Styles */
        .seat {
            width: var(--seat-size);
            height: var(--seat-size);
            margin: var(--seat-gap);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .seat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .seat:hover::before {
            transform: translateX(100%);
        }

        .seat-available {
            background: linear-gradient(145deg, #ffffff, #f0f0f0);
            color: var(--text-color);
            border: 2px solid #ddd;
        }

        .seat-available:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            border-color: var(--primary-color);
        }

        .seat-available:active {
            transform: translateY(0) scale(0.95);
        }

        .seat-selected {
            background: linear-gradient(145deg, var(--secondary-color), #2d9348);
            color: white;
            border: 2px solid #2d9348;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(52, 168, 83, 0.4);
            animation: selectedPulse 2s infinite;
        }

        @keyframes selectedPulse {
            0%, 100% { transform: translateY(-3px) scale(1); }
            50% { transform: translateY(-3px) scale(1.05); }
        }

        .seat-booked {
            background: linear-gradient(145deg, var(--accent-color), #d93025);
            color: white;
            border: 2px solid #d93025;
            opacity: 0.8;
            cursor: not-allowed;
            pointer-events: none;
            transform: scale(0.95);
            position: relative;
        }

        .seat-booked::before {
            content: '✕';
            position: absolute;
            font-size: 24px;
            color: white;
            opacity: 0.5;
            animation: crossPulse 2s infinite;
        }

        @keyframes crossPulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 0.7; }
        }

        /* Enhanced Bus Front */
        .bus-front {
            background: linear-gradient(135deg, var(--primary-color), #1a73e8);
            color: white;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            transform: rotateX(15deg);
            transform-origin: bottom;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
            position: relative;
        }

        .bus-front::before {
            content: "🚌";
            font-size: 28px;
            margin-right: 15px;
            animation: busFloat 3s ease-in-out infinite;
        }

        .bus-front::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .bus-layout:hover .bus-front {
            transform: rotateX(0deg) scale(1.02);
        }

        /* Enhanced Row Numbers */
        .row-number {
            width: 28px;
            height: 28px;
            margin-right: 20px;
            font-weight: bold;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .row-number::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .row-number:hover::after {
            opacity: 1;
        }

        /* Enhanced Aisle */
        .aisle {
            width: 25px;
            display: inline-block;
            position: relative;
        }

        .aisle::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 2px;
            background: linear-gradient(to bottom, transparent, rgba(0, 0, 0, 0.1), transparent);
            transform: translateX(-50%);
            animation: aislePulse 2s infinite;
        }

        @keyframes aislePulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 0.8; }
        }

        /* Enhanced Seat Info */
        .seat-info {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.03), rgba(0, 0, 0, 0.05));
            padding: 20px;
            border-radius: 16px;
            margin-top: 30px;
            animation: fadeIn 1s ease-out 1s forwards;
            opacity: 0;
            position: relative;
            overflow: hidden;
        }

        .seat-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            animation: lineGrow 0.5s ease-out forwards;
        }

        /* Enhanced Booking Summary Styles */
        .booking-summary {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            color: var(--text-color);
            padding: 1.5rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.8s ease-out;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-top: 2px solid rgba(74, 144, 226, 0.2);
        }

        .booking-summary::before {
            content: '';
            position: absolute;
            top: -2px;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .booking-summary h6 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-color);
        }

        .booking-summary h6 i {
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        #selected-seats-display, #total-fare-display {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
            background: rgba(74, 144, 226, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: inline-block;
            margin-left: 0.5rem;
            border: 1px solid rgba(74, 144, 226, 0.2);
        }

        /* Enhanced Proceed Button */
        #proceed-button {
            background: linear-gradient(135deg, var(--secondary-color), #2d9348);
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(52, 168, 83, 0.3);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            width: 100%;
            margin-top: 1.5rem;
        }

        #proceed-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        #proceed-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(52, 168, 83, 0.4);
        }

        #proceed-button:hover::before {
            transform: translateX(100%);
        }

        #proceed-button:active {
            transform: translateY(0);
        }

        #proceed-button:disabled {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            box-shadow: none;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Dark Mode Adjustments */
        body.dark-mode .booking-summary {
            background: linear-gradient(135deg, #2d2d2d, #1e1e1e);
            border-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .booking-summary h6 {
            color: #ffffff;
        }

        body.dark-mode #selected-seats-display,
        body.dark-mode #total-fare-display {
            color: var(--secondary-color);
            background: rgba(80, 200, 120, 0.1);
            border-color: rgba(80, 200, 120, 0.2);
        }

        /* Dark Mode Seat Adjustments */
        body.dark-mode .seat-selected {
            background: linear-gradient(145deg, #2d9348, var(--secondary-color));
            border-color: var(--secondary-color);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .booking-summary {
                padding: 1rem;
            }

            .booking-summary h6 {
                font-size: 1rem;
            }

            #selected-seats-display, #total-fare-display {
                font-size: 1.1rem;
                padding: 0.4rem 0.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1 class="text-center mt-4 mb-4">Select Your Seats</h1>
        <div class="card">
            <div class="card-body">
                <h3 class="card-title"><?= htmlspecialchars($bus['bus_name']) ?></h3>
                <p class="card-text">
                    <strong>Route:</strong> <?= htmlspecialchars($bus['from_location']) ?> to <?= htmlspecialchars($bus['to_location']) ?><br>
                    <strong>Departure Time:</strong> <?= htmlspecialchars($bus['departure_time']) ?><br>
                    <strong>Journey Date:</strong> <?= htmlspecialchars($journey_date) ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title text-center">Choose Your Preferred Seats</h5>
            <div class="bus-layout">
                <div class="bus-front">FRONT (Driver)</div>
                
                <?php 
                $letters = ['A', 'B', 'C', 'D']; // Define the seat columns
                for ($row = 0; $row < 11; $row++): ?>
                    <div class="seat-row">
                        <span class="row-number"><?= $row ?></span>
                        
                        <?php foreach ($letters as $index => $letter): ?>
                            <?php if (!isset($seating[$row][$letter])) continue; ?>
                            <?php 
                                $seat = $seating[$row][$letter];
                                $seatClass = $seat['status'] == 'available' ? 'seat-available' : 'seat-booked';
                                $disabledAttr = $seat['status'] == 'booked' ? 'disabled' : '';
                            ?>
                            <?php if ($letter == 'B'): ?>
                                <div class="seat <?= $seatClass ?>" data-seat-id="<?= $seat['seat_id'] ?>" <?= $disabledAttr ?>>
                                    <?= $seat['seat_number'] ?>
                                </div>
                                <div class="aisle"></div>
                            <?php else: ?>
                                <div class="seat <?= $seatClass ?>" data-seat-id="<?= $seat['seat_id'] ?>" <?= $disabledAttr ?>>
                                    <?= $seat['seat_number'] ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
                
                <div class="seat-info mt-4">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="seat seat-available mr-3">A1</div> Available
                        <div class="seat seat-selected mx-3">A1</div> Selected
                        <div class="seat seat-booked mx-3">A1</div> Booked
                    </div>
                </div>
            </div>
            
            <form id="booking-form" action="long_route_payment.php" method="POST">
                <input type="hidden" name="bus_id" value="<?= $bus_id ?>">
                <input type="hidden" name="journey_date" value="<?= htmlspecialchars($journey_date) ?>">
                <input type="hidden" name="selected_seats" id="selected-seats" value="">
                <input type="hidden" name="total_fare" id="total-fare" value="">
                
                <div class="form-group mt-4">
                    <div class="booking-summary">
                        <h6><i class="fas fa-chair"></i> Selected Seats: <span id="selected-seats-display">None</span></h6>
                        <h6><i class="fas fa-money-bill-wave"></i> Total Fare: BDT <span id="total-fare-display">0.00</span></h6>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" id="proceed-button" disabled>
                    <i class="fas fa-arrow-right"></i> Proceed to Payment
                </button>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Dark mode toggle functionality
        function toggleDarkMode() {
            const body = document.body;
            const isDarkMode = body.classList.contains('dark-mode');
            
            if (isDarkMode) {
                body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            } else {
                body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            }
            
            // Update icon
            const icon = document.querySelector('.dark-mode-toggle i');
            if (icon) {
                icon.className = isDarkMode ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        // Check for saved theme preference
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const body = document.body;
            
            if (savedTheme === 'dark') {
                body.classList.add('dark-mode');
            } else {
                body.classList.remove('dark-mode');
            }

            // Add dark mode toggle button if it doesn't exist
            if (!document.querySelector('.dark-mode-toggle')) {
                const toggleButton = document.createElement('button');
                toggleButton.className = 'dark-mode-toggle';
                toggleButton.innerHTML = '<i class="fas fa-moon"></i>';
                toggleButton.onclick = toggleDarkMode;
                document.body.appendChild(toggleButton);
            }

            // Update initial icon
            const icon = document.querySelector('.dark-mode-toggle i');
            if (icon) {
                icon.className = body.classList.contains('dark-mode') ? 'fas fa-sun' : 'fas fa-moon';
            }
        });
        
        // Seat selection logic
        $(document).ready(function() {
            const farePerSeat = <?= $bus['fare'] ?>; // Get the fare per seat from PHP
            const selectedSeats = []; // Array to store selected seats

            // Handle seat selection
            $('.seat-available').on('click', function() {
                const seatId = $(this).data('seat-id'); // Get the seat ID
                const seatNumber = $(this).text(); // Get the seat number

                if ($(this).hasClass('seat-selected')) {
                    // Deselect the seat
                    $(this).removeClass('seat-selected');

                    // Remove the seat from the selectedSeats array
                    const index = selectedSeats.findIndex(seat => seat.id === seatId);
                    if (index !== -1) {
                        selectedSeats.splice(index, 1);
                    }
                } else {
                    // Select the seat
                    $(this).addClass('seat-selected');
                    selectedSeats.push({
                        id: seatId,
                        number: seatNumber
                    });
                }

                // Update the booking summary
                updateBookingSummary();
            });

            // Function to update the booking summary
            function updateBookingSummary() {
                if (selectedSeats.length > 0) {
                    const seatNumbers = selectedSeats.map(seat => seat.number).join(', ');
                    const totalFare = selectedSeats.length * farePerSeat;

                    // Update the display
                    $('#selected-seats-display').text(seatNumbers);
                    $('#total-fare-display').text(totalFare.toFixed(2));

                    // Update hidden inputs for form submission
                    $('#selected-seats').val(JSON.stringify(selectedSeats));
                    $('#total-fare').val(totalFare);

                    // Enable the proceed button
                    $('#proceed-button').prop('disabled', false);
                } else {
                    // No seats selected
                    $('#selected-seats-display').text('None');
                    $('#total-fare-display').text('0.00');
                    $('#selected-seats').val('');
                    $('#total-fare').val('');

                    // Disable the proceed button
                    $('#proceed-button').prop('disabled', true);
                }
            }
        });
    </script>
</body>
</html>