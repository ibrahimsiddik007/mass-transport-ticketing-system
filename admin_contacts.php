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

        .search-form {
            margin-bottom: 2rem;
            animation: fadeIn 0.8s ease-out 0.2s backwards;
        }

        .input-group {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .form-control {
            border: none;
            padding: 1rem;
            font-size: 1rem;
            border-radius: 8px 0 0 8px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary-color);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #357abd);
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all var(--transition-speed);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
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

        .table {
            margin-top: 2rem;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.8s ease-out 0.4s backwards;
        }

        .table thead th {
            background: var(--table-header-bg);
            border-bottom: 2px solid var(--primary-color);
            color: var(--text-color);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
        }

        .table tbody tr {
            transition: all var(--transition-speed);
        }

        .table tbody tr:hover {
            background-color: var(--table-row-hover);
            transform: translateX(5px);
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }

        /* Dark Mode Support */
        body.dark-mode {
            background-color: var(--dark-bg);
            color: var(--dark-text);
        }

        body.dark-mode .dashboard-container {
            background-color: var(--dark-card-bg);
        }

        body.dark-mode .table thead th {
            background-color: #2d2d2d;
            color: var(--dark-text);
        }

        body.dark-mode .table tbody tr:hover {
            background-color: #333;
        }

        body.dark-mode .table td {
            border-bottom-color: var(--dark-border);
        }

        body.dark-mode .form-control {
            background-color: #333;
            color: var(--dark-text);
        }

        body.dark-mode .form-control:focus {
            background-color: #333;
            color: var(--dark-text);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .dashboard-container {
                padding: 1rem;
            }
            
            .table {
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