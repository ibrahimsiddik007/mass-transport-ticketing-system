<?php
session_start();
include 'db.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Check if we need to add 'active' column to stations table
$check_active_column = "SHOW COLUMNS FROM metro_stations LIKE 'active'";
$active_column_exists = $conn1->query($check_active_column);
if ($active_column_exists->num_rows == 0) {
    // Add active column if it doesn't exist
    $conn->query("ALTER TABLE stations ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1");
}

// Handle station status toggle
if (isset($_POST['toggle_station'])) {
    $station_id = (int)$_POST['station_id'];
    $current_status = $_POST['current_status'] === '1' ? 0 : 1;
    
    $query = "UPDATE metro_stations SET active = ? WHERE id = ?";
    $stmt = $conn1->prepare($query);
    $stmt->bind_param("ii", $current_status, $station_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Station status updated successfully";
    } else {
        $_SESSION['error_message'] = "Failed to update station status";
    }
    
    header('Location: metro_admin.php');
    exit;
}

// Get all metro stations
$query = "SELECT * FROM metro_stations ORDER BY id";
$result = $conn1->query($query);
$stations = [];
while ($row = $result->fetch_assoc()) {
    $stations[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metro Admin Dashboard</title>
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
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        /* Metro line visualization */
        .metro-line-container {
            position: relative;
            height: 200px;
            padding: 20px 0;
            margin: 30px 0;
            transition: height 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }
        
        .metro-line {
            position: absolute;
            top: 50%;
            left: 40px;
            right: 40px;
            height: 8px;
            background: linear-gradient(90deg, #1a73e8, #34a853);
            border-radius: 4px;
            transform: translateY(-50%);
            z-index: 1;
        }
        
        .metro-station {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
            z-index: 2;
        }
        
        .station-point {
            width: 24px;
            height: 24px;
            background: #fff;
            border: 4px solid #1a73e8;
            border-radius: 50%;
            margin-bottom: 10px;
            z-index: 2;
            transition: all 0.3s ease;
            box-shadow: 0 0 0 5px rgba(26, 115, 232, 0.2);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(26, 115, 232, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(26, 115, 232, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(26, 115, 232, 0);
            }
        }
        
        .station-name {
            transform: rotate(-45deg);
            white-space: nowrap;
            font-size: 12px;
            font-weight: bold;
            padding: 5px 10px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        /* Inactive station styling */
        .station-inactive .station-point {
            background: #f1f1f1;
            border-color: #ea4335;
            animation: none;
            box-shadow: 0 0 0 5px rgba(234, 67, 53, 0.2);
        }
        
        .station-inactive .station-name {
            color: #777;
            background: #f1f1f1;
            text-decoration: line-through;
        }
        
        /* List view for stations */
        .metro-line-container.list-view {
            height: auto;
            display: flex;
            flex-direction: column;
        }
        
        .metro-line-container.list-view .metro-line {
            display: none;
        }
        
        .metro-line-container.list-view .metro-station {
            position: relative;
            top: 0;
            left: 0 !important;
            transform: none;
            flex-direction: row;
            justify-content: flex-start;
            margin: 10px 0;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            animation: fadeInUp 0.5s forwards;
            opacity: 0;
        }
        
        .metro-line-container.list-view .station-point {
            margin: 0 15px 0 0;
        }
        
        .metro-line-container.list-view .station-name {
            transform: none;
        }
        
        /* Apply staggered animation to list items */
        .metro-line-container.list-view .metro-station:nth-child(2) { animation-delay: 0.1s; }
        .metro-line-container.list-view .metro-station:nth-child(3) { animation-delay: 0.15s; }
        .metro-line-container.list-view .metro-station:nth-child(4) { animation-delay: 0.2s; }
        .metro-line-container.list-view .metro-station:nth-child(5) { animation-delay: 0.25s; }
        
        /* Metro train animation */
        .metro-train {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            font-size: 28px;
            color: #fbbc05;
            z-index: 3;
            transition: left 0.5s ease;
            opacity: 0;
        }
        
        .train-moving {
            animation: trainMove 15s linear infinite;
            opacity: 1;
        }
        
        @keyframes trainMove {
            0% { left: 0; }
            100% { left: calc(100% - 28px); }
        }
        
        /* Status indicators */
        .status-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-active {
            background-color: #34a853;
            box-shadow: 0 0 5px #34a853;
        }
        
        .status-inactive {
            background-color: #ea4335;
            box-shadow: 0 0 5px #ea4335;
        }
        
        /* Animation keyframes */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Notification animation */
        .alert {
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    
    <div class="container my-4">
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
        
        <h1 class="mb-4 text-center">Metro Admin Dashboard</h1>
        
        <div class="row">
            <div class="col-lg-4">
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
                            <a href="metro_admin.php" class="list-group-item list-group-item-action active">
                                <i class="fas fa-map-marked-alt mr-2"></i>Station Management
                            </a>
                            <a href="metro_fares.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-dollar-sign mr-2"></i>Fare Management
                            </a>
                        </div>
                        
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">System Status</h6>
                            </div>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> System Online</p>
                                <p class="mb-2"><i class="fas fa-subway text-primary mr-2"></i> 
                                    <span class="font-weight-bold"><?= count($stations) ?></span> Stations
                                </p>
                                <p class="mb-0"><i class="fas fa-toggle-on text-success mr-2"></i> 
                                    <span class="font-weight-bold">
                                        <?php
                                        $active_count = 0;
                                        foreach ($stations as $station) {
                                            if ($station['active'] == 1) $active_count++;
                                        }
                                        echo $active_count;
                                        ?>
                                    </span> Active Stations
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle mr-2"></i>Add New Station</h5>
                    </div>
                    <div class="card-body">
                        <form action="metro_station_add.php" method="POST">
                            <div class="form-group">
                                <label for="station_name">Station Name</label>
                                <input type="text" class="form-control" id="station_name" name="name" required>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="station_active" name="active" value="1" checked>
                                <label class="form-check-label" for="station_active">Station Active</label>
                            </div>
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-plus-circle mr-2"></i>Add Station
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-train mr-2"></i>Metro Line Visualization</h5>
                        <div>
                            <button class="btn btn-light btn-sm" id="toggleAnimation">
                                <i class="fas fa-play"></i> Animate
                            </button>
                            <button class="btn btn-light btn-sm" id="toggleView">
                                <i class="fas fa-th-list"></i> Toggle View
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="metro-line-container" id="metroLineVisual">
                            <div class="metro-line"></div>
                            <?php foreach ($stations as $index => $station): ?>
                                <div class="metro-station <?= $station['active'] == 0 ? 'station-inactive' : '' ?>" 
                                    style="left: <?= ($index / max(1, count($stations)-1)) * 100 ?>%">
                                    <div class="station-point"></div>
                                    <div class="station-name"><?= htmlspecialchars($station['s_name']) ?></div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Animated train -->
                            <div class="metro-train">
                                <i class="fas fa-subway"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Station Management</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stations as $station): ?>
                                        <tr class="<?= $station['active'] == 0 ? 'table-danger' : '' ?>">
                                            <td><?= $station['id'] ?></td>
                                            <td><?= htmlspecialchars($station['s_name']) ?></td>
                                            <td>
                                                <span class="status-dot status-<?= $station['active'] == 1 ? 'active' : 'inactive' ?>"></span>
                                                <?= $station['active'] == 1 ? 'Active' : 'Inactive' ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="station_id" value="<?= $station['id'] ?>">
                                                        <input type="hidden" name="current_status" value="<?= $station['active'] ?>">
                                                        <button type="submit" name="toggle_station" class="btn btn-<?= $station['active'] == 1 ? 'warning' : 'success' ?>" data-toggle="tooltip" title="<?= $station['active'] == 1 ? 'Disable' : 'Enable' ?> Station">
                                                            <i class="fas fa-<?= $station['active'] == 1 ? 'power-off' : 'check' ?>"></i>
                                                        </button>
                                                    </form>
                                                    <a href="metro_station_edit.php?id=<?= $station['id'] ?>" class="btn btn-info" data-toggle="tooltip" title="Edit Station">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-danger delete-station" data-id="<?= $station['id'] ?>" data-name="<?= htmlspecialchars($station['s_name']) ?>" data-toggle="tooltip" title="Delete Station">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
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
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteStationModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete Station</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the station: <strong id="stationNameToDelete"></strong>?</p>
                    <p class="text-danger">This action cannot be undone. All fare mappings associated with this station will be deleted.</p>
                </div>
                <div class="modal-footer">
                    <form action="metro_station_delete.php" method="POST">
                        <input type="hidden" id="stationIdToDelete" name="station_id">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
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
            // Enable tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Delete station modal
            $('.delete-station').click(function() {
                var stationId = $(this).data('id');
                var stationName = $(this).data('name');
                
                $('#stationIdToDelete').val(stationId);
                $('#stationNameToDelete').text(stationName);
                $('#deleteStationModal').modal('show');
            });
            
            // Toggle metro train animation
            let animationRunning = false;
            let train = $('.metro-train');
            
            $('#toggleAnimation').click(function() {
                if (animationRunning) {
                    train.removeClass('train-moving');
                    $(this).html('<i class="fas fa-play"></i> Animate');
                } else {
                    train.addClass('train-moving');
                    $(this).html('<i class="fas fa-pause"></i> Pause');
                }
                animationRunning = !animationRunning;
            });
            
            // Toggle between visual and list view
            $('#toggleView').click(function() {
                $('#metroLineVisual').toggleClass('list-view');
                $(this).find('i').toggleClass('fa-th-list fa-map-marked-alt');
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>