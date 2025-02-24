<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname1 = "mass transport ticketing system";
$dbname2 = "train_ticketing_system";
$dbname3 = "bus_ticketing_system";

$conn1 = new mysqli($servername, $username, $password, $dbname1);
$conn2 = new mysqli($servername, $username, $password, $dbname2);
$conn3 = new mysqli($servername, $username, $password, $dbname3);

if ($conn1->connect_error) {
    die("Connection to mass transport ticketing system failed: " . $conn1->connect_error);
}

if ($conn2->connect_error) {
    die("Connection to train transport system failed: " . $conn2->connect_error);
}

if ($conn3->connect_error) {
    die("Connection to bus transport system failed: " . $conn3->connect_error);
}

mysqli_select_db($conn1, $dbname1);
mysqli_select_db($conn2, $dbname2);
mysqli_select_db($conn3, $dbname3);

?>