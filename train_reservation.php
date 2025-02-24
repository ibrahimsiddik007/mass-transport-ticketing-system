<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['train']) || !isset($_POST['date']) || !isset($_POST['ticket_count'])) {
    echo 'error';
    exit;
}

$userId = $_SESSION['user_id'];
$trainId = $_POST['train'];
$date = $_POST['date'];
$tickets = (int)$_POST['ticket_count'];

// Redirect to reserve seats
header('Location: reserve_seats.php?trainId='.$trainId.'&date='.$date.'&tickets='.$tickets);
exit;
?>