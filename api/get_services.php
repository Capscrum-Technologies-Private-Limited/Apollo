<?php
/**
 * Apollo Cleaning Platform — Services API
 *
 * GET /api/get_services.php               → All services
 * GET /api/get_services.php?id=xxx        → Single service by service_id
 * GET /api/get_services.php?category=xxx  → Services filtered by category
 *
 * Response: { "status": "success"|"error", "data": ... }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Method not allowed. Use GET.'
    ]);
    exit;
}

try {
    require_once __DIR__ . '/db.php';

    $id       = isset($_GET['id'])       ? trim($_GET['id'])       : null;
    $category = isset($_GET['category']) ? trim($_GET['category']) : null;

    if ($id !== null && $id !== '') {
        // ── Single service by ID ──
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Invalid service ID format.'
            ]);
            exit;
        }

        $stmt = $pdo->prepare(
            "SELECT * FROM services WHERE service_id = :id LIMIT 1"
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($service) {
            echo json_encode([
                'status' => 'success',
                'data'   => $service
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Service not found.'
            ]);
        }
    } elseif ($category !== null && $category !== '') {
        // ── Filter by category ──
        $stmt = $pdo->prepare(
            "SELECT * FROM services WHERE category = :category ORDER BY service_id ASC"
        );
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->execute();

        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data'   => $services
        ]);
    } else {
        // ── All services ──
        $stmt = $pdo->prepare(
            "SELECT * FROM services ORDER BY service_id ASC"
        );
        $stmt->execute();

        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data'   => $services
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database error occurred.'
    ]);
    error_log('get_services.php — PDOException: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'An unexpected error occurred.'
    ]);
    error_log('get_services.php — Exception: ' . $e->getMessage());
}
