<?php
// API Endpoint: Submit Contact Inquiry to Database
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
$subject = trim($inputData['subject'] ?? '');
$date = trim($inputData['date'] ?? '');
$messageText = trim($inputData['message'] ?? '');

// Simple Validation
if (empty($name) || empty($email) || empty($messageText)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields (Name, Email, Message)']);
    exit;
}

// Compile a detailed message block if extra fields are provided
$fullMessage = $messageText;
if (!empty($service) || !empty($subject) || !empty($date)) {
    $fullMessage = "Subject: " . ($subject ? $subject : 'N/A') . "\n"
                 . "Service Selection: " . ($service ? $service : 'N/A') . "\n"
                 . "Preferred Date: " . ($date ? $date : 'N/A') . "\n\n"
                 . "Message:\n" . $messageText;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO inquiries (name, email, phone, message, status) 
        VALUES (:name, :email, :phone, :message, 'unread')
    ");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':message' => $fullMessage
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Your message has been received successfully.'
    ]);

} catch (PDOException $e) {
    error_log('api/inquiry.php — PDOException: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit inquiry: ' . $e->getMessage()]);
}
?>
