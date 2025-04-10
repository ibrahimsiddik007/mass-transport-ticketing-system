<?php
session_start();
include 'db.php'; // Include your database connection file
date_default_timezone_set('Asia/Dhaka'); // Set to your desired time zone

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Pagination variables for stations
$stations_rows_per_page = 10;
$stations_page = isset($_GET['stations_page']) ? (int)$_GET['stations_page'] : 1;
$stations_offset = ($stations_page - 1) * $stations_rows_per_page;

// Fetch stations with pagination
$stations = [];
$query = "SELECT SQL_CALC_FOUND_ROWS * FROM stations LIMIT $stations_rows_per_page OFFSET $stations_offset";
error_log($query);
$result = $conn2->query($query);
while ($row = $result->fetch_assoc()) {
    $stations[] = $row;
}
$total_stations_result = $conn2->query("SELECT FOUND_ROWS() AS total_rows");
$total_stations = $total_stations_result->fetch_assoc()['total_rows'];
$total_stations_pages = ceil($total_stations / $stations_rows_per_page);

// Pagination variables for trains
$trains_rows_per_page = 10;
$trains_page = isset($_GET['trains_page']) ? (int)$_GET['trains_page'] : 1;
$trains_offset = ($trains_page - 1) * $trains_rows_per_page;

// Fetch trains with pagination
$trains = [];
$query = "SELECT SQL_CALC_FOUND_ROWS train_id, train_name, start_point, end_point, TIME_FORMAT(departure_time, '%h:%i %p') AS departure_time, fare FROM trains LIMIT $trains_rows_per_page OFFSET $trains_offset";
$result = $conn2->query($query);
while ($row = $result->fetch_assoc()) {
    $trains[] = $row;
}
$total_trains_result = $conn2->query("SELECT FOUND_ROWS() AS total_rows");
$total_trains = $total_trains_result->fetch_assoc()['total_rows'];
$total_trains_pages = ceil($total_trains / $trains_rows_per_page);

// Pagination variables for compartments
$compartments_rows_per_page = 10;
$compartments_page = isset($_GET['compartments_page']) ? (int)$_GET['compartments_page'] : 1;
$compartments_offset = ($compartments_page - 1) * $compartments_rows_per_page;

// Fetch compartments with pagination
$compartments = [];
$query = "SELECT SQL_CALC_FOUND_ROWS * FROM compartments LIMIT $compartments_rows_per_page OFFSET $compartments_offset";
error_log($query);
$result = $conn2->query($query);
while ($row = $result->fetch_assoc()) {
    $compartments[] = $row;
}
$total_compartments_result = $conn2->query("SELECT FOUND_ROWS() AS total_rows");
$total_compartments = $total_compartments_result->fetch_assoc()['total_rows'];
$total_compartments_pages = ceil($total_compartments / $compartments_rows_per_page);

// Pagination variables for transactions
$transactions_rows_per_page = 10;
$transactions_page = isset($_GET['transactions_page']) ? (int)$_GET['transactions_page'] : 1;
$transactions_offset = ($transactions_page - 1) * $transactions_rows_per_page;

// Fetch transactions with pagination
$transactions = [];
$query = "SELECT SQL_CALC_FOUND_ROWS * FROM train_transactions LIMIT $transactions_rows_per_page OFFSET $transactions_offset";
error_log($query);
$result = $conn2->query($query);
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$total_transactions_result = $conn2->query("SELECT FOUND_ROWS() AS total_rows");
$total_transactions = $total_transactions_result->fetch_assoc()['total_rows'];
$total_transactions_pages = ceil($total_transactions / $transactions_rows_per_page);

// Handle Add Train and Compartments functionality
if (isset($_POST['add_train'])) {
    $train_name = $_POST['train_name'];
    $start_point = $_POST['start_point'];
    $end_point = $_POST['end_point'];
    $departure_time = $_POST['departure_time']; // Ensure this is captured correctly
    error_log("Departure Time from Form: " . $departure_time);
    $fare = $_POST['fare'];
    $num_compartments = $_POST['num_compartments'];

    // Get the time input directly (format it comes in from input type="time": HH:MM)
    $departure_time = $_POST['departure_time']; 

    // Format it for the database (MySQL TIME format: HH:MM:SS)
    $departure_time_24hr = $departure_time . ':00';

    // Insert the train into the trains table
    $stmt = $conn2->prepare("INSERT INTO trains (train_name, start_point, end_point, departure_time, fare) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $train_name, $start_point, $end_point, $departure_time_24hr, $fare);
    if ($stmt->execute()) {
        // Get the ID of the newly inserted train
        $train_id = $conn2->insert_id;

        // Add compartments and seats for the train
        for ($i = 1; $i <= $num_compartments; $i++) {
            // Generate a unique compartment ID
            $compartment_id = 'C' . $train_id . '_' . $i;

            // Insert compartment into the compartments table
            $stmt = $conn2->prepare("INSERT INTO compartments (compartment_id, train_id, total_seats) VALUES (?, ?, ?)");
            $total_seats = 50; // Each compartment has 50 seats
            $stmt->bind_param("sii", $compartment_id, $train_id, $total_seats);
            $stmt->execute();

            // Generate 50 seats for this compartment
            for ($seat_number = 1; $seat_number <= 50; $seat_number++) {
                $stmt = $conn2->prepare("INSERT INTO seats (compartment_id, train_id, seat_number) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $compartment_id, $train_id, $seat_number);
                $stmt->execute();
            }
        }

        // Add symmetric route (reverse route)
        $stmt = $conn2->prepare("INSERT INTO trains (train_name, start_point, end_point, departure_time, fare) VALUES (?, ?, ?, ?, ?)");
        $symmetric_train_name = $train_name;
        $stmt->bind_param("ssssi", $symmetric_train_name, $end_point, $start_point, $departure_time_24hr, $fare);
        if ($stmt->execute()) {
            // Get the ID of the symmetric train
            $symmetric_train_id = $conn2->insert_id;

            // Add compartments and seats for the symmetric train
            for ($i = 1; $i <= $num_compartments; $i++) {
                // Generate a unique compartment ID for the symmetric train
                $compartment_id = 'C' . $symmetric_train_id . '_' . $i;

                // Insert compartment into the compartments table
                $stmt = $conn2->prepare("INSERT INTO compartments (compartment_id, train_id, total_seats) VALUES (?, ?, ?)");
                $total_seats = 50; // Each compartment has 50 seats
                $stmt->bind_param("sii", $compartment_id, $symmetric_train_id, $total_seats);
                $stmt->execute();

                // Generate 50 seats for this compartment
                for ($seat_number = 1; $seat_number <= 50; $seat_number++) {
                    $stmt = $conn2->prepare("INSERT INTO seats (compartment_id, train_id, seat_number) VALUES (?, ?, ?)");
                    $stmt->bind_param("sii", $compartment_id, $symmetric_train_id, $seat_number);
                    $stmt->execute();
                }
            }
        }

        echo "<script>alert('Train, compartments, and seats added successfully, including symmetric route!');</script>";
    } else {
        echo "<script>alert('Failed to add train. Please try again.');</script>";
    }
    $stmt->close();
}

// Handle Add Compartments functionality
if (isset($_POST['add_compartments'])) {
    $train_id = $_POST['train_id'];
    $num_compartments = $_POST['num_compartments'];

    for ($i = 1; $i <= $num_compartments; $i++) {
        // Generate a unique compartment ID
        $compartment_id = 'C' . $train_id . '_' . $i;

        // Insert compartment into the compartments table
        $stmt = $conn2->prepare("INSERT INTO compartments (compartment_id, train_id, total_seats) VALUES (?, ?, ?)");
        $total_seats = 50; // Each compartment has 50 seats
        $stmt->bind_param("sii", $compartment_id, $train_id, $total_seats);
        $stmt->execute();

        // Generate 50 seats for this compartment
        for ($seat_number = 1; $seat_number <= 50; $seat_number++) {
            $stmt = $conn2->prepare("INSERT INTO seats (compartment_id, train_id, seat_number) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $compartment_id, $train_id, $seat_number);
            $stmt->execute();
        }
    }
    echo "<script>alert('Compartments and seats added successfully!');</script>";
}

// Handle CSV download
if (isset($_POST['download_csv'])) {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="train_transactions_' . date('Y-m-d') . '.csv"');
    
    // Create a file handle for PHP to output the CSV data
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, array('Transaction ID', 'User ID', 'Amount', 'Compartment ID', 'Train ID', 'Seats', 'Payment Time', 'Departure Time'));
    
    // Fetch all transactions (not just paginated ones)
    $query = "SELECT * FROM train_transactions";
    $result = $conn2->query($query);
    
    // Loop through data and output as CSV rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
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
    <title>Train Management - Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        .container {
            margin-top: 50px;
        }
        .card {
            margin-bottom: 20px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            border: 1px solid #007bff;
            color: #007bff;
            text-decoration: none;
            border-radius: 5px;
        }
        .pagination a.active {
            background-color: #007bff;
            color: #fff;
        }
        .pagination a:hover {
            background-color: #0056b3;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Train Management</h1>

        <div class="text-left mb-3">
            <a href="admin_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- View Stations -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Stations</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Station Name</th>
                            <th>District</th>
                        </tr>
                    </thead>
                    <tbody id="stations-table-body">
                        <!-- Data will be dynamically loaded here -->
                    </tbody>
                </table>
                <div class="pagination" id="stations-pagination">
                    <!-- Pagination links will be dynamically loaded here -->
                </div>
            </div>
        </div>

        <!-- View Trains -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Trains</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Train ID</th>
                            <th>Train Name</th>
                            <th>Start Point</th>
                            <th>End Point</th>
                            <th>Departure Time</th>
                            <th>Fare</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="trains-table-body">
                        <!-- Data will be dynamically loaded here -->
                    </tbody>
                </table>
                <div class="pagination" id="trains-pagination">
                    <!-- Pagination links will be dynamically loaded here -->
                </div>
            </div>
        </div>

        <!-- Add Train and Compartments -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Add Train and Compartments</h3>
                <form method="POST">
                    <!-- Train Details -->
                    <div class="form-group">
                        <label for="train_name">Train Name</label>
                        <input type="text" class="form-control" id="train_name" name="train_name" required>
                    </div>
                    <div class="form-group">
                        <label for="start_point">Start Point</label>
                        <input type="text" class="form-control" id="start_point" name="start_point" required>
                    </div>
                    <div class="form-group">
                        <label for="end_point">End Point</label>
                        <input type="text" class="form-control" id="end_point" name="end_point" required>
                    </div>
                    <div class="form-group">
                        <label for="departure_time">Departure Time</label>
                        <input type="time" class="form-control" id="departure_time" name="departure_time" required>
                    </div>
                    <div class="form-group">
                        <label for="fare">Fare (BDT)</label>
                        <input type="number" class="form-control" id="fare" name="fare" required>
                    </div>

                    <!-- Compartment Details -->
                    <div class="form-group">
                        <label for="num_compartments">Number of Compartments</label>
                        <input type="number" class="form-control" id="num_compartments" name="num_compartments" min="1" required>
                    </div>

                    <button type="submit" name="add_train" class="btn btn-primary">Add Train and Compartments</button>
                </form>
            </div>
        </div>

        <!-- View Compartments -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Compartments</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Compartment ID</th>
                            <th>Train ID</th>
                            <th>Total Seats</th>
                        </tr>
                    </thead>
                    <tbody id="compartments-table-body">
                        <!-- Data will be dynamically loaded here -->
                    </tbody>
                </table>
                <!-- Pagination for Compartments -->
                <div class="pagination" id="compartments-pagination">
                    <!-- Pagination links will be dynamically loaded here -->
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Transaction History</h3>
                <form method="POST">
                    <button type="submit" name="download_csv" class="btn btn-primary">Download as CSV</button>
                </form>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>User ID</th>
                            <th>Amount</th>
                            <th>Compartment ID</th>
                            <th>Train ID</th>
                            <th>Seats</th>
                            <th>Payment Time</th>
                            <th>Departure Time</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-table-body">
                        <!-- Data will be dynamically loaded here -->
                    </tbody>
                </table>
                <!-- Pagination for Transactions -->
                <div class="pagination" id="transactions-pagination">
                    <!-- Pagination links will be dynamically loaded here -->
                </div>
            </div>
        </div>
    </div>
    <script>
        function fetchData(endpoint, tableBodyId, paginationId, page = 1) {
            $.ajax({
                url: endpoint,
                type: 'GET',
                data: { page: page },
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response); // Debug the response

                    // Update the table body
                    const tableBody = $(`#${tableBodyId}`);
                    tableBody.empty();

                    if (response.data.length > 0) {
                        response.data.forEach(row => {
                            let rowHtml = '<tr>';
                            for (const key in row) {
                                rowHtml += `<td>${row[key]}</td>`;
                            }
                            // Add delete button
                            if (tableBodyId === 'trains-table-body') {
                                rowHtml += `<td>
                                    <button class="btn btn-danger btn-sm delete-train" data-train-id="${row.train_id}">Delete</button>
                                </td>`;
                            }
                            rowHtml += '</tr>';
                            tableBody.append(rowHtml);
                        });
                    } else {
                        tableBody.append('<tr><td colspan="100%">No data available</td></tr>');
                    }

                    // Update the pagination
                    const pagination = $(`#${paginationId}`);
                    pagination.empty();

                    for (let i = 1; i <= response.total_pages; i++) {
                        pagination.append(`
                            <a href="#" class="pagination-link ${i === response.current_page ? 'active' : ''}" data-page="${i}" data-endpoint="${endpoint}" data-table-body="${tableBodyId}" data-pagination="${paginationId}">
                                ${i}
                            </a>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('Failed to fetch data.');
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
            fetchData(endpoint, tableBodyId, paginationId, page);
        });

        // Handle delete train button clicks
        $(document).on('click', '.delete-train', function() {
            const trainId = $(this).data('train-id');
            if (confirm('Are you sure you want to delete this train? This action cannot be undone.')) {
                $.ajax({
                    url: 'delete_train.php',
                    type: 'POST',
                    data: { train_id: trainId },
                    success: function(response) {
                        if (response.success) {
                            alert('Train deleted successfully!');
                        }
                        fetchData('admin_t_fetch_trains.php', 'trains-table-body', 'trains-pagination');
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        alert('Failed to delete train.');
                    }
                });
            }
        });

        // Fetch initial data for all tables
        $(document).ready(function() {
            fetchData('admin_t_fetch_stations.php', 'stations-table-body', 'stations-pagination');
            fetchData('admin_t_fetch_trains.php', 'trains-table-body', 'trains-pagination');
            fetchData('admin_t_fetch_compartments.php', 'compartments-table-body', 'compartments-pagination');
            fetchData('admin_t_fetch_transactions.php', 'transactions-table-body', 'transactions-pagination');
        });
    </script>
</body>
</html>