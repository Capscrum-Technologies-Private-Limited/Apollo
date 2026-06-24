<?php
/**
 * Unified PHP Error Rendering Helper
 * Apollo Cleaning Services Platform
 */

if (!function_exists('render_premium_error')) {
    /**
     * Renders a highly polished error page matching the platform's design system
     *
     * @param int $statusCode HTTP status code (400, 403, 404, 500 etc)
     * @param string $title Short title of the error
     * @param string $message Detailed user-friendly description of what occurred
     * @param string|null $backUrl Destination for the back button (defaults to index.html)
     */
    function render_premium_error($statusCode, $title, $message, $backUrl = null) {
        // Prevent sending headers if already sent, but set response code if possible
        if (!headers_sent()) {
            http_response_code($statusCode);
        }
        
        $backUrl = $backUrl ?? './index.html';
        $isDbError = (strpos(strtolower($title), 'database') !== false || strpos(strtolower($message), 'database') !== false);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title><?= htmlspecialchars($title) ?> — Error <?= $statusCode ?></title>
          <link rel="preconnect" href="https://fonts.googleapis.com">
          <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
          <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
          <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
            
            :root {
              --primary: #1B3E6B;
              --primary-dark: #150F38;
              --danger: #dc2626;
              --warning: #d97706;
              --bg: #f7f7f7;
              --white: #ffffff;
              --text: #1a1a1a;
              --text-secondary: #636363;
              --border: #e8e8e8;
            }
            
            body {
              font-family: 'Manrope', sans-serif;
              background: var(--bg);
              color: var(--text);
              min-height: 100vh;
              display: flex;
              align-items: center;
              justify-content: center;
              padding: 2rem 1rem;
              line-height: 1.6;
            }
            
            .error-card {
              background: var(--white);
              border-radius: 16px;
              box-shadow: 0 10px 30px rgba(0,0,0,.05);
              border: 1px solid var(--border);
              width: 100%;
              max-width: 500px;
              padding: 3rem 2rem;
              text-align: center;
              position: relative;
              overflow: hidden;
            }
            
            .error-card::before {
              content: '';
              position: absolute;
              top: 0;
              left: 0;
              width: 100%;
              height: 4px;
              background: <?= $isDbError ? 'var(--warning)' : 'var(--danger)' ?>;
            }
            
            .icon-wrapper {
              width: 72px;
              height: 72px;
              border-radius: 50%;
              background: <?= $isDbError ? 'rgba(217, 119, 6, 0.1)' : 'rgba(220, 38, 38, 0.1)' ?>;
              color: <?= $isDbError ? 'var(--warning)' : 'var(--danger)' ?>;
              display: inline-flex;
              align-items: center;
              justify-content: center;
              font-size: 2rem;
              margin-bottom: 1.5rem;
            }
            
            h1 {
              font-size: 1.6rem;
              font-weight: 700;
              color: var(--primary-dark);
              margin-bottom: 0.75rem;
              letter-spacing: -0.5px;
            }
            
            p {
              color: var(--text-secondary);
              font-size: 0.95rem;
              margin-bottom: 2rem;
            }
            
            .debug-box {
              background: #f8fafc;
              border: 1px solid #e2e8f0;
              border-radius: 8px;
              padding: 1rem;
              font-family: monospace;
              font-size: 0.85rem;
              text-align: left;
              color: #475569;
              margin-bottom: 2rem;
              word-break: break-all;
              max-height: 150px;
              overflow-y: auto;
            }
            
            .btn {
              display: inline-flex;
              align-items: center;
              justify-content: center;
              gap: 8px;
              background: var(--primary);
              color: var(--white);
              font-weight: 600;
              height: 46px;
              padding: 0 24px;
              border-radius: 8px;
              text-decoration: none;
              transition: background 0.2s, transform 0.1s;
            }
            
            .btn:hover {
              background: var(--primary-dark);
              transform: translateY(-1px);
            }
            
            .btn:active {
              transform: translateY(0);
            }
            
            .footer-links {
              margin-top: 1.5rem;
              display: flex;
              justify-content: center;
              gap: 15px;
              font-size: 0.85rem;
            }
            
            .footer-links a {
              color: var(--primary);
              text-decoration: none;
            }
            
            .footer-links a:hover {
              text-decoration: underline;
            }
          </style>
        </head>
        <body>
          <div class="error-card">
            <div class="icon-wrapper">
              <i class="fa-solid <?= $isDbError ? 'fa-database' : 'fa-circle-exclamation' ?>"></i>
            </div>
            
            <h1><?= htmlspecialchars($title) ?></h1>
            <p><?= htmlspecialchars($message) ?></p>
            
            <?php if ($isDbError): ?>
              <div class="debug-box">
                <strong>Troubleshooting tips:</strong><br>
                1. Make sure your MySQL server is running.<br>
                2. Check if the database credentials are correct.<br>
                3. Run the <a href="./install.php" style="color:var(--primary);text-decoration:underline;">Installer</a> page to configure the connection.
              </div>
            <?php endif; ?>
            
            <a href="<?= htmlspecialchars($backUrl) ?>" class="btn">
              <i class="fa-solid fa-house"></i> Back to Homepage
            </a>
            
            <div class="footer-links">
              <a href="./install.php">Run Setup</a>
              <span>•</span>
              <a href="./contact.html">Support Services</a>
            </div>
          </div>
        </body>
        </html>
        <?php
        exit;
    }
}
?>
