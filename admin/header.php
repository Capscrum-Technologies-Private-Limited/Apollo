<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../api/db.php';

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Apollo Admin') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #1B3E6B;
            --primary-dark: #150F38;
            --sidebar-bg: #150F38;
            --bg: #f7f7f7;
            --text: #1a1a1a;
            --text-secondary: #636363;
            --border: #e8e8e8;
            --white: #ffffff;
            --success: #16a34a;
            --warning: #ca8a04;
            --danger: #dc2626;
        }

        body {
            font-family: 'Manrope', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: var(--white);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }
        .sidebar-brand {
            padding: 2rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .sidebar-brand i {
            color: #38bdf8;
        }
        .sidebar-menu {
            list-style: none;
            padding: 1.5rem 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            flex-grow: 1;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.85rem 1rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 550;
            transition: all 0.2s ease;
        }
        .sidebar-link:hover {
            color: var(--white);
            background: rgba(255, 255, 255, 0.05);
        }
        .sidebar-link.active {
            color: var(--white);
            background: var(--primary);
        }
        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.4);
        }

        /* ── Main Layout ── */
        .main-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .topbar {
            height: 70px;
            background: var(--white);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
        }
        .topbar-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-dark);
        }
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            font-weight: 600;
        }
        .topbar-user i {
            color: var(--primary);
        }

        .content-body {
            padding: 2rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        /* ── Utility Admin Styles ── */
        .card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.02);
            margin-bottom: 1.5rem;
        }
        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .admin-table th {
            padding: 0.85rem 1rem;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--border);
            font-weight: 700;
        }
        .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
            vertical-align: middle;
        }
        .admin-table tr:hover td {
            background: #fafafa;
        }

        /* Status Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge-pending { background: #fef9c3; color: #a16207; }
        .badge-confirmed { background: #dcfce7; color: #15803d; }
        .badge-cancelled { background: #fee2e2; color: #b91c1c; }
        .badge-paid { background: #dcfce7; color: #15803d; }
        .badge-unpaid { background: #fee2e2; color: #b91c1c; }
        .badge-draft { background: #f3f4f6; color: #4b5563; }
        .badge-published { background: #dbeafe; color: #1d4ed8; }

        /* Buttons */
        .btn-admin {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .btn-admin-primary {
            background: var(--primary);
            color: var(--white);
        }
        .btn-admin-primary:hover {
            background: var(--primary-dark);
        }
        .btn-admin-success {
            background: var(--success);
            color: var(--white);
        }
        .btn-admin-success:hover {
            opacity: 0.9;
        }
        .btn-admin-danger {
            background: var(--danger);
            color: var(--white);
        }
        .btn-admin-danger:hover {
            opacity: 0.9;
        }
        .btn-admin-outline {
            background: var(--white);
            color: var(--text);
            border-color: var(--border);
        }
        .btn-admin-outline:hover {
            background: #f5f5f5;
            border-color: #d0d0d0;
        }
        .btn-admin-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 4px;
        }

        /* Responsive Mobile Layout */
        .mobile-header {
            display: none;
            height: 60px;
            background: var(--sidebar-bg);
            color: var(--white);
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
        }
        .mobile-toggle {
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
        }

        @media (max-width: 991px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                position: fixed;
                top: 60px;
                left: -260px;
                height: calc(100vh - 60px);
                z-index: 1000;
                box-shadow: 4px 0 15px rgba(0,0,0,0.1);
            }
            .sidebar.active {
                left: 0;
            }
            .mobile-header {
                display: flex;
            }
            .topbar {
                display: none;
            }
            .content-body {
                padding: 1.25rem;
            }
        }
    </style>
</head>
<body>

    <!-- Mobile Navigation Header -->
    <header class="mobile-header">
        <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800;">
            <i class="fa-solid fa-circle-nodes" style="color: #38bdf8;"></i>
            <span>APOLLO ADMIN</span>
        </div>
        <button class="mobile-toggle" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
    </header>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-circle-nodes"></i>
            <span>Apollo Services</span>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php" class="sidebar-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="bookings.php" class="sidebar-link <?= $currentPage === 'bookings.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <li>
                <a href="services.php" class="sidebar-link <?= $currentPage === 'services.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-concierge-bell"></i>
                    <span>Services</span>
                </a>
            </li>
            <li>
                <a href="blog.php" class="sidebar-link <?= $currentPage === 'blog.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-newspaper"></i>
                    <span>Blog CMS</span>
                </a>
            </li>
            <li style="margin-top: auto; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 0.5rem;">
                <a href="../index.html" class="sidebar-link" target="_blank">
                    <i class="fa-solid fa-external-link-alt"></i>
                    <span>View Site</span>
                </a>
            </li>
            <li>
                <a href="logout.php" class="sidebar-link" style="color: #fda4af;">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            Admin Portal v1.0
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <header class="topbar">
            <h1 class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
            <div class="topbar-user">
                <i class="fa-solid fa-circle-user"></i>
                <span>Hello, <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
            </div>
        </header>

        <main class="content-body">
