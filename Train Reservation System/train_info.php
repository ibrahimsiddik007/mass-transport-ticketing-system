<?php
include 'db/db_connection.php';
include 'nav.php';

$route_id = $_GET['route_id'];
$sql = "SELECT * FROM trains WHERE route_id = $route_id";
$result = $conn->query($sql);
?>

<div class="container">
    <h2>Select a Train</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Train Name</th>
                <th>Total Seats</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['train_name']; ?></td>
                    <td><?php echo $row['total_seats']; ?></td>
                    <td><a href="seat_selection.php?train_id=<?php echo $row['id']; ?>" class="btn btn-primary">Select</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>