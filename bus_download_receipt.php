<?php
session_start();
include 'db.php'; // Include your database connection file
include 'phpqrcode/qrlib.php'; // Include the QR code library
require('fpdf/fpdf.php'); // Include the FPDF library

if (!isset($_GET['transaction_id'])) {
    echo 'error: missing transaction ID';
    exit;
}

$transaction_id = $_GET['transaction_id'];
$user_name = $_SESSION['user_name'];

// Set the time zone to Asia/Dhaka
date_default_timezone_set('Asia/Dhaka');

// Fetch transaction details
$query = "SELECT * FROM bus_transactions WHERE transaction_id = ?";
$stmt = $conn3->prepare($query);
$stmt->bind_param("s", $transaction_id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    echo 'error: transaction not found';
    exit;
}

// Calculate "Valid Till" time
$payment_time = new DateTime($transaction['payment_time']);
$valid_till = $payment_time->add(new DateInterval('PT3H'))->format('Y-m-d H:i:s');

// Prepare data for QR code
$qrContent = 'Transaction ID: ' . $transaction['transaction_id'] . "\n" .
             'Name: ' . $user_name . "\n" .
             'Fare: ' . $transaction['amount'] . " BDT\n" .
             'Origin: ' . $transaction['origin'] . "\n" .
             'Destination: ' . $transaction['destination'] . "\n" .
             'Bus: ' . $transaction['bus_name'] . "\n" .
             'Valid Till: ' . $valid_till;

// Generate QR code
$qrFilePath = 'qrcodes/' . $transaction_id . '.png';
QRcode::png($qrContent, $qrFilePath, QR_ECLEVEL_L, 5);

// Create PDF receipt
class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Mass Transport Ticketing System', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Payment Receipt', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-30);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Thank you for your purchase!', 0, 1, 'C');
        $this->Cell(0, 10, 'For any complaints/queries, please email to:', 0, 1, 'C');
        $this->Cell(0, 10, 'Email: support@masstransport.com', 0, 1, 'C');
    }

    function ReceiptBody($transaction, $qrFilePath, $valid_till, $user_name)
    {
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Transaction ID: ' . $transaction['transaction_id'], 0, 1);
        $this->Cell(0, 10, 'Name: ' . $user_name, 0, 1);
        $this->Cell(0, 10, 'Fare: ' . $transaction['amount'] . ' BDT', 0, 1);
        $this->Cell(0, 10, 'Origin: ' . $transaction['origin'], 0, 1);
        $this->Cell(0, 10, 'Destination: ' . $transaction['destination'], 0, 1);
        $this->Cell(0, 10, 'Bus: ' . $transaction['bus_name'], 0, 1);
        $this->Cell(0, 10, 'Valid Till: ' . $valid_till, 0, 1);
        $this->Ln(10);
        $this->Cell(0, 10, 'Please scan the QR code below for information:', 0, 1);
        $this->Image($qrFilePath, $this->GetX(), $this->GetY(), 50, 50);
        $this->Ln(60);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->ReceiptBody($transaction, $qrFilePath, $valid_till, $user_name);
$pdf->Output('I', 'receipt_' . $transaction_id . '.pdf');
?>