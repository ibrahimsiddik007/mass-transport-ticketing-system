<?php
include 'db/db_connection.php';
include 'nav.php';

$train_id = $_GET['train_id'];
$seats = explode(',', $_GET['seats']);
?>

<div class="container">
    <h2>Payment</h2>
    <form action="process_payment.php" method="POST">
        <input type="hidden" name="train_id" value="<?php echo $train_id; ?>">
        <input type="hidden" name="seats" value="<?php echo implode(',', $seats); ?>">
        <div class="form-group">
            <label for="customer_name">Name:</label>
            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
        </div>
        <div class="form-group">
            <label for="customer_email">Email:</label>
            <input type="email" class="form-control" id="customer_email" name="customer_email" required>
        </div>
        <button type="submit" class="btn btn-primary">Pay Now</button>
    </form>
</div>