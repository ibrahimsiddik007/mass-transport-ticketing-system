<?php
session_start();
include 'db.php';

// Check if the user is not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
// Pagination variables for buses
$buses_rows_per_page = 10;
$buses_page = isset($_GET['buses_page']) ? (int)$_GET['buses_page'] : 1;
$buses_offset = ($buses_page - 1) * $buses_rows_per_page;

// Fetch buses with pagination
$buses = [];
$query = "SELECT SQL_CALC_FOUND_ROWS bus_id, bus_name, from_location, to_location, TIME_FORMAT(departure_time, '%h:%i %p') AS departure_time, journey_date, fare, total_seats, bus_type FROM long_route_buses LIMIT $buses_rows_per_page OFFSET $buses_offset";
$result = $conn3->query($query);
while ($row = $result->fetch_assoc()) {
    $buses[] = $row;
}
$total_buses_result = $conn3->query("SELECT FOUND_ROWS() AS total_rows");
$total_buses = $total_buses_result->fetch_assoc()['total_rows'];
$total_buses_pages = ceil($total_buses / $buses_rows_per_page);

// Pagination variables for transactions
$transactions_rows_per_page = 10;
$transactions_page = isset($_GET['transactions_page']) ? (int)$_GET['transactions_page'] : 1;
$transactions_offset = ($transactions_page - 1) * $transactions_rows_per_page;

// STEP 1: First, fetch all users from conn1 (users database)
$users = [];
$result_users = $conn1->query("SELECT id, name FROM users");
while ($user_row = $result_users->fetch_assoc()) {
    $users[$user_row['id']] = $user_row['name'];
}

// STEP 2: Now fetch transactions from conn3 WITHOUT joining with users
$transactions = [];
$query = "SELECT t.payment_transaction_id, t.user_id, b.bus_name, b.from_location, b.to_location, 
          b.journey_date, t.seat_numbers, t.amount, t.payment_time, t.payment_status 
          FROM long_route_transactions t 
          JOIN long_route_buses b ON t.bus_id = b.bus_id 
          ORDER BY t.payment_time DESC 
          LIMIT $transactions_rows_per_page OFFSET $transactions_offset";

$result = $conn3->query($query);
while ($row = $result->fetch_assoc()) {
    // STEP 3: Manually add the username from our users array
    $row['username'] = isset($users[$row['user_id']]) ? $users[$row['user_id']] : 'Unknown User';
    $transactions[] = $row;
}

$total_transactions_result = $conn3->query("SELECT FOUND_ROWS() AS total_rows");
$total_transactions = $total_transactions_result->fetch_assoc()['total_rows'];
$total_transactions_pages = ceil($total_transactions / $transactions_rows_per_page);

// Handle Add Bus functionality
if (isset($_POST['add_bus'])) {
    $bus_name = $_POST['bus_name'];
    $from_location = $_POST['from_location'];
    $to_location = $_POST['to_location'];
    $departure_time = $_POST['departure_time'];
    $fare = $_POST['fare'];
    $total_seats = $_POST['total_seats'];
    $bus_type = $_POST['bus_type'];
    
    // Format the departure_time for MySQL TIME format
    $departure_time_24hr = $departure_time . ':00';
    
    // Insert bus into the database
    $stmt = $conn3->prepare("INSERT INTO long_route_buses (bus_name, from_location, to_location, departure_time, fare, total_seats, bus_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdis", $bus_name, $from_location, $to_location, $departure_time_24hr, $fare, $total_seats, $bus_type);
    
    if ($stmt->execute()) {
        $bus_id = $conn3->insert_id;
        
        // Generate seats for the bus
        $seat_rows = ['A', 'B', 'C', 'D'];
        $seat_columns = 10; // 10 columns (40 seats total)
        
        for ($row = 0; $row < count($seat_rows); $row++) {
            for ($col = 1; $col <= $seat_columns; $col++) {
                $seat_number = $seat_rows[$row] . $col;
                $stmt = $conn3->prepare("INSERT INTO long_route_seats (bus_id, seat_number, status) VALUES (?, ?, 'available')");
                $stmt->bind_param("is", $bus_id, $seat_number);
                $stmt->execute();
            }
        }
        
        echo "<script>alert('Bus and seats added successfully!');</script>";
    } else {
        echo "<script>alert('Failed to add bus. Please try again.');</script>";
    }
}

// Handle CSV download for transactions
if (isset($_POST['download_csv'])) {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="long_route_transactions_' . date('Y-m-d') . '.csv"');
    
    // Create a file handle for PHP to output the CSV data
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, array('Transaction ID', 'User', 'Bus', 'From', 'To', 'Journey Date', 'Seats', 'Amount', 'Payment Time', 'Payment Status'));
    
    // STEP 1: Fetch all users from conn1
    $users = [];
    $result_users = $conn1->query("SELECT id, name FROM users");
    while ($user_row = $result_users->fetch_assoc()) {
        $users[$user_row['id']] = $user_row['name'];
    }
    
    // STEP 2: Fetch all transactions from conn3
    $query = "SELECT t.payment_transaction_id, t.user_id, b.bus_name, b.from_location, b.to_location, 
              t.journey_date, t.seat_numbers, t.amount, t.payment_time, t.payment_status 
              FROM long_route_transactions t 
              JOIN long_route_buses b ON t.bus_id = b.bus_id 
              ORDER BY t.payment_time DESC";
    $result = $conn3->query($query);
    
    // Loop through data and output as CSV rows
    while ($row = $result->fetch_assoc()) {
        // STEP 3: Add username from our users array
        $username = isset($users[$row['user_id']]) ? $users[$row['user_id']] : 'Unknown User';
        
        // Create a new row with username instead of user_id
        $csv_row = [
            'transaction_id' => $row['payment_transaction_id'],
            'username' => $username,
            'bus_name' => $row['bus_name'],
            'from_location' => $row['from_location'],
            'to_location' => $row['to_location'],
            'journey_date' => $row['journey_date'],
            'seat_numbers' => $row['seat_numbers'],
            'amount' => $row['amount'],
            'payment_time' => $row['payment_time'],
            'payment_status' => $row['payment_status']
        ];
        
        fputcsv($output, $csv_row);
    }
    
    // Close the file handle
    fclose($output);
    exit; // Ensure no other content is added to the CSV file
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Long Route Management - Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
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
            --dark-bg: #121212;
            --dark-card-bg: #1e1e1e;
            --dark-text: #ffffff;
            --dark-border: #333;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            transition: all var(--transition-speed);
            margin-bottom: 2rem;
            overflow: hidden;
            animation: slideIn 0.6s ease-out forwards;
            border: none;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-body {
            padding: 2rem;
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            position: relative;
            padding-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .form-group label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all var(--transition-speed);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
            transform: translateY(-2px);
        }

        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all var(--transition-speed);
            position: relative;
            overflow: hidden;
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
            border: none;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(74, 144, 226, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--accent-color), #d93025);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(255, 107, 107, 0.3);
        }

        .table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }

        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), #357abd);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        .table tbody tr:hover {
            background-color: rgba(74, 144, 226, 0.05);
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }

        .pagination-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: white;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all var(--transition-speed);
            box-shadow: var(--card-shadow);
        }

        .pagination-link:hover {
            transform: translateY(-3px);
            background: var(--primary-color);
            color: white;
        }

        .pagination-link.active {
            background: var(--primary-color);
            color: white;
        }

        .seat-map {
            display: grid;
            grid-template-rows: repeat(2, auto);
            grid-template-columns: repeat(20, 1fr);
            gap: 10px;
            margin-top: 2rem;
            justify-items: center;
        }

        .bus-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }

        .bus-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .bus-side {
            display: flex;
            gap: 5px;
        }

        .bus-aisle {
            width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #6c757d;
        }

        .seat {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-speed);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .seat:hover:not(.booked) {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            background: #dee2e6;
        }

        .seat.booked {
            background: var(--accent-color);
            color: white;
            cursor: not-allowed;
        }

        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), #357abd);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.4rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1.5rem;
        }

        .seat-sample {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Dark Mode Support */
        body.dark-mode {
            background-color: var(--dark-bg);
            color: var(--dark-text);
        }

        body.dark-mode .card {
            background-color: var(--dark-card-bg);
            border-color: var(--dark-border);
        }

        body.dark-mode .form-control {
            background-color: #2d2d2d;
            border-color: #333;
            color: var(--dark-text);
        }

        body.dark-mode .form-control:focus {
            background-color: #2d2d2d;
            border-color: var(--primary-color);
            color: var(--dark-text);
        }

        body.dark-mode .table {
            color: var(--dark-text);
        }

        body.dark-mode .table tbody tr:hover {
            background-color: rgba(74, 144, 226, 0.1);
        }

        body.dark-mode .pagination-link {
            background-color: var(--dark-card-bg);
            color: var(--dark-text);
        }

        body.dark-mode .pagination-link:hover {
            background-color: var(--primary-color);
            color: white;
        }

        body.dark-mode .seat {
            background-color: #2d2d2d;
            color: var(--dark-text);
        }

        body.dark-mode .seat:hover:not(.booked) {
            background-color: #3d3d3d;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .table-responsive {
                border-radius: 12px;
                overflow: hidden;
            }
            
            .seat {
                width: 35px;
                height: 35px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back to Dashboard Button -->
        <div class="text-left mb-3">
            <a href="admin_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <h1 class="text-center mb-4">Long Route Bus Management</h1>

        <!-- Add Long Route Bus -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Add Long Route Bus</h3>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bus_name">Bus Name</label>
                                <input type="text" class="form-control" id="bus_name" name="bus_name" required>
                            </div>
                            <div class="form-group">
                                <label for="from_location">From</label>
                                <input type="text" class="form-control" id="from_location" name="from_location" required>
                            </div>
                            <div class="form-group">
                                <label for="to_location">To</label>
                                <input type="text" class="form-control" id="to_location" name="to_location" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="departure_time">Departure Time</label>
                                <input type="time" class="form-control" id="departure_time" name="departure_time" required>
                            </div>
                            <div class="form-group">
                                <label for="fare">Fare (BDT)</label>
                                <input type="number" step="0.01" class="form-control" id="fare" name="fare" required>
                            </div>
                            <div class="form-group">
                                <label for="total_seats">Total Seats</label>
                                <input type="number" class="form-control" id="total_seats" name="total_seats" value="40" required>
                            </div>
                            <div class="form-group">
                                <label for="bus_type">Bus Type</label>
                                <select class="form-control" id="bus_type" name="bus_type" required>
                                    <option value="AC">AC</option>
                                    <option value="Non-AC">Non-AC</option>
                                    <option value="Business Class">Business Class</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="add_bus" class="btn btn-primary">Add Bus</button>
                </form>
            </div>
        </div>

        <!-- View Buses -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Long Route Buses</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Bus Name</th>
                            <th>Route</th>
                            <th>Departure Time</th>
                            <th>Fare</th>
                            <th>Seats</th>
                            <th>Bus Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="buses-table-body">
                        <!-- Data will be dynamically loaded here -->
                    </tbody>
                </table>
                <div class="pagination" id="buses-pagination">
                    <!-- Pagination links will be dynamically loaded here -->
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Transaction History</h3>
                <form method="POST" class="d-inline">
                    <button type="submit" name="download_csv" class="btn btn-primary mb-3">Download as CSV</button>
                </form>
                <button id="clear-transactions" class="btn btn-danger mb-3 ml-2">Clear Transactions</button>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>User</th>
                            <th>Bus Name</th>
                            <th>Route</th>
                            <th>Journey Date</th>
                            <th>Seats</th>
                            <th>Amount</th>
                            <th>Payment Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-table-body">
                        <!-- Data will be dynamically loaded here -->
                    </tbody>
                </table>
                <div class="pagination" id="transactions-pagination">
                    <!-- Pagination links will be dynamically loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Seat Map Modal -->
    <div class="modal fade" id="seatMapModal" tabindex="-1" role="dialog" aria-labelledby="seatMapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="seatMapModalLabel">Bus Seat Map</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Bus front indicator -->
                    <div class="text-center mb-3">
                        <strong>FRONT OF BUS</strong>
                        <hr>
                    </div>
                    
                    <!-- Driver seat -->
                    <div class="d-flex justify-content-end mb-4">
                        <div style="width: 50px; height: 50px; background-color: #343a40; color: white; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                    
                    <!-- Seat map container -->
                    <div id="seat-map-container" class="text-center">
                        <!-- Seats will be dynamically loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="seat-legend d-flex justify-content-center w-100 mb-3">
                        <div class="mr-4">
                            <span class="seat-sample" style="background-color: #e9ecef; display: inline-block; width: 20px; height: 20px; margin-right: 5px;"></span> Available
                        </div>
                        <div>
                            <span class="seat-sample" style="background-color: #dc3545; display: inline-block; width: 20px; height: 20px; margin-right: 5px;"></span> Booked
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to fetch data for the Long Route Buses section
        function fetchBusesData(endpoint, tableBodyId, paginationId, page = 1) {
            $.ajax({
                url: endpoint,
                type: 'GET',
                data: { page: page },
                dataType: 'json',
                success: function(response) {
                    const tableBody = $(`#${tableBodyId}`);
                    tableBody.empty();

                    if (response.data.length > 0) {
                        response.data.forEach(row => {
                            let rowHtml = '<tr>';
                            rowHtml += `<td>${row.bus_id}</td>`; // Bus ID
                            rowHtml += `<td>${row.bus_name}</td>`; // Bus Name
                            rowHtml += `<td>${row.from_location} to ${row.to_location}</td>`; // Route
                            rowHtml += `<td>${row.departure_time}</td>`; // Departure Time
                            rowHtml += `<td>${row.fare}</td>`; // Fare
                            rowHtml += `<td>${row.total_seats}</td>`; // Total Seats
                            rowHtml += `<td>${row.bus_type}</td>`; // Bus Type
                            rowHtml += `<td>
                                <button class="btn btn-danger delete-bus" data-bus-id="${row.bus_id}">Delete</button>
                                <button class="btn btn-info view-seats" data-bus-id="${row.bus_id}">View Seats</button>
                            </td>`;
                            rowHtml += '</tr>';
                            tableBody.append(rowHtml);
                        });
                    } else {
                        tableBody.append('<tr><td colspan="9">No data available</td></tr>');
                    }

                    // Update the pagination
                    const pagination = $(`#${paginationId}`);
                    pagination.empty();

                    for (let i = 1; i <= response.total_pages; i++) {
                        pagination.append(`
                            <a href="#" class="pagination-link ${i === response.current_page ? 'active' : ''}" 
                               data-page="${i}" 
                               data-endpoint="${endpoint}" 
                               data-table-body="${tableBodyId}" 
                               data-pagination="${paginationId}">
                                ${i}
                            </a>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('Failed to fetch buses data.');
                }
            });
        }

        // Function to fetch data for the Transactions section
        function fetchTransactionsData(endpoint, tableBodyId, paginationId, page = 1) {
            $.ajax({
                url: endpoint,
                type: 'GET',
                data: { page: page },
                dataType: 'json',
                success: function(response) {
                    const tableBody = $(`#${tableBodyId}`);
                    tableBody.empty();

                    if (response.data.length > 0) {
                        response.data.forEach(row => {
                            let rowHtml = '<tr>';
                            rowHtml += `<td>${row.payment_transaction_id}</td>`; // Payment Transaction ID
                            rowHtml += `<td>${row.username}</td>`; // User
                            rowHtml += `<td>${row.bus_name}</td>`; // Bus Name
                            rowHtml += `<td>${row.from_location} to ${row.to_location}</td>`; // Route
                            rowHtml += `<td>${row.journey_date}</td>`; // Journey Date
                            rowHtml += `<td>${row.seat_numbers}</td>`; // Seats
                            rowHtml += `<td>${row.amount}</td>`; // Amount
                            rowHtml += `<td>${row.payment_time}</td>`; // Payment Time
                            rowHtml += `<td>${row.payment_status}</td>`; // Status
                            rowHtml += '</tr>';
                            tableBody.append(rowHtml);
                        });
                    } else {
                        tableBody.append('<tr><td colspan="9">No data available</td></tr>');
                    }

                    // Update the pagination
                    const pagination = $(`#${paginationId}`);
                    pagination.empty();

                    for (let i = 1; i <= response.total_pages; i++) {
                        pagination.append(`
                            <a href="#" class="pagination-link ${i === response.current_page ? 'active' : ''}" 
                               data-page="${i}" 
                               data-endpoint="${endpoint}" 
                               data-table-body="${tableBodyId}" 
                               data-pagination="${paginationId}">
                                ${i}
                            </a>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('Failed to fetch transactions data.');
                }
            });
        }

        // Handle pagination link clicks
        $(document).on('click', '.pagination-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            const endpoint = $(this).data('endpoint');
            const tableBodyId = $(this).data('table-body');
            const paginationId = $(this).data('pagination');

            if (endpoint === 'admin_lr_fetch_buses.php') {
                fetchBusesData(endpoint, tableBodyId, paginationId, page);
            } else if (endpoint === 'admin_lr_fetch_transactions.php') {
                fetchTransactionsData(endpoint, tableBodyId, paginationId, page);
            }
        });

        // Function to handle the Delete button click
        $(document).on('click', '.delete-bus', function () {
            const busId = $(this).data('bus-id');
            const busRow = $(this).closest('tr');
            
            // Confirm deletion
            if (confirm('Are you sure you want to delete this bus? This action cannot be undone.')) {
                // Show loading state
                $(this).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                $(this).prop('disabled', true);
                
                // Send AJAX request to delete the bus
                $.ajax({
                    url: 'admin_lr_delete_bus.php',
                    type: 'POST',
                    data: {bus_id: busId},
                    dataType: 'json',
                    success: function(response) {
                        // Alert the message from server
                        alert(response.message);
                        
                        // If deletion was successful, remove the row
                        if (response.message.includes('successfully')) {
                            busRow.fadeOut(400, function() {
                                $(this).remove();
                            });
                        }
                        
                        // Refresh the buses table
                        fetchBusesData('admin_lr_fetch_buses.php', 'buses-table-body', 'buses-pagination');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        console.log(xhr.responseText);
                        alert('Failed to delete bus. Error: ' + error);
                        
                        // Reset button state
                        $(this).html('Delete');
                        $(this).prop('disabled', false);
                    }
                });
            }
        });

        // Function to handle the View Seats button click
        $(document).on('click', '.view-seats', function () {
            const busId = $(this).data('bus-id');
            const busRow = $(this).closest('tr');
            const busName = busRow.find('td:nth-child(2)').text();
            
            // Clear any previous seat map
            $('#seat-map-container').empty().html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading seat map...</div>');
            
            // Update modal title with bus name
            $('#seatMapModalLabel').text(`Bus Seat Map - ${busName}`);
            
            // Show the modal
            $('#seatMapModal').modal('show');

            // Log for debugging
            console.log(`Fetching seats for bus ID: ${busId}`);

            // Fetch seat map data for the selected bus
            $.ajax({
                url: 'admin_lr_fetch_seats.php',
                type: 'GET',
                data: { bus_id: busId },
                dataType: 'json',
                success: function (response) {
                    console.log('Seat data response:', response);
                    
                    const seatMapContainer = $('#seat-map-container');
                    seatMapContainer.empty();

                    if (response.seats && response.seats.length > 0) {
                        // Create a structured bus layout
                        const busContainer = $('<div class="bus-container"></div>');
                        
                        // Group seats by row (A, B, C, D)
                        const seatsByRow = {};
                        
                        // Process each seat in the response
                        response.seats.forEach(seat => {
                            // Extract the row letter and seat number
                            const rowLetter = seat.seat_number.charAt(0);
                            const seatNum = seat.seat_number.substring(1);
                            
                            if (!seatsByRow[rowLetter]) {
                                seatsByRow[rowLetter] = [];
                            }
                            
                            seatsByRow[rowLetter].push({
                                id: seat.seat_id, // Changed from seat.id to seat.seat_id
                                number: seat.seat_number,
                                status: seat.status,
                                seatNum: parseInt(seatNum)
                            });
                        });
                        
                        // Sort each row's seats by seat number
                        Object.keys(seatsByRow).forEach(rowLetter => {
                            seatsByRow[rowLetter].sort((a, b) => a.seatNum - b.seatNum);
                        });
                        
                        // Get the max number of columns (seats per row)
                        const maxSeats = Math.max(...Object.values(seatsByRow).map(seats => seats.length));
                        
                        // Create rows of seats with 2×2 configuration
                        for (let i = 0; i < maxSeats; i++) {
                            const busRow = $('<div class="bus-row"></div>');
                            
                            // Left side - Row A and B side by side
                            const leftSide = $('<div class="bus-side left-side"></div>');
                            
                            // Add seat from Row A (if exists)
                            if (seatsByRow['A'] && seatsByRow['A'][i]) {
                                const seat = seatsByRow['A'][i];
                                const seatClass = seat.status === 'booked' ? 'seat booked' : 'seat';
                                leftSide.append(`<div class="${seatClass}" data-seat-id="${seat.id}">${seat.number}</div>`);
                            }
                            
                            // Add seat from Row B (if exists)
                            if (seatsByRow['B'] && seatsByRow['B'][i]) {
                                const seat = seatsByRow['B'][i];
                                const seatClass = seat.status === 'booked' ? 'seat booked' : 'seat';
                                leftSide.append(`<div class="${seatClass}" data-seat-id="${seat.id}">${seat.number}</div>`);
                            }
                            
                            busRow.append(leftSide);
                            
                            // Aisle
                            busRow.append($('<div class="bus-aisle">↕️</div>'));
                            
                            // Right side - Row C and D side by side
                            const rightSide = $('<div class="bus-side right-side"></div>');
                            
                            // Add seat from Row C (if exists)
                            if (seatsByRow['C'] && seatsByRow['C'][i]) {
                                const seat = seatsByRow['C'][i];
                                const seatClass = seat.status === 'booked' ? 'seat booked' : 'seat';
                                rightSide.append(`<div class="${seatClass}" data-seat-id="${seat.id}">${seat.number}</div>`);
                            }
                            
                            // Add seat from Row D (if exists)
                            if (seatsByRow['D'] && seatsByRow['D'][i]) {
                                const seat = seatsByRow['D'][i];
                                const seatClass = seat.status === 'booked' ? 'seat booked' : 'seat';
                                rightSide.append(`<div class="${seatClass}" data-seat-id="${seat.id}">${seat.number}</div>`);
                            }
                            
                            busRow.append(rightSide);
                            
                            busContainer.append(busRow);
                        }
                        
                        seatMapContainer.append(busContainer);
                    } else {
                        seatMapContainer.html('<p class="text-center">No seats available for this bus.</p>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching seat map:', error);
                    alert('Failed to load seat map. Please try again.');
                }
            });
        });

        // Fetch initial data
        $(document).ready(function() {
            $('#buses-table-body').html('<tr><td colspan="9" class="text-center">Loading buses data...</td></tr>');
            $('#transactions-table-body').html('<tr><td colspan="9" class="text-center">Loading transactions data...</td></tr>');

            fetchBusesData('admin_lr_fetch_buses.php', 'buses-table-body', 'buses-pagination');
            fetchTransactionsData('admin_lr_fetch_transactions.php', 'transactions-table-body', 'transactions-pagination');
        });

        // Add this right after your existing script tags or merge with existing scripts
        $(document).ready(function() {
            // Event delegation for delete bus buttons
            $(document).on('click', '.delete-bus', function() {
                const busId = $(this).data('bus-id');
                const busRow = $(this).closest('tr');
                
                // Confirm deletion
                if (confirm('Are you sure you want to delete this bus? This action cannot be undone.')) {
                    // Show loading state
                    $(this).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                    $(this).prop('disabled', true);
                    
                    // Send AJAX request to delete the bus
                    $.ajax({
                        url: 'admin_lr_delete_bus.php',
                        type: 'POST',
                        data: {bus_id: busId},
                        dataType: 'json',
                        success: function(response) {
                            // Alert the message from server
                            alert(response.message);
                            
                            // If deletion was successful, remove the row
                            if (response.message.includes('successfully')) {
                                busRow.fadeOut(400, function() {
                                    $(this).remove();
                                });
                            }
                            
                            // Refresh the buses table
                            fetchBusesData('admin_lr_fetch_buses.php', 'buses-table-body', 'buses-pagination');
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            console.log(xhr.responseText);
                            alert('Failed to delete bus. Error: ' + error);
                            
                            // Reset button state
                            $(this).html('Delete');
                            $(this).prop('disabled', false);
                        }
                    });
                }
            });
        });

        // Event handler for Clear Transactions button
        $(document).on('click', '#clear-transactions', function() {
            if (confirm('WARNING: This will permanently delete ALL transaction records. This action cannot be undone. Are you sure you want to continue?')) {
                // Show a second confirmation for extra safety
                if (confirm('FINAL WARNING: You are about to delete ALL transaction history. Click OK to proceed.')) {
                    // Show loading state
                    $(this).html('<i class="fas fa-spinner fa-spin"></i> Clearing...');
                    $(this).prop('disabled', true);
                    
                    $.ajax({
                        url: 'admin_lr_clear_transactions.php',
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            alert(response.message);
                            
                            // Refresh the transactions table
                            fetchTransactionsData('admin_lr_fetch_transactions.php', 'transactions-table-body', 'transactions-pagination');
                            
                            // Reset button state
                            $('#clear-transactions').html('Clear Transactions');
                            $('#clear-transactions').prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error clearing transactions:', error);
                            alert('Failed to clear transactions. Error: ' + error);
                            
                            // Reset button state
                            $('#clear-transactions').html('Clear Transactions');
                            $('#clear-transactions').prop('disabled', false);
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>