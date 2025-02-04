<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="receipt">
            <h2>Payment Receipt</h2>
            <p><strong>Start Location:</strong> <?php echo htmlspecialchars($_GET['startLocation']); ?></p>
            <p><strong>End Location:</strong> <?php echo htmlspecialchars($_GET['endLocation']); ?></p>
            <p><strong>Fare:</strong> <?php echo htmlspecialchars($_GET['fare']); ?> BDT</p>
            <p><strong>Created At:</strong> <?php echo htmlspecialchars($_GET['created_at']); ?></p>
            <div class="qr-code">
                <?php
                include 'phpqrcode/qrlib.php';
                $qrData = 'Start Location: ' . $_GET['startLocation'] . "\n" .
                          'End Location: ' . $_GET['endLocation'] . "\n" .
                          'Fare: ' . $_GET['fare'] . ' BDT' . "\n" .
                          'Created At: ' . $_GET['created_at'];
                $qrFileName = 'qrcodes/' . uniqid() . '.png';

                // Check if the directory exists and create it if it doesn't
                if (!is_dir('qrcodes')) {
                    mkdir('qrcodes', 0777, true);
                }

                // Generate the QR code
                QRcode::png($qrData, $qrFileName);
                ?>
                <img src="<?php echo $qrFileName; ?>" alt="QR Code">
            </div>
            <a href="metro.php" class="btn btn-primary">Back to Home</a>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>