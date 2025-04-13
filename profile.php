<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn1->prepare("SELECT name, email, profile_image, address, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $profile_image, $address, $phone_number);
$stmt->fetch();
$stmt->close();

$needs_completion = empty($address) || empty($phone_number);

// Fetch balances for all payment methods
$balances = [];
$stmt = $conn1->prepare("SELECT account_type, balance FROM demo_accounts");
$stmt->execute();
$stmt->bind_result($accountType, $balance);
while ($stmt->fetch()) {
    $balances[] = [
        'account_type' => $accountType,
        'balance' => $balance
    ];
}
$stmt->close();

// Fetch user receipts for metro transactions
$receipts = [];
$stmt = $conn1->prepare("SELECT transaction_id, start_location, end_location, fare, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($transactionId, $startLocation, $endLocation, $fare, $createdAt);
while ($stmt->fetch()) {
    $receipts[] = [
        'transaction_id' => $transactionId,
        'start_location' => $startLocation,
        'end_location' => $endLocation,
        'fare' => $fare,
        'created_at' => $createdAt,
        'type' => 'Metro'
    ];
}
$stmt->close();

// Fetch user receipts for train transactions
$stmt = $conn2->prepare("
    SELECT tt.transaction_id, t.start_point, t.end_point, t.fare, tt.payment_time 
    FROM train_transactions tt 
    JOIN trains t ON tt.train_id = t.train_id 
    WHERE tt.user_id = ? 
    ORDER BY tt.payment_time DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($transactionId, $startPoint, $endPoint, $fare, $createdAt);
while ($stmt->fetch()) {
    $receipts[] = [
        'transaction_id' => $transactionId,
        'start_location' => $startPoint,
        'end_location' => $endPoint,
        'fare' => $fare,
        'created_at' => $createdAt,
        'type' => 'Train'
    ];
}
$stmt->close();

// Fetch user receipts for bus transactions
$stmt = $conn3->prepare("
    SELECT transaction_id,origin,destination,amount,payment_time 
    FROM bus_transactions 
    WHERE user_id = ?
    ORDER BY payment_time DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($transactionId, $origin, $destination, $amount, $paymentTime);
while ($stmt->fetch()) {
    $receipts[] = [
        'transaction_id' => $transactionId,
        'start_location' => $origin,
        'end_location' => $destination,
        'fare' => $amount,
        'created_at' => $paymentTime,
        'type' => 'Local Bus'
    ];
}
$stmt->close();

// Sort receipts by created_at in descending order
usort($receipts, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            /* Light theme variables */
            --primary-color: #4a00e0;
            --secondary-color: #8e2de2;
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(0, 0, 0, 0.1);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --text-primary: #2c3e50;
            --text-secondary: #34495e;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #c0392b;
            --card-bg: rgba(255, 255, 255, 0.95);
            --hover-color: rgba(0, 0, 0, 0.05);
            --btn-text: #ffffff;
            --btn-bg: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
            color: var(--text-primary);
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/profile_bg.jpg') center/cover no-repeat;
            opacity: 0.05;
            z-index: -1;
        }

        .container {
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .profile-card, .balance-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--glass-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .profile-card::before, .balance-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(74, 0, 224, 0.03) 0%, rgba(142, 45, 226, 0.03) 100%);
            z-index: -1;
        }

        .profile-card:hover, .balance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--glass-border);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
        }

        .profile-image:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-title {
            color: var(--text-primary);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .card-text {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .btn-toggle {
            background: var(--btn-bg);
            color: var(--btn-text);
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-size: 1.1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-toggle::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .btn-toggle:hover::before {
            left: 100%;
        }

        .btn-toggle:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .receipt-table {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--glass-shadow);
            display: none;
            margin-top: 20px;
            animation: fadeIn 0.5s ease-out;
        }

        .receipt-table thead {
            background: var(--btn-bg);
            color: var(--btn-text);
        }

        .receipt-table th {
            font-weight: 600;
            padding: 15px;
            text-align: center;
            color: var(--btn-text);
        }

        .receipt-table td {
            padding: 12px;
            text-align: center;
            vertical-align: middle;
            color: var(--text-primary);
            font-weight: 500;
        }

        .receipt-table tbody tr {
            transition: background-color 0.3s ease;
            background-color: var(--card-bg);
        }

        .receipt-table tbody tr:hover {
            background-color: var(--hover-color);
        }

        .receipt-table .btn-primary {
            background: var(--btn-bg);
            color: var(--btn-text);
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .receipt-table .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-light {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 25px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .btn-light:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .alert-warning {
            background: rgba(243, 156, 18, 0.1);
            border: 1px solid var(--warning-color);
            color: var(--warning-color);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(20px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        /* Dark theme styles */
        body.dark-mode {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --glass-bg: rgba(0, 0, 0, 0.3);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            --text-primary: #ecf0f1;
            --text-secondary: #bdc3c7;
            --card-bg: rgba(44, 62, 80, 0.9);
            --hover-color: rgba(255, 255, 255, 0.05);
            --btn-text: #ffffff;
            --btn-bg: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        body.dark-mode {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c3e50 100%);
        }

        body.dark-mode::before {
            opacity: 0.1;
        }

        .dark-mode .profile-card::before,
        .dark-mode .balance-card::before {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(41, 128, 185, 0.1) 100%);
        }

        .dark-mode .btn-light {
            background: transparent;
            color: var(--text-primary);
            border: 2px solid var(--text-primary);
        }

        .dark-mode .btn-light:hover {
            background: var(--text-primary);
            color: var(--primary-color);
        }

        .dark-mode .profile-image {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .balance-card h4 {
            color: var(--text-primary);
        }

        .balance-card p {
            color: var(--text-secondary);
        }

        .balance-card i {
            color: var(--text-primary);
        }

        /* Dark mode specific adjustments */
        .dark-mode .receipt-table {
            background: var(--card-bg);
        }

        .dark-mode .receipt-table td {
            color: var(--text-primary);
        }

        .dark-mode .receipt-table tbody tr {
            background-color: var(--card-bg);
        }

        .dark-mode .receipt-table tbody tr:hover {
            background-color: var(--hover-color);
        }

        .dark-mode .receipt-table .btn-primary {
            background: var(--btn-bg);
            color: var(--btn-text);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .profile-card, .balance-card {
                padding: 20px;
            }

            .profile-image {
                width: 120px;
                height: 120px;
            }

            .card-title {
                font-size: 1.5rem;
            }

            .card-text {
                font-size: 1rem;
            }
        }
    </style>
    <script>
        function toggleReceipts() {
            var receiptTable = document.getElementById('receipt-table');
            if (receiptTable.style.display === 'none') {
                receiptTable.style.display = 'table';
            } else {
                receiptTable.style.display = 'none';
            }
        }

        function toggleEditForm() {
            var profileInfo = document.getElementById('profile-info');
            var editForm = document.getElementById('edit-form');
            if (editForm.style.display === 'none') {
                editForm.style.display = 'block';
                profileInfo.style.display = 'none';
            } else {
                editForm.style.display = 'none';
                profileInfo.style.display = 'block';
            }
        }
    </script>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h2 class="text-center mt-5">Profile</h2>
        <?php if ($needs_completion): ?>
            <div class="alert alert-warning text-center">
                Please complete your profile information.
            </div>
        <?php endif; ?>
        <div class="row">
            <!-- Profile Details Card -->
            <div class="col-md-6">
                <div class="card mb-4 profile-card">
                    <div class="card-body text-center" id="profile-info">
                        <?php if ($profile_image): ?>
                            <img src="<?php echo $profile_image; ?>" class="profile-image" alt="Profile Image">
                        <?php else: ?>
                            <img src="images/default_profile_account_photo.jpg" class="profile-image" alt="Default Profile Image">
                        <?php endif; ?>
                        <h4><?php echo htmlspecialchars($username); ?></h4>
                        <p>Email: <?php echo htmlspecialchars($email); ?></p>
                        <p>Address: <?php echo htmlspecialchars($address); ?></p>
                        <p>Phone: <?php echo htmlspecialchars($phone_number); ?></p>
                        <button class="btn btn-light" onclick="toggleEditForm()"><i class="fas fa-edit"></i> Edit</button>
                    </div>
                    <div class="card-body edit-form" id="edit-form" style="display: none;">
                        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="profile_image">Profile Image</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image">

                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($username); ?>" required>

                                <label for="address">Address</label>
                                <input type="text" class="form-control" name="address" id="address" value="<?php echo htmlspecialchars($address); ?>" required>

                                <label for="phone_number">Phone Number</label>
                                <input type="text" class="form-control" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-light btn-block"><i class="fas fa-save"></i> Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Combined Balance Card -->
            <div class="col-md-6">
                <div class="card mb-4 balance-card">
                    <div class="card-body text-center">
                        <h4>Demo Account Balances (For All Users)</h4>
                        <?php foreach ($balances as $balance): ?>
                            <p>
                                <i class="<?php echo $balance['account_type'] === 'bKash' ? 'fas fa-mobile-alt' : ($balance['account_type'] === 'Rocket' ? 'fas fa-wallet' : 'fas fa-credit-card'); ?>"></i>
                                <strong><?php echo htmlspecialchars($balance['account_type']); ?>:</strong>
                                <?php echo number_format($balance['balance'], 2); ?> BDT
                            </p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-12 text-center">
                <button class="btn btn-toggle btn-block mx-auto" style="max-width: 300px;" onclick="toggleReceipts()">
                    <i class="fas fa-receipt"></i> Show Receipts
                </button>
            </div>
            <div class="col-md-6">
                <div class="receipt-section">
                    <table class="table table-striped receipt-table" id="receipt-table">
                        <thead class="thead-dark">
                            <tr>
                                <th>Transaction ID</th>
                                <th>Start Location</th>
                                <th>End Location</th>
                                <th>Fare (BDT)</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receipts as $receipt): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($receipt['transaction_id']); ?></td>
                                    <td><?php echo htmlspecialchars($receipt['start_location']); ?></td>
                                    <td><?php echo htmlspecialchars($receipt['end_location']); ?></td>
                                    <td><?php echo htmlspecialchars($receipt['fare']); ?></td>
                                    <td><?php echo htmlspecialchars($receipt['created_at']); ?></td>
                                    <td><?php echo htmlspecialchars($receipt['type']); ?></td>
                                    <td>
                                        <?php if ($receipt['type'] == 'Metro'): ?>
                                            <a href="metro_generate_receipt.php?transaction_id=<?php echo htmlspecialchars($receipt['transaction_id']); ?>" download class="btn btn-primary"><i class="fas fa-download"></i> Download</a>
                                        <?php elseif ($receipt['type'] == 'Train'): ?>
                                            <a href="train_generate_receipt.php?transaction_id=<?php echo htmlspecialchars($receipt['transaction_id']); ?>" download class="btn btn-primary"><i class="fas fa-download"></i> Download</a>
                                        <?php elseif ($receipt['type'] == 'Local Bus'): ?>
                                            <a href="bus_download_receipt.php?transaction_id=<?php echo htmlspecialchars($receipt['transaction_id']); ?>" download class="btn btn-primary"><i class="fas fa-download"></i> Download</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>