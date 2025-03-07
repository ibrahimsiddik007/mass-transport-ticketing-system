<?php
session_start();
include 'db.php'; // Include your database connection file
require 'phpqrcode/qrlib.php'; // Include the QR code library
require 'fpdf/fpdf.php'; // Include the FPDF library

if (!isset($_GET['transaction_id'])) {
    echo 'error: missing transaction ID';
    exit;
}

$transaction_id = $_GET['transaction_id'];

// Fetch transaction details
$stmt = $conn2->prepare("SELECT t.*, tr.train_name, tr.start_point, tr.end_point, r.compartment_id, r.seat_number, r.reservation_date, tr.train_name 
                         FROM train_transactions t
                         JOIN reservations r ON t.train_id = r.train_id
                         JOIN trains tr ON r.train_id = tr.train_id
                         WHERE t.transaction_id = ?");
$stmt->bind_param("s", $transaction_id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    echo 'error: transaction not found';
    exit;
}

// Prepare data for QR code
$qrData = "Transaction ID: {$transaction['transaction_id']}\n";
$qrData .= "Train: {$transaction['train_name']}\n";
$qrData .= "Start Point: {$transaction['start_point']}\n";
$qrData .= "End Point: {$transaction['end_point']}\n";
$qrData .= "Compartment Number: {$transaction['compartment_id']}\n";
$qrData .= "Seat Numbers: {$transaction['seat_number']}\n";
$qrData .= "Reservation Date: {$transaction['reservation_date']}\n";
$qrData .= "Amount Paid: BDT {$transaction['amount']}\n";
$qrData .= "Payment Time: {$transaction['payment_time']}\n";
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
        $this->Cell(0, 10, 'Train: ' . $transaction['train_name'], 0, 1);
        $this->Cell(0, 10, 'Start Point: ' . $transaction['start_point'], 0, 1);
        $this->Cell(0, 10, 'End Point: ' . $transaction['end_point'], 0, 1);
        $this->Cell(0, 10, 'Compartment Number: ' . $transaction['compartment_id'], 0, 1);
        $this->Cell(0, 10, 'Seat Numbers: ' . $transaction['seat_number'], 0, 1);
        $this->Cell(0, 10, 'Reservation Date: ' . $transaction['reservation_date'], 0, 1);
        $this->Cell(0, 10, 'Amount Paid: BDT ' . $transaction['amount'], 0, 1);
        $this->Cell(0, 10, 'Payment Time: ' . $transaction['payment_time'], 0, 1);
        $this->Ln(10);
        $this->Image($qrFile, $this->GetX(), $this->GetY(), 50, 50);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->ReceiptBody($transaction, $qrFile);
$pdf->Output('I', 'receipt_' . $transaction_id . '.pdf');
?>