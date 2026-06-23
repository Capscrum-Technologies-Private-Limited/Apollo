<?php
// API Endpoint: Process Payment & Update Invoices / Bookings

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/db.php';

$inputData = json_decode(file_get_contents('php://input'), true);

if (!$inputData) {
    $inputData = $_POST;
}

$invoiceId = intval($inputData['invoice_id'] ?? 0);
$paymentMethod = trim($inputData['payment_method'] ?? 'Credit Card');
$transactionId = trim($inputData['transaction_id'] ?? '');
$amount = floatval($inputData['amount'] ?? 0);

if ($invoiceId <= 0 || empty($transactionId) || $amount <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing transaction details']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Verify invoice exists and is unpaid
    $stmtInvoice = $pdo->prepare("SELECT * FROM invoices WHERE id = :id FOR UPDATE");
    $stmtInvoice->execute([':id' => $invoiceId]);
    $invoice = $stmtInvoice->fetch();

    if (!$invoice) {
        throw new Exception("Invoice not found.");
    }

    if ($invoice['status'] === 'paid') {
        echo json_encode(['status' => 'success', 'message' => 'Invoice already paid']);
        $pdo->rollBack();
        exit;
    }

    // 2. Insert payment record
    $stmtPay = $pdo->prepare("INSERT INTO payments (invoice_id, payment_method, transaction_id, amount, status) 
                              VALUES (:invoice_id, :payment_method, :transaction_id, :amount, 'completed')");
    $stmtPay->execute([
        ':invoice_id' => $invoiceId,
        ':payment_method' => $paymentMethod,
        ':transaction_id' => $transactionId,
        ':amount' => $amount
    ]);

    // 3. Update invoice status to 'paid'
    $stmtUpdateInvoice = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = :id");
    $stmtUpdateInvoice->execute([':id' => $invoiceId]);

    // 4. Update booking status to 'confirmed'
    $stmtUpdateBooking = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = :id");
    $stmtUpdateBooking->execute([':id' => $invoice['booking_id']]);

    $pdo->commit();

    // Determine the base URL dynamically
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    $subfolder = '';
    if (strpos($uri, '/api/') !== false) {
        $subfolder = explode('/api/', $uri)[0];
    }
    $baseUrl = $protocol . "://" . $host . $subfolder;

    echo json_encode([
        'status' => 'success',
        'message' => 'Payment logged and invoice cleared successfully',
        'redirect_url' => $baseUrl . '/receipt.php?invoice_id=' . $invoiceId
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to process payment: ' . $e->getMessage()]);
}
?>
