<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// Fetch contact information from the database
$query = "SELECT name, email, phone, message, created_at FROM contacts WHERE name LIKE ? OR email LIKE ?";
$stmt = $conn1->prepare($query);
$search_param = '%' . $search . '%';
$stmt->bind_param("ss", $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query failed: " . $conn1->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
        }
        .logout-link {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .search-form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-container">
            <a href="admin_dashboard.php?logout=true" class="btn btn-danger logout-link">Logout</a>
            <h3 class="text-center">Contact Messages</h3>
            <form class="search-form" method="GET" action="admin_contacts.php">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </div>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>