<?php
session_start();
include 'db.php'; // Include your database connection file
include 'phpqrcode/qrlib.php'; // Include the QR code library
require('fpdf/fpdf.php'); // Include the FPDF library

if (!isset($_SESSION['transaction_id'])) {
    echo 'error: missing transaction ID';
    exit;
}

$transaction_id = $_SESSION['transaction_id'];

// Fetch transaction details
$stmt = $conn1->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
$stmt->bind_param("s", $transaction_id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    echo 'error: transaction not found';
    exit;
}

// Prepare data for QR code
$qrData = "Transaction ID: {$transaction['transaction_id']}\n";
$qrData .= "Start Location: {$transaction['start_location']}\n";
$qrData .= "End Location: {$transaction['end_location']}\n";
$qrData .= "Fare: {$transaction['fare']} BDT\n";
$qrData .= "Created At: {$transaction['created_at']}\n";
$qrData .= "User Name: {$_SESSION['user_name']}";

// Generate QR code
$qrFile = 'qrcodes/' . $transaction_id . '.png';
QRcode::png($qrData, $qrFile, QR_ECLEVEL_L, 3);

// Create PDF receipt
class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Payment Receipt', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Thank you for your purchase!', 0, 0, 'C');
    }

    function ReceiptBody($transaction, $qrFile)
    {
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'User Name: ' . $_SESSION['user_name'], 0, 1);
        $this->Cell(0, 10, 'Transaction ID: ' . $transaction['transaction_id'], 0, 1);
        $this->Cell(0, 10, 'Start Location: ' . $transaction['start_location'], 0, 1);
        $this->Cell(0, 10, 'End Location: ' . $transaction['end_location'], 0, 1);
        $this->Cell(0, 10, 'Fare: ' . $transaction['fare'] . ' BDT', 0, 1);
        $this->Cell(0, 10, 'Created At: ' . $transaction['created_at'], 0, 1);
        $this->Ln(10);
        $this->Image($qrFile, $this->GetX(), $this->GetY(), 50, 50);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->ReceiptBody($transaction, $qrFile);
$pdf->Output('I', 'receipt_' . $transaction_id . '.pdf');
?>