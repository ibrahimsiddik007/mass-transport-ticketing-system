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
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Arial', sans-serif;
        }

        .profile-card {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .profile-card:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .profile-image {
            border: 5px solid #fff;
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin-bottom: 15px;
            transition: transform 0.3s ease-in-out;
        }

        .profile-image:hover {
            transform: scale(1.1);
        }

        .btn-toggle {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: #fff;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            transition: background 0.3s ease-in-out, transform 0.3s ease-in-out;
        }

        .btn-toggle:hover {
            background: linear-gradient(135deg, #ff4b2b 0%, #ff416c 100%);
            transform: scale(1.05);
        }

        .receipt-table {
            display: none;
            margin-top: 20px;
            animation: fadeIn 0.5s ease-in-out;
            border-radius: 15px; /* Add rounded corners */
            overflow: hidden; /* Ensure the rounded corners apply to the table content */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add a subtle shadow */
        }

        .receipt-table thead {
            border-radius: 15px 15px 0 0; /* Rounded corners for the table header */
            background-color: #6a11cb; /* Header background color */
            color: #fff; /* Header text color */
        }

        .receipt-table tbody tr {
            background-color: #f8f9fa; /* Light background for rows */
            transition: background-color 0.3s ease-in-out;
        }

        .receipt-table tbody tr:hover {
            background-color: #e9ecef; /* Slightly darker background on hover */
        }

        .receipt-section {
            margin-top: 30px;
        }

        .btn-light {
            background: #fff;
            color: #6a11cb;
            border: 2px solid #6a11cb;
            border-radius: 25px;
            transition: background 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        .btn-light:hover {
            background: #6a11cb;
            color: #fff;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Dark mode styles */
        body.dark-mode {
            background: linear-gradient(135deg, #121212 0%, #1f1f1f 100%);
            color: #ffffff;
        }

        .dark-mode .profile-card {
            background: linear-gradient(135deg, #333333 0%, #444444 100%);
            color: #ffffff;
        }

        .dark-mode .btn-toggle {
            background: linear-gradient(135deg, #ff4b2b 0%, #ff416c 100%);
            color: #ffffff;
        }

        .dark-mode .receipt-table {
            background-color: #1f1f1f;
            color: #ffffff;
        }

        .dark-mode .receipt-table thead {
            background-color: #333333;
        }

        .dark-mode .receipt-table tbody tr {
            background-color: #1f1f1f;
        }

        .dark-mode .receipt-table tbody tr:hover {
            background-color: #333333;
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