<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $train_id = $_POST['train_id'];

    // Delete seats associated with the train
    $stmt = $conn2->prepare("DELETE FROM seats WHERE train_id = ?");
    $stmt->bind_param("i", $train_id);
    $stmt->execute();

    // Delete compartments associated with the train
    $stmt = $conn2->prepare("DELETE FROM compartments WHERE train_id = ?");
    $stmt->bind_param("i", $train_id);
    $stmt->execute();

    // Delete the train
    $stmt = $conn2->prepare("DELETE FROM trains WHERE train_id = ?");
    $stmt->bind_param("i", $train_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['message' => 'Train and its related data deleted successfully.']);
    } else {
        echo json_encode(['message' => 'Failed to delete train.']);
    }
}
?>