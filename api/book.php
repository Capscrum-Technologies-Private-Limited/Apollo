<?php
// API Endpoint: Submit Booking Inquiry & Generate Invoice

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

// Get JSON inputs
$inputData = json_decode(file_get_contents('php://input'), true);

if (!$inputData) {
    // Fallback to URL-encoded form data
    $inputData = $_POST;
}

$name = trim($inputData['name'] ?? '');
$email = trim($inputData['email'] ?? '');
$phone = trim($inputData['phone'] ?? '');
$service = trim($inputData['service'] ?? '');
$date = trim($inputData['date'] ?? '');
$time = trim($inputData['time'] ?? '');
$notes = trim($inputData['notes'] ?? '');
$price = floatval($inputData['price'] ?? 0);

// Simple Validation
if (empty($name) || empty($email) || empty($phone) || empty($service) || empty($date) || empty($time)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Insert into bookings
    $stmt = $pdo->prepare("INSERT INTO bookings (customer_name, customer_email, customer_phone, service_type, booking_date, booking_time, notes, status, total_price) 
                           VALUES (:name, :email, :phone, :service, :date, :time, :notes, 'pending', :price)");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':service' => $service,
        ':date' => $date,
        ':time' => $time,
        ':notes' => $notes,
        ':price' => $price
    ]);
    
    $bookingId = $pdo->lastInsertId();

    // 2. Generate invoice number
    $year = date('Y');
    $rand = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $invoiceNumber = "INV-{$year}-{$rand}";
    $dueDate = date('Y-m-d', strtotime('+3 days'));

    // 3. Create invoice record
    $stmtInvoice = $pdo->prepare("INSERT INTO invoices (invoice_number, booking_id, amount, status, due_date) 
                                  VALUES (:invoice_number, :booking_id, :amount, 'unpaid', :due_date)");
    $stmtInvoice->execute([
        ':invoice_number' => $invoiceNumber,
        ':booking_id' => $bookingId,
        ':amount' => $price,
        ':due_date' => $dueDate
    ]);

    $invoiceId = $pdo->lastInsertId();
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
        'message' => 'Booking logged successfully',
        'booking_id' => $bookingId,
        'invoice_id' => $invoiceId,
        'invoice_number' => $invoiceNumber,
        'redirect_url' => $baseUrl . '/checkout.php?invoice_id=' . $invoiceId
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to process booking: ' . $e->getMessage()]);
}
?>
