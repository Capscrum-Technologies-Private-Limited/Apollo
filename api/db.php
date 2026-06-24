<?php
// Database Connection Helper

if (!file_exists(__DIR__ . '/config.php')) {
    // If not configured, redirect to installer
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    // Find subfolder
    $uri = $_SERVER['REQUEST_URI'];
    $subfolder = '';
    if (strpos($uri, '/api/') !== false) {
        $subfolder = explode('/api/', $uri)[0];
    }
    
    header("Location: " . $protocol . "://" . $host . $subfolder . "/install.php");
    exit;
}

require_once __DIR__ . '/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    require_once __DIR__ . '/error_helper.php';
    
    // Determine if calling request is expecting JSON
    $isApi = (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) || 
             (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
             
    if ($isApi) {
        http_response_code(500);
        die(json_encode([
            'status' => 'error',
            'message' => 'Database connection failed: ' . $e->getMessage()
        ]));
    } else {
        $backLink = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../index.html' : './index.html';
        render_premium_error(500, 'Database Connection Failed', 'We were unable to establish a secure database connection. Please check your MySQL setup or run the installation portal.', $backLink);
    }
}
?>
