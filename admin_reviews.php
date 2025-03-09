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
        .dashboard-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .dashboard-container h3 {
            margin-bottom: 20px;
            text-align: center;
            color: #007bff;
        }
        .logout-link {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .review-table th, .review-table td {
            vertical-align: middle;
        }
        .sortable-placeholder {
            height: 50px;
            background: #f0f0f0;
            border: 1px dashed #ccc;
        }
        .order-column {
            cursor: move;
        }
        .order-column i {
            cursor: move;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-container">
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