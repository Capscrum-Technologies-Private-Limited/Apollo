<?php
session_start();

// Already logged in? Go to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../api/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Apollo Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Manrope', sans-serif;
            background: #f7f7f7;
            color: #1a1a1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px 32px;
            box-shadow: 0 4px 24px rgba(21, 15, 56, 0.08);
            border: 1px solid #e8e8e8;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo .logo-icon {
            width: 56px;
            height: 56px;
            background: #1B3E6B;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }
        .login-logo .logo-icon i {
            color: #fff;
            font-size: 24px;
        }
        .login-logo h1 {
            font-size: 22px;
            font-weight: 700;
            color: #150F38;
        }
        .login-logo p {
            font-size: 14px;
            color: #636363;
            margin-top: 4px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 6px;
        }
        .form-group .input-wrap {
            position: relative;
        }
        .form-group .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #636363;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid #e8e8e8;
            border-radius: 10px;
            font-family: 'Manrope', sans-serif;
            font-size: 14px;
            color: #1a1a1a;
            background: #f7f7f7;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .form-group input:focus {
            border-color: #1B3E6B;
            box-shadow: 0 0 0 3px rgba(27, 62, 107, 0.1);
            background: #fff;
        }
        .error-msg {
            background: #fff0f0;
            color: #c0392b;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .error-msg i { font-size: 14px; }
        .btn-login {
            width: 100%;
            padding: 13px;
            background: #1B3E6B;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Manrope', sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }
        .btn-login:hover { background: #150F38; }
        .btn-login:active { transform: scale(0.98); }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo">
                <div class="logo-icon"><i class="fas fa-spray-can-sparkles"></i></div>
                <h1>Apollo Admin</h1>
                <p>Sign in to manage your platform</p>
            </div>

            <?php if ($error): ?>
                <div class="error-msg">
                    <i class="fas fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-right-to-bracket"></i>&nbsp; Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>
