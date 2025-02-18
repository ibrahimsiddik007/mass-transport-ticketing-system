<?php
session_start();
include 'db.php'; // Include your database connection file
include 'phpqrcode/qrlib.php'; // Include the QR code library
require('fpdf/fpdf.php'); // Include the FPDF library

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'metro.php';
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the token is valid
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        header('Location: metro.php');
        exit;
    }

    // Unset the token to prevent reuse
    unset($_SESSION['token']);

    $startLocation = $_POST['startLocation'];
    $endLocation = $_POST['endLocation'];
    $fare = $_POST['fare'];

    // Simulate payment success
    $paymentStatus = "success";

    // Generate unique transaction_id
    $transactionId = 'txn_' . substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 7);

    // Get user_id from session
    $userId = $_SESSION['user_id'];

    // Get current timestamp
    $createdAt = date('Y-m-d H:i:s');

    // Fetch user details
    $stmt = $conn1->prepare("SELECT name, email, phone FROM users WHERE id = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn1->error));
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($username, $email, $phone);
    $stmt->fetch();
    $stmt->close();

    // Save transaction details
    $stmt = $conn1->prepare("INSERT INTO transactions (transaction_id, user_id, start_location, end_location, fare, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn1->error));
    }
    $status = 'success';
    $stmt->bind_param("sssssss", $transactionId, $userId, $startLocation, $endLocation, $fare, $status, $createdAt);
    $stmt->execute();
    if ($stmt->affected_rows === 0) {
        die('Insert failed: ' . htmlspecialchars($stmt->error));
    }
    $stmt->close();

    // Generate QR code data
    $qrData = "Name: " . $username . "\nEmail: " . $email . "\nPhone: " . $phone . "\nStart Point: " . $startLocation . "\nEnd Point: " . $endLocation . "\nFare: " . $fare . " BDT\nDate: " . $createdAt;
    $qrFile = 'receipts/' . $transactionId . '.png';

    // Ensure the receipts directory exists and is writable
    if (!is_dir('receipts')) {
        mkdir('receipts', 0777, true);
    }

    // Generate the QR code
    QRcode::png($qrData, $qrFile);

    // Create PDF receipt
    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, 'Payment Receipt', 0, 1, 'C');
            $this->Ln(10);
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Transaction ID: ' . $transactionId, 0, 1);
    $pdf->Cell(0, 10, 'Name: ' . $username, 0, 1);
    $pdf->Cell(0, 10, 'Email: ' . $email, 0, 1);
    $pdf->Cell(0, 10, 'Phone: ' . $phone, 0, 1);
    $pdf->Cell(0, 10, 'Start Location: ' . $startLocation, 0, 1);
    $pdf->Cell(0, 10, 'End Location: ' . $endLocation, 0, 1);
    $pdf->Cell(0, 10, 'Fare: ' . $fare . ' BDT', 0, 1);
    $pdf->Cell(0, 10, 'Date: ' . $createdAt, 0, 1);
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Scan the QR code below for details:', 0, 1);
    $pdf->Image($qrFile, $pdf->GetX(), $pdf->GetY(), 50, 50);
    $pdfFile = 'receipts/' . $transactionId . '.pdf';
    $pdf->Output('F', $pdfFile);

    // Set session flag to indicate payment completion
    $_SESSION['payment_completed'] = true;

    // Redirect to the success display page
    header('Location: payment_success_display.php?transaction_id=' . $transactionId);
    exit;
} else {
    header('Location: metro.php');
    exit;
}
?>