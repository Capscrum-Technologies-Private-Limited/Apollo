<?php
/**
 * Apollo Cleaning Platform — Blog Posts API
 * 
 * GET /api/get_blog.php           → All published posts (newest first)
 * GET /api/get_blog.php?slug=xxx  → Single published post by slug
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
        'status' => 'error',
        'message' => 'Method not allowed. Use GET.'
    ]);
    exit;
}

try {
    require_once __DIR__ . '/db.php';

    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;

    if ($slug !== null && $slug !== '') {
        // ── Single post by slug ──
        $stmt = $pdo->prepare(
            "SELECT * FROM blog_posts
             WHERE slug = :slug AND status = 'published'
             LIMIT 1"
        );
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();

        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            echo json_encode([
                'status' => 'success',
                'data'   => $post
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Blog post not found.'
            ]);
        }
    } else {
        // ── All published posts ──
        $stmt = $pdo->prepare(
            "SELECT * FROM blog_posts
             WHERE status = 'published'
             ORDER BY published_at DESC"
        );
        $stmt->execute();

        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data'   => $posts
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database error occurred.'
    ]);
    // Log the real error server-side
    error_log('get_blog.php — PDOException: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'An unexpected error occurred.'
    ]);
    error_log('get_blog.php — Exception: ' . $e->getMessage());
}
