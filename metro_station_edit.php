<?php
session_start();
include 'db.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Check if station ID is provided
if (!isset($_GET['id']) && !isset($_POST['station_id'])) {
    $_SESSION['error_message'] = "No station specified";
    header('Location: metro_admin.php');
    exit;
}

$station_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['station_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $name = trim($_POST['name']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    // Validate inputs
    if (empty($name)) {
        $_SESSION['error_message'] = "Station name cannot be empty";
        header("Location: metro_station_edit.php?id=$station_id");
        exit;
    }
    
    // Check if another station with the same name exists
    $check_query = "SELECT id FROM metro_stations WHERE s_name = ? AND id != ?";
    $check_stmt = $conn1->prepare($check_query);
    $check_stmt->bind_param("si", $name, $station_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Another station with this name already exists";
        header("Location: metro_station_edit.php?id=$station_id");
        exit;
    }
    
    // Update station
    $update_query = "UPDATE metro_stations SET s_name = ?, active = ? WHERE id = ?";
    $update_stmt = $conn1->prepare($update_query);
    $update_stmt->bind_param("sii", $name, $active, $station_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Station updated successfully";
        header('Location: metro_admin.php');
        exit;
    } else {
        $_SESSION['error_message'] = "Failed to update station: " . $conn->error;
        header("Location: metro_station_edit.php?id=$station_id");
        exit;
    }
} else {
    // Load existing station data
    $query = "SELECT * FROM metro_stations WHERE id = ?";
    $stmt = $conn1->prepare($query);
    $stmt->bind_param("i", $station_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error_message'] = "Station not found";
        header('Location: metro_admin.php');
        exit;
    }
    
    $station = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Station - Metro Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            margin-bottom: 25px;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .btn-primary {
            background-color: #1a73e8;
            border-color: #1a73e8;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #1557b0;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(26, 115, 232, 0.3);
        }
        
        .alert {
            border-radius: 8px;
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    
    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?= $_SESSION['error_message'] ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-edit mr-2"></i>Edit Station</h5>
                    </div>
                    <div class="card-body">
                        <form action="metro_station_edit.php" method="POST">
                            <input type="hidden" name="station_id" value="<?= $station['id'] ?>">
                            
                            <div class="form-group">
                                <label for="name">Station Name</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-subway"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($station['s_name']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" <?= $station['active'] == 1 ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="active">Station Active</label>
                                </div>
                                <small class="form-text text-muted">Inactive stations won't be available for ticket booking</small>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Save Changes
                                </button>
                                <a href="metro_admin.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Station Information</h5>
                    </div>
                    <div class="card-body">
                        <p>
                            <strong>ID:</strong> <?= $station['id'] ?><br>
                            <strong>Status:</strong>
                            <span class="badge badge-<?= $station['active'] == 1 ? 'success' : 'danger' ?>">
                                <?= $station['active'] == 1 ? 'Active' : 'Inactive' ?>
                            </span>
                        </p>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Note:</strong> Changing a station's status to inactive will prevent users from selecting it for ticket booking.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    </script>
</body>
</html>