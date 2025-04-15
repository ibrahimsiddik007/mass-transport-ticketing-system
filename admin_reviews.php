<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle delete review request
if (isset($_GET['delete'])) {
    $review_id = $_GET['delete'];
    $delete_query = "DELETE FROM reviews WHERE id = ?";
    $stmt = $conn1->prepare($delete_query);
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
    header('Location: admin_reviews.php');
    exit;
}

// Handle update review order request
if (isset($_POST['update_order'])) {
    $order = explode(",", $_POST['order']);
    foreach ($order as $position => $id) {
        $update_query = "UPDATE reviews SET display_order = ? WHERE id = ?";
        $stmt = $conn1->prepare($update_query);
        $stmt->bind_param("ii", $position, $id);
        $stmt->execute();
    }
    header('Location: admin_reviews.php');
    exit;
}

// Fetch reviews with user information
$query = "
    SELECT reviews.id, reviews.user_id, reviews.rating, reviews.comment, reviews.created_at, reviews.display_order, users.name 
    FROM reviews 
    JOIN users ON reviews.user_id = users.id 
    ORDER BY reviews.display_order ASC, reviews.created_at DESC
";
$result = $conn1->query($query);

if (!$result) {
    die("Query failed: " . $conn1->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Management - Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
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
            --dark-bg: #121212;
            --dark-card-bg: #1e1e1e;
            --dark-text: #ffffff;
            --dark-border: #333;
            --table-header-bg: #f1f3f5;
            --table-row-hover: #f8f9fa;
            --sortable-placeholder: #f0f0f0;
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .dashboard-container {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            padding: 2rem;
            position: relative;
            animation: slideIn 0.6s ease-out forwards;
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

        .dashboard-container h3 {
            margin-bottom: 2rem;
            color: var(--text-color);
            font-weight: 600;
            text-align: center;
            position: relative;
        }

        .dashboard-container h3::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .back-link, .logout-link {
            position: absolute;
            top: 1rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all var(--transition-speed);
            z-index: 10;
        }

        .back-link {
            left: 1rem;
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        .logout-link {
            right: 1rem;
            background: linear-gradient(135deg, var(--accent-color), #d93025);
            color: white;
        }

        .back-link:hover, .logout-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .review-table {
            margin-top: 2rem;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.8s ease-out 0.4s backwards;
        }

        .review-table thead th {
            background: var(--table-header-bg);
            border-bottom: 2px solid var(--primary-color);
            color: var(--text-color);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
        }

        .review-table tbody tr {
            transition: all var(--transition-speed);
        }

        .review-table tbody tr:hover {
            background-color: var(--table-row-hover);
            transform: translateX(5px);
        }

        .review-table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }

        .order-column {
            cursor: move;
            text-align: center;
            width: 50px;
        }

        .order-column i {
            color: var(--primary-color);
            font-size: 1.2rem;
            transition: all var(--transition-speed);
        }

        .order-column:hover i {
            transform: scale(1.2);
            color: var(--secondary-color);
        }

        .sortable-placeholder {
            height: 60px;
            background: var(--sortable-placeholder);
            border: 2px dashed var(--primary-color);
            border-radius: 8px;
            margin: 0.5rem 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #357abd);
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all var(--transition-speed);
            margin-top: 1rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--accent-color), #d93025);
            border: none;
            padding: 0.5rem 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all var(--transition-speed);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 107, 107, 0.3);
        }

        /* Dark Mode Support */
        body.dark-mode {
            background-color: var(--dark-bg);
            color: var(--dark-text);
        }

        body.dark-mode .dashboard-container {
            background-color: var(--dark-card-bg);
        }

        body.dark-mode .review-table thead th {
            background-color: #2d2d2d;
            color: var(--dark-text);
        }

        body.dark-mode .review-table tbody tr:hover {
            background-color: #333;
        }

        body.dark-mode .review-table td {
            border-bottom-color: var(--dark-border);
        }

        body.dark-mode .sortable-placeholder {
            background-color: #2d2d2d;
            border-color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .dashboard-container {
                padding: 1rem;
            }
            
            .review-table {
                display: block;
                overflow-x: auto;
            }
            
            .back-link, .logout-link {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-container">
            <a href="admin_dashboard.php" class="btn btn-primary back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="admin_dashboard.php?logout=true" class="btn btn-danger logout-link">Logout</a>
            <h3 class="text-center">Review Management</h3>
            <form method="POST" action="admin_reviews.php">
                <table class="table table-bordered review-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sortable">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                <td class="order-column">
                                    <i class="fas fa-arrows-alt"></i>
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['rating']); ?></td>
                                <td><?php echo htmlspecialchars($row['comment']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td>
                                    <a href="admin_reviews.php?delete=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <input type="hidden" name="order" id="order">
                <button type="submit" name="update_order" class="btn btn-primary">Update Order</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(function() {
            $("#sortable").sortable({
                placeholder: "sortable-placeholder",
                handle: ".order-column",
                update: function(event, ui) {
                    var order = $(this).sortable("toArray", { attribute: "data-id" });
                    $("#order").val(order.join(","));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
</body>
</html>