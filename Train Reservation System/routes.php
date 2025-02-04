<?php
include 'db/db_connection.php';
include 'nav.php';

$sql = "SELECT * FROM routes";
$result = $conn->query($sql);
?>

<div class="container">
    <h2>Select a Route</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Origin</th>
                <th>Destination</th>
                <th>Departure Time</th>
                <th>Arrival Time</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['origin']; ?></td>
                    <td><?php echo $row['destination']; ?></td>
                    <td><?php echo $row['departure_time']; ?></td>
                    <td><?php echo $row['arrival_time']; ?></td>
                    <td><a href="train_info.php?route_id=<?php echo $row['id']; ?>" class="btn btn-primary">Select</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>