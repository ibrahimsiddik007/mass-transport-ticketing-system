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
$stmt = $conn3->prepare("
    SELECT t.*, b.bus_name, b.from_location, b.to_location, b.departure_time, b.bus_type
    FROM long_route_transactions t
    JOIN long_route_buses b ON t.bus_id = b.bus_id
    WHERE t.transaction_id = ?
");
$stmt->bind_param("s", $transaction_id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    echo 'error: transaction not found';
    exit;
}

// Fetch user details from conn1
$user_stmt = $conn1->prepare("SELECT name FROM users WHERE id = ?");
$user_stmt->bind_param("i", $transaction['user_id']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Prepare data for QR code
$qrData = "Transaction ID: {$transaction['transaction_id']}\n";
$qrData .= "Bus: {$transaction['bus_name']}\n";
$qrData .= "From: {$transaction['from_location']}\n";
$qrData .= "To: {$transaction['to_location']}\n";
$qrData .= "Bus Type: {$transaction['bus_type']}\n";
$qrData .= "Seat Numbers: {$transaction['seat_numbers']}\n";
$qrData .= "Journey Date: {$transaction['journey_date']}\n";
$qrData .= "Amount Paid: BDT " . number_format($transaction['amount'], 2) . "\n";
$qrData .= "Payment Time: {$transaction['payment_time']}\n";
$qrData .= "User Name: {$user['name']}";

// Generate QR code
$qrFile = 'qrcodes/' . $transaction_id . '.png';
QRcode::png($qrData, $qrFile, QR_ECLEVEL_L, 3);

// Create PDF receipt
class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Mass Transport Ticketing System', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'InterCity Bus Ticket Receipt', 0, 1, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'For support, email us at: masstransportsystem@gmail.com', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Thank you for choosing Mass Transport Ticketing System!', 0, 0, 'C');
    }

    function ReceiptBody($transaction, $qrFile)
    {
        global $user;
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'User Name: ' . $user['name'], 0, 1);
        $this->Cell(0, 10, 'Transaction ID: ' . $transaction['transaction_id'], 0, 1);
        $this->Cell(0, 10, 'Bus: ' . $transaction['bus_name'], 0, 1);
        $this->Cell(0, 10, 'From: ' . $transaction['from_location'], 0, 1);
        $this->Cell(0, 10, 'To: ' . $transaction['to_location'], 0, 1);
        $this->Cell(0, 10, 'Bus Type: ' . $transaction['bus_type'], 0, 1);
        $this->Cell(0, 10, 'Seat Numbers: ' . $transaction['seat_numbers'], 0, 1);
        $this->Cell(0, 10, 'Journey Date: ' . date('l, F j, Y', strtotime($transaction['journey_date'])), 0, 1);
        $this->Cell(0, 10, 'Departure Time: ' . date('h:i A', strtotime($transaction['departure_time'])), 0, 1);
        $this->Cell(0, 10, 'Amount Paid: BDT ' . number_format($transaction['amount'], 2), 0, 1);
        $this->Cell(0, 10, 'Payment Time: ' . $transaction['payment_time'], 0, 1);
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Please scan the QR code below for verification:', 0, 1, 'C');
        $this->Ln(5);
        $this->Image($qrFile, $this->GetX(), $this->GetY(), 50, 50);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->ReceiptBody($transaction, $qrFile);
$pdf->Output('I', 'receipt_' . $transaction_id . '.pdf');
?> 