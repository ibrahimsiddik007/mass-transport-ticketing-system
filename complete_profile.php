<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn1->prepare("UPDATE users SET address = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("ssi", $address, $phone_number, $user_id);
    $stmt->execute();
    $stmt->close();

    header('Location: profile.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="text-center mt-5">Complete Your Profile</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <form action="complete_profile.php" method="POST">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" name="address" id="address" required>

                                <label for="phone_number">Phone Number</label>
                                <input type="text" class="form-control" name="phone_number" id="phone_number" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>