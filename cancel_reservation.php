<?php
include 'db.php';

// This will mark reservations as cancelled if older than 15 minutes and still 'pending'.
$sql = "UPDATE reservations
        SET status = 'cancelled'
        WHERE status = 'pending'
          AND TIMESTAMPDIFF(MINUTE, reservation_time, NOW()) >= 15";
$conn2->query($sql);
?>