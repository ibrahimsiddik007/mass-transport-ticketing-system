<?php
session_start();
include 'db.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle fare update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_fare'])) {
    $from_station = $_POST['from_station']; // Station name, not ID
    $to_station = $_POST['to_station'];     // Station name, not ID
    $new_fare = (int)$_POST['fare'];
    
    if ($new_fare < 10) {
        $_SESSION['error_message'] = "Fare cannot be less than 10";
    } else {
        // Check if route exists
        $check_query = "SELECT id FROM ticket_routes WHERE start_point = ? AND end_point = ?";
        $check_stmt = $conn1->prepare($check_query);
        $check_stmt->bind_param("ss", $from_station, $to_station);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing route
            $query = "UPDATE ticket_routes SET fare = ? WHERE start_point = ? AND end_point = ?";
            $stmt = $conn1->prepare($query);
            $stmt->bind_param("iss", $new_fare, $from_station, $to_station);
        } else {
            // Insert new route
            $query = "INSERT INTO ticket_routes (start_point, end_point, fare) VALUES (?, ?, ?)";
            $stmt = $conn1->prepare($query);
            $stmt->bind_param("ssi", $from_station, $to_station, $new_fare);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Fare updated successfully";
        } else {
            $_SESSION['error_message'] = "Failed to update fare: " . $conn1->error;
        }
    }
}

// Get all stations
$stations_query = "SELECT id, s_name, active FROM metro_stations ORDER BY s_name";
$stations_result = $conn1->query($stations_query);

$stations = [];
while ($row = $stations_result->fetch_assoc()) {
    $stations[$row['s_name']] = [
        'id' => $row['id'],
        'name' => $row['s_name'],
        'active' => $row['active']
    ];
}

// Get fares between stations
$fares_query = "SELECT tr.id, tr.start_point, tr.end_point, tr.fare, 
                ms1.active as from_active, ms2.active as to_active
                FROM ticket_routes tr
                JOIN metro_stations ms1 ON tr.start_point = ms1.s_name
                JOIN metro_stations ms2 ON tr.end_point = ms2.s_name
                ORDER BY tr.start_point, tr.end_point";

$fares_result = $conn1->query($fares_query);
$fares = [];

while ($row = $fares_result->fetch_assoc()) {
    $fares[] = $row;
}

// Create fare matrix for visualization
$fare_matrix = [];
foreach ($stations as $station_name => $station) {
    $fare_matrix[$station_name] = [];
}

foreach ($fares as $fare) {
    $fare_matrix[$fare['start_point']][$fare['end_point']] = [
        'fare' => $fare['fare'],
        'from_active' => $fare['from_active'],
        'to_active' => $fare['to_active']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fare Management - Metro Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-light: #ffffff;
            --bg-dark: #2c2c2c;
            --text-light: #000000;
            --text-dark: #e0e0e0;
            --hover-light: #007bff;
            --hover-dark: #007bff;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 25px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .table th, .table td {
            vertical-align: middle;
        }
        
        .fare-value {
            font-weight: bold;
            font-size: 1.1em;
            color: #1a73e8;
        }
        
        .fare-input {
            width: 80px;
            display: inline-block;
        }
        
        .alert {
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Station statuses */
        .station-inactive {
            color: #dc3545;
            text-decoration: line-through;
        }
        
        /* Fare matrix table styles */
        .fare-matrix-container {
            overflow-x: auto;
            max-height: 600px;
            overflow-y: auto;
        }
        
        .fare-matrix {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .fare-matrix th {
            position: sticky;
            background-color: #f8f9fa;
        }
        
        .fare-matrix th:first-child {
            left: 0;
            z-index: 2;
        }
        
        .fare-matrix th {
            top: 0;
            z-index: 1;
        }
        
        .fare-matrix td:first-child {
            position: sticky;
            left: 0;
            background-color: #f8f9fa;
            font-weight: bold;
            z-index: 1;
        }
        
        .fare-matrix td {
            min-width: 80px;
            height: 50px;
            text-align: center;
        }
        
        .fare-matrix td.editable {
            background-color: rgba(26, 115, 232, 0.05);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .fare-matrix td.editable:hover {
            background-color: rgba(26, 115, 232, 0.1);
        }
        
        .fare-matrix td.same-station {
            background-color: #f8f9fa;
        }
        
        .fare-matrix td.inactive-route {
            background-color: rgba(220, 53, 69, 0.05);
            color: #868e96;
        }
        
        /* Search box styling */
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-box i {
            position: absolute;
            left: 10px;
            top: 10px;
            color: #6c757d;
        }
        
        .search-input {
            padding-left: 35px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    
    <div class="container-fluid my-4">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i> <?= $_SESSION['success_message'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= $_SESSION['error_message'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <h1 class="mb-4 text-center">Metro Fare Management</h1>
        
        <div class="row">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-subway mr-2"></i>Administration</h5>
                    </div>
                    <div class="card-body">
                        <!-- Add this back button -->
                        <a href="admin_dashboard.php" class="btn btn-outline-secondary mb-3 w-100">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Admin Dashboard
                        </a>
                        
                        <div class="list-group">
                            <a href="metro_admin.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-map-marked-alt mr-2"></i>Station Management
                            </a>
                            <a href="metro_fares.php" class="list-group-item list-group-item-action active">
                                <i class="fas fa-dollar-sign mr-2"></i>Fare Management
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-edit mr-2"></i>Edit Individual Fare</h5>
                    </div>
                    <div class="card-body">
                        <form action="metro_fares.php" method="POST">
                            <div class="form-group">
                                <label for="from_station">From Station</label>
                                <select class="form-control" id="from_station" name="from_station" required>
                                    <option value="">Select departure station</option>
                                    <?php foreach ($stations as $station_name => $station): ?>
                                        <option value="<?= $station_name ?>"><?= htmlspecialchars($station_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="to_station">To Station</label>
                                <select class="form-control" id="to_station" name="to_station" required>
                                    <option value="">Select arrival station</option>
                                    <?php foreach ($stations as $station_name => $station): ?>
                                        <option value="<?= $station_name ?>"><?= htmlspecialchars($station_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="fare">Fare Amount (BDT)</label>
                                <input type="number" class="form-control" id="fare" name="fare" min="10" required>
                            </div>
                            
                            <button type="submit" name="update_fare" class="btn btn-success btn-block">
                                <i class="fas fa-save mr-2"></i>Update Fare
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Information</h5>
                    </div>
                    <div class="card-body">
                        <p>The fare matrix shows all station-to-station fares in the system.</p>
                        <ul>
                            <li>Click on any fare to edit it directly</li>
                            <li>Fares to/from inactive stations are dimmed</li>
                            <li>Use the search box to filter by station name</li>
                        </ul>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <small>Updating fares will immediately affect ticket prices for passengers.</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-table mr-2"></i>Fare Matrix</h5>
                    </div>
                    <div class="card-body">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchFares" class="form-control search-input" placeholder="Search for station...">
                        </div>
                        
                        <div class="fare-matrix-container">
                            <table class="table table-bordered fare-matrix" id="fareMatrix">
                                <thead>
                                    <tr>
                                        <th>From \ To</th>
                                        <?php foreach ($stations as $station_name => $station): ?>
                                            <th class="<?= $station['active'] == 0 ? 'station-inactive' : '' ?>">
                                                <?= htmlspecialchars($station_name) ?>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stations as $from_name => $from_station): ?>
                                        <tr>
                                            <td class="<?= $from_station['active'] == 0 ? 'station-inactive' : '' ?>">
                                                <?= htmlspecialchars($from_name) ?>
                                            </td>
                                            
                                            <?php foreach ($stations as $to_name => $to_station): ?>
                                                <?php
                                                $cell_class = '';
                                                $fare = '-';
                                                
                                                if ($from_name == $to_name) {
                                                    $cell_class = 'same-station';
                                                } elseif ($from_station['active'] == 0 || $to_station['active'] == 0) {
                                                    $cell_class = 'inactive-route';
                                                } else {
                                                    $cell_class = 'editable';
                                                    // Find fare for this route
                                                    if (isset($fare_matrix[$from_name][$to_name])) {
                                                        $fare = $fare_matrix[$from_name][$to_name]['fare'];
                                                    }
                                                }
                                                ?>
                                                
                                                <td class="<?= $cell_class ?>" 
                                                    <?php if ($cell_class === 'editable'): ?>
                                                        data-from="<?= $from_name ?>" 
                                                        data-to="<?= $to_name ?>"
                                                        data-fare="<?= $fare ?>"
                                                    <?php endif; ?>>
                                                    <?= $fare ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Fare Listing</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="fareList">
                                <thead class="thead-light">
                                    <tr>
                                        <th>From Station</th>
                                        <th>To Station</th>
                                        <th>Fare (BDT)</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fares as $fare): ?>
                                        <tr>
                                            <td class="<?= $fare['from_active'] == 0 ? 'station-inactive' : '' ?>">
                                                <?= htmlspecialchars($fare['start_point']) ?>
                                            </td>
                                            <td class="<?= $fare['to_active'] == 0 ? 'station-inactive' : '' ?>">
                                                <?= htmlspecialchars($fare['end_point']) ?>
                                            </td>
                                            <td class="fare-value"><?= $fare['fare'] ?></td>
                                            <td>
                                                <?php if ($fare['from_active'] == 1 && $fare['to_active'] == 1): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary edit-fare-btn" 
                                                        data-from="<?= $fare['start_point'] ?>" 
                                                        data-to="<?= $fare['end_point'] ?>"
                                                        data-fare="<?= $fare['fare'] ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Edit Modal -->
    <div class="modal fade" id="editFareModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Fare</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="metro_fares.php" method="POST" id="quickEditForm">
                        <input type="hidden" id="modalFromStation" name="from_station">
                        <input type="hidden" id="modalToStation" name="to_station">
                        
                        <div class="form-group">
                            <label>From Station</label>
                            <p class="form-control-static" id="modalFromName"></p>
                        </div>
                        
                        <div class="form-group">
                            <label>To Station</label>
                            <p class="form-control-static" id="modalToName"></p>
                        </div>
                        
                        <div class="form-group">
                            <label for="modalFare">Fare Amount (BDT)</label>
                            <input type="number" class="form-control" id="modalFare" name="fare" min="10" required>
                        </div>
                        
                        <button type="submit" name="update_fare" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Update Fare
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Quick edit from fare matrix
            $('.editable').click(function() {
                var fromName = $(this).data('from');
                var toName = $(this).data('to');
                var fare = $(this).data('fare');
                
                $('#modalFromStation').val(fromName);
                $('#modalToStation').val(toName);
                $('#modalFromName').text(fromName);
                $('#modalToName').text(toName);
                $('#modalFare').val(fare !== '-' ? fare : '');
                
                $('#editFareModal').modal('show');
            });
            
            // Quick edit from fare list
            $('.edit-fare-btn').click(function() {
                var fromName = $(this).data('from');
                var toName = $(this).data('to');
                var fare = $(this).data('fare');
                
                $('#modalFromStation').val(fromName);
                $('#modalToStation').val(toName);
                $('#modalFromName').text(fromName);
                $('#modalToName').text(toName);
                $('#modalFare').val(fare);
                
                $('#editFareModal').modal('show');
            });
            
            // Search functionality
            $('#searchFares').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                
                // Search in fare matrix
                $('#fareMatrix tbody tr').filter(function() {
                    var stationName = $(this).find('td:first').text().toLowerCase();
                    $(this).toggle(stationName.indexOf(value) > -1);
                });
                
                // Search in fare list
                $('#fareList tbody tr').filter(function() {
                    var rowText = $(this).text().toLowerCase();
                    $(this).toggle(rowText.indexOf(value) > -1);
                });
            });
            
            // Form validation for fare edits
            $('form').submit(function() {
                var fare = parseInt($(this).find('input[name="fare"]').val());
                if (fare < 10) {
                    alert('Fare must be at least 10 BDT');
                    return false;
                }
                return true;
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>