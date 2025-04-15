<?php
session_start();
include 'db.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle add new route
if (isset($_POST['add_route'])) {
    $origin = $_POST['origin'];
    $destination = $_POST['destination'];
    $distance = $_POST['distance'];
    $fare = $_POST['fare'];
    
    // Validate that origin and destination are different
    if ($origin == $destination) {
        $_SESSION['error_message'] = "Origin and destination cannot be the same.";
        header('Location: admin_local_bus.php');
        exit;
    }
    
    // Check if route or its symmetric already exists
    $check_query = "SELECT COUNT(*) as count FROM local_routes 
                    WHERE (origin = ? AND destination = ?) 
                    OR (origin = ? AND destination = ?)";
    $check_stmt = $conn3->prepare($check_query);
    $check_stmt->bind_param("ssss", $origin, $destination, $destination, $origin);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();
    
    if ($check_row['count'] > 0) {
        $_SESSION['error_message'] = "This route (or its reverse) already exists.";
        header('Location: admin_local_bus.php');
        exit;
    }
    
    // Start transaction to ensure both routes are added or none
    $conn3->begin_transaction();
    
    try {
        // Insert original route (A to B)
        $query = "INSERT INTO local_routes (origin, destination, distance, fare) VALUES (?, ?, ?, ?)";
        $stmt = $conn3->prepare($query);
        $stmt->bind_param("ssdd", $origin, $destination, $distance, $fare);
        $stmt->execute();
        
        // Insert symmetric route (B to A) with the same distance and fare
        $stmt = $conn3->prepare($query);
        $stmt->bind_param("ssdd", $destination, $origin, $distance, $fare);
        $stmt->execute();
        
        // Commit transaction
        $conn3->commit();
        
        $_SESSION['success_message'] = "Route added successfully in both directions!";
    } catch (Exception $e) {
        // Rollback in case of error
        $conn3->rollback();
        $_SESSION['error_message'] = "Failed to add route: " . $e->getMessage();
    }
    
    header('Location: admin_local_bus.php');
    exit;
}

// Handle delete route
if (isset($_GET['delete_route'])) {
    $route_id = (int)$_GET['delete_route'];
    
    // Get the route details first
    $route_query = "SELECT origin, destination FROM local_routes WHERE id = ?";
    $route_stmt = $conn3->prepare($route_query);
    $route_stmt->bind_param("i", $route_id);
    $route_stmt->execute();
    $route_result = $route_stmt->get_result();
    $route = $route_result->fetch_assoc();
    
    if (!$route) {
        $_SESSION['error_message'] = "Route not found.";
        header('Location: admin_local_bus.php');
        exit;
    }
    
    // First check if route is used in any transaction
    $check_query = "SELECT COUNT(*) as count FROM bus_transactions WHERE 
                   (origin = ? AND destination = ?) OR
                   (origin = ? AND destination = ?)";
    $check_stmt = $conn3->prepare($check_query);
    $check_stmt->bind_param("ssss", $route['origin'], $route['destination'], 
                                   $route['destination'], $route['origin']);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $_SESSION['error_message'] = "Cannot delete route as it has associated transactions.";
    } else {
        // Check if route is used by any bus
        $check_bus_query = "SELECT COUNT(*) as count FROM local_buses WHERE 
                           (origin = ? AND destination = ?) OR
                           (origin = ? AND destination = ?)";
        $check_bus_stmt = $conn3->prepare($check_bus_query);
        $check_bus_stmt->bind_param("ssss", $route['origin'], $route['destination'], 
                                          $route['destination'], $route['origin']);
        $check_bus_stmt->execute();
        $bus_result = $check_bus_stmt->get_result();
        $bus_row = $bus_result->fetch_assoc();
        
        if ($bus_row['count'] > 0) {
            $_SESSION['error_message'] = "Cannot delete route as it is used by buses.";
        } else {
            // Safe to delete both the route and its symmetric counterpart
            $conn3->begin_transaction();
            
            try {
                // Delete this route
                $delete_query = "DELETE FROM local_routes WHERE id = ?";
                $delete_stmt = $conn3->prepare($delete_query);
                $delete_stmt->bind_param("i", $route_id);
                $delete_stmt->execute();
                
                // Delete the symmetric route
                $delete_sym_query = "DELETE FROM local_routes WHERE origin = ? AND destination = ?";
                $delete_sym_stmt = $conn3->prepare($delete_sym_query);
                $delete_sym_stmt->bind_param("ss", $route['destination'], $route['origin']);
                $delete_sym_stmt->execute();
                
                $conn3->commit();
                $_SESSION['success_message'] = "Route deleted successfully in both directions!";
            } catch (Exception $e) {
                $conn3->rollback();
                $_SESSION['error_message'] = "Failed to delete route: " . $e->getMessage();
            }
        }
    }
    
    header('Location: admin_local_bus.php');
    exit;
}

// Handle update fare
if (isset($_POST['update_fare'])) {
    $route_id = (int)$_POST['route_id'];
    $new_fare = (float)$_POST['new_fare'];
    
    // Get the route details first
    $route_query = "SELECT origin, destination FROM local_routes WHERE id = ?";
    $route_stmt = $conn3->prepare($route_query);
    $route_stmt->bind_param("i", $route_id);
    $route_stmt->execute();
    $route_result = $route_stmt->get_result();
    $route = $route_result->fetch_assoc();
    
    if ($route) {
        // Start transaction
        $conn3->begin_transaction();
        
        try {
            // Update the selected route's fare
            $update_query = "UPDATE local_routes SET fare = ? WHERE id = ?";
            $update_stmt = $conn3->prepare($update_query);
            $update_stmt->bind_param("di", $new_fare, $route_id);
            $update_stmt->execute();
            
            // Update the symmetric route with the same fare
            $symmetric_query = "UPDATE local_routes SET fare = ? 
                                WHERE origin = ? AND destination = ?";
            $symmetric_stmt = $conn3->prepare($symmetric_query);
            $symmetric_stmt->bind_param("dss", $new_fare, $route['destination'], $route['origin']);
            $symmetric_stmt->execute();
            
            // Commit transaction
            $conn3->commit();
            
            $_SESSION['success_message'] = "Fare updated successfully for both directions!";
        } catch (Exception $e) {
            // Rollback in case of error
            $conn3->rollback();
            $_SESSION['error_message'] = "Failed to update fare: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Route not found.";
    }
    
    header('Location: admin_local_bus.php');
    exit;
}

// Handle add bus to route
if (isset($_POST['add_bus'])) {
    $bus_name = $_POST['bus_name'];
    $capacity = $_POST['capacity'];
    $origin = $_POST['bus_origin'];
    $destination = $_POST['bus_destination'];
    
    $query = "INSERT INTO local_buses (bus_name, capacity, origin, destination) VALUES (?, ?, ?, ?)";
    $stmt = $conn3->prepare($query);
    $stmt->bind_param("siss", $bus_name, $capacity, $origin, $destination);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Bus added successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to add bus: " . $conn3->error;
    }
    
    header('Location: admin_local_bus.php');
    exit;
}

// Handle delete bus
if (isset($_GET['delete_bus'])) {
    $bus_id = (int)$_GET['delete_bus'];
    
    // Check if bus is used in any transaction
    $check_query = "SELECT COUNT(*) as count FROM bus_transactions WHERE 
                    bus_name = (SELECT bus_name FROM local_buses WHERE id = ?)";
    $check_stmt = $conn3->prepare($check_query);
    $check_stmt->bind_param("i", $bus_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $_SESSION['error_message'] = "Cannot delete bus as it has associated transactions.";
    } else {
        // Safe to delete
        $delete_query = "DELETE FROM local_buses WHERE id = ?";
        $delete_stmt = $conn3->prepare($delete_query);
        $delete_stmt->bind_param("i", $bus_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = "Bus deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete bus: " . $conn3->error;
        }
    }
    
    header('Location: admin_local_bus.php');
    exit;
}

// Get all routes
$routes_query = "SELECT * FROM local_routes ORDER BY origin, destination";
$routes_result = $conn3->query($routes_query);

// Get all buses
$buses_query = "SELECT * FROM local_buses ORDER BY bus_name, origin, destination";
$buses_result = $conn3->query($buses_query);

// Get all distinct origins and destinations for dropdowns
$locations_query = "SELECT DISTINCT origin FROM local_routes UNION SELECT DISTINCT destination FROM local_routes ORDER BY origin";
$locations_result = $conn3->query($locations_query);
$locations = [];
while ($row = $locations_result->fetch_assoc()) {
    $locations[] = $row['origin'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Bus Management - Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .nav-tabs .nav-link.active {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .alert {
            margin-top: 15px;
        }
        .action-buttons .btn {
            margin-right: 5px;
        }
        .card {
            margin-bottom: 20px;
        }
        .btn-group-sm > .btn, .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.76563rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2><i class="fas fa-bus"></i> Local Bus Admin Management</h2>
            </div>
            <div class="col-md-6 text-right">
                <a href="admin_dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message'] ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_message'] ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="routes-tab" data-toggle="tab" href="#routes" role="tab">
                    <i class="fas fa-route"></i> Routes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="buses-tab" data-toggle="tab" href="#buses" role="tab">
                    <i class="fas fa-bus-alt"></i> Buses
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="transactions-tab" data-toggle="tab" href="#transactions" role="tab">
                    <i class="fas fa-exchange-alt"></i> Transactions
                </a>
            </li>
        </ul>
        
        <div class="tab-content" id="tabContent">
            <!-- Routes Tab -->
            <div class="tab-pane fade show active" id="routes" role="tabpanel">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Route</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> When you add a route from A to B, the symmetric route from B to A will also be automatically added with the same fare.
                                </div>
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="origin">Origin</label>
                                        <input type="text" class="form-control" id="origin" name="origin" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="destination">Destination</label>
                                        <input type="text" class="form-control" id="destination" name="destination" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="distance">Distance (km)</label>
                                        <input type="number" step="0.1" class="form-control" id="distance" name="distance" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="fare">Fare (BDT)</label>
                                        <input type="number" step="0.01" class="form-control" id="fare" name="fare" required>
                                    </div>
                                    <button type="submit" name="add_route" class="btn btn-primary btn-block">
                                        <i class="fas fa-plus"></i> Add Route
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-route"></i> Existing Routes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Origin</th>
                                                <th>Destination</th>
                                                <th>Distance</th>
                                                <th>Fare</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($routes_result && $routes_result->num_rows > 0): ?>
                                                <?php while ($route = $routes_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?= $route['id'] ?></td>
                                                        <td><?= htmlspecialchars($route['origin']) ?></td>
                                                        <td><?= htmlspecialchars($route['destination']) ?></td>
                                                        <td><?= $route['distance'] ?> km</td>
                                                        <td><?= $route['fare'] ?> BDT</td>
                                                        <td class="action-buttons">
                                                            <button type="button" class="btn btn-sm btn-info edit-fare" 
                                                                    data-toggle="modal" data-target="#editFareModal"
                                                                    data-id="<?= $route['id'] ?>"
                                                                    data-current="<?= $route['fare'] ?>">
                                                                <i class="fas fa-edit"></i> Edit Fare
                                                            </button>
                                                            <a href="admin_local_bus.php?delete_route=<?= $route['id'] ?>" 
                                                               class="btn btn-sm btn-danger"
                                                               onclick="return confirm('Are you sure you want to delete this route?');">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No routes found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Buses Tab -->
            <div class="tab-pane fade" id="buses" role="tabpanel">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Bus</h5>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="bus_name">Bus Name</label>
                                        <input type="text" class="form-control" id="bus_name" name="bus_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="capacity">Capacity</label>
                                        <input type="number" class="form-control" id="capacity" name="capacity" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="bus_origin">Origin</label>
                                        <select class="form-control" id="bus_origin" name="bus_origin" required>
                                            <option value="">Select Origin</option>
                                            <?php foreach ($locations as $location): ?>
                                                <option value="<?= htmlspecialchars($location) ?>">
                                                    <?= htmlspecialchars($location) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="bus_destination">Destination</label>
                                        <select class="form-control" id="bus_destination" name="bus_destination" required>
                                            <option value="">Select Destination</option>
                                            <?php foreach ($locations as $location): ?>
                                                <option value="<?= htmlspecialchars($location) ?>">
                                                    <?= htmlspecialchars($location) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" name="add_bus" class="btn btn-primary btn-block">
                                        <i class="fas fa-plus"></i> Add Bus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-bus-alt"></i> Existing Buses</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Bus Name</th>
                                                <th>Capacity</th>
                                                <th>Origin</th>
                                                <th>Destination</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($buses_result && $buses_result->num_rows > 0): ?>
                                                <?php while ($bus = $buses_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?= $bus['id'] ?></td>
                                                        <td><?= htmlspecialchars($bus['bus_name']) ?></td>
                                                        <td><?= $bus['capacity'] ?></td>
                                                        <td><?= htmlspecialchars($bus['origin']) ?></td>
                                                        <td><?= htmlspecialchars($bus['destination']) ?></td>
                                                        <td>
                                                            <a href="admin_local_bus.php?delete_bus=<?= $bus['id'] ?>" 
                                                               class="btn btn-sm btn-danger"
                                                               onclick="return confirm('Are you sure you want to delete this bus?');">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No buses found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Transactions Tab -->
            <div class="tab-pane fade" id="transactions" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Transaction History</h5>
                            <button id="exportTransactions" class="btn btn-sm btn-light">
                                <i class="fas fa-download"></i> Export as CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-row mb-3">
                            <div class="col">
                                <input type="text" id="searchUser" class="form-control" placeholder="Search by username...">
                            </div>
                            <div class="col-auto">
                                <button id="clearFilters" class="btn btn-secondary">Clear Filters</button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped" id="transactionsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Origin</th>
                                        <th>Destination</th>
                                        <th>Bus</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="transactionsBody">
                                    <!-- Transaction data will be loaded here via AJAX -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center my-3" id="loadingTransactions">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        
                        <nav aria-label="Transaction pagination">
                            <ul class="pagination justify-content-center" id="transactionPagination">
                                <!-- Pagination will be generated via JavaScript -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Fare Modal -->
    <div class="modal fade" id="editFareModal" tabindex="-1" role="dialog" aria-labelledby="editFareModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFareModalLabel">Update Fare</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <input type="hidden" name="route_id" id="edit_route_id">
                        <div class="form-group">
                            <label for="new_fare">New Fare (BDT)</label>
                            <input type="number" step="0.01" class="form-control" id="new_fare" name="new_fare" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_fare" class="btn btn-primary">Update Fare</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Current page for pagination
            let currentPage = 1;
            let totalPages = 1;
            
            // Load transactions when the transactions tab is clicked
            $('#transactions-tab').on('click', function() {
                loadTransactions(1);
            });
            
            // Load transactions function
            function loadTransactions(page) {
                currentPage = page;
                const searchUser = $('#searchUser').val();
                
                $('#loadingTransactions').show();
                
                $.ajax({
                    url: 'fetch_bus_transactions.php',
                    type: 'GET',
                    data: {
                        page: page,
                        search_user: searchUser
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#loadingTransactions').hide();
                        
                        // Clear the table
                        $('#transactionsBody').empty();
                        
                        if (response.transactions.length === 0) {
                            $('#transactionsBody').html('<tr><td colspan="8" class="text-center">No transactions found</td></tr>');
                        } else {
                            // Add each transaction to the table
                            response.transactions.forEach(function(transaction) {
                                let statusClass = '';
                                if (transaction.status === 'completed') {
                                    statusClass = 'text-success';
                                } else if (transaction.status === 'failed') {
                                    statusClass = 'text-danger';
                                } else if (transaction.status === 'pending') {
                                    statusClass = 'text-warning';
                                }
                                
                                $('#transactionsBody').append(`
                                    <tr>
                                        <td>${transaction.id}</td>
                                        <td>${transaction.username}</td>
                                        <td>${transaction.origin}</td>
                                        <td>${transaction.destination}</td>
                                        <td>${transaction.bus_name}</td>
                                        <td>${transaction.amount} BDT</td>
                                        <td>${transaction.transaction_date}</td>
                                        <td class="${statusClass}">${transaction.status}</td>
                                    </tr>
                                `);
                            });
                            
                            // Update pagination
                            totalPages = response.total_pages;
                            updatePagination(page, totalPages);
                        }
                    },
                    error: function() {
                        $('#loadingTransactions').hide();
                        $('#transactionsBody').html('<tr><td colspan="8" class="text-center text-danger">Error loading transactions</td></tr>');
                    }
                });
            }
            
            // Update pagination links
            function updatePagination(currentPage, totalPages) {
                const pagination = $('#transactionPagination');
                pagination.empty();
                
                // Previous button
                pagination.append(`
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                    </li>
                `);
                
                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    pagination.append(`
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `);
                }
                
                // Next button
                pagination.append(`
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                    </li>
                `);
                
                // Attach click event to pagination links
                $('.page-link').on('click', function(e) {
                    e.preventDefault();
                    const page = $(this).data('page');
                    if (page >= 1 && page <= totalPages) {
                        loadTransactions(page);
                    }
                });
            }
            
            // Filter events
            $('#searchUser').on('change', function() {
                loadTransactions(1);
            });
            
            // Clear filters
            $('#clearFilters').on('click', function() {
                $('#searchUser').val('');
                loadTransactions(1);
            });
            
            // Edit fare modal
            $('.edit-fare').on('click', function() {
                const routeId = $(this).data('id');
                const currentFare = $(this).data('current');
                
                $('#edit_route_id').val(routeId);
                $('#new_fare').val(currentFare);
            });
            
            // Export transactions as CSV
            $('#exportTransactions').on('click', function() {
                const searchUser = $('#searchUser').val();
                
                window.location.href = `export_bus_transactions.php?search_user=${searchUser}`;
            });
            
            // Validate origin and destination are not the same
            $('form').on('submit', function(e) {
                const origin = $(this).find('[name="origin"], [name="bus_origin"]').val();
                const destination = $(this).find('[name="destination"], [name="bus_destination"]').val();
                
                if (origin === destination && origin !== '' && destination !== '') {
                    e.preventDefault();
                    alert('Origin and destination cannot be the same');
                }
            });
        });
    </script>
</body>
</html>
