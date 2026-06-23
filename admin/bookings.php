<?php
$pageTitle = 'Manage Bookings — Apollo Admin';
require_once __DIR__ . '/header.php';

$message = '';
$messageType = '';

// Handle actions (Confirm, Cancel, Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    
    try {
        if ($action === 'confirm') {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Booking #{$id} has been confirmed successfully.";
            $messageType = 'success';
        } elseif ($action === 'cancel') {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Booking #{$id} has been cancelled.";
            $messageType = 'info';
        } elseif ($action === 'delete') {
            // Delete booking (associated invoices and payments will cascade delete)
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Booking #{$id} and its associated invoices/payments have been deleted.";
            $messageType = 'warning';
        }
        
        // Redirect to avoid double execution on page refresh
        header("Location: bookings.php?msg=" . urlencode($message) . "&msg_type=" . urlencode($messageType));
        exit;
    } catch (PDOException $e) {
        error_log('bookings.php action error — ' . $e->getMessage());
        $message = "Database error executing action: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Check for redirect message
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['msg_type'] ?? 'success';
}

// Fetch all bookings
try {
    $stmt = $pdo->prepare("
        SELECT b.*, i.id AS invoice_id, i.invoice_number, i.status AS invoice_status 
        FROM bookings b
        LEFT JOIN invoices i ON b.id = i.booking_id
        ORDER BY b.created_at DESC
    ");
    $stmt->execute();
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('bookings.php fetch error — ' . $e->getMessage());
    $bookings = [];
    $message = "Failed to load bookings: " . $e->getMessage();
    $messageType = 'error';
}

$servicesMap = [
    'office' => 'Office Cleaning',
    'carpet' => 'Carpet Cleaning',
    'window' => 'Window Cleaning',
    'lawn' => 'Cleaning & Lawn Mowing',
    'outings' => 'Outings - Day & Night',
    'transport' => 'Transport Services',
    'endlease' => 'End of Lease Cleaning',
    'personal' => 'Personal Care',
    'pressure' => 'Pressure Cleaning',
    'facility' => 'Facility Management'
];
?>

<style>
    .alert-banner {
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .alert-banner-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .alert-banner-info { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .alert-banner-warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .alert-banner-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    .action-group {
        display: flex;
        gap: 0.25rem;
    }
</style>

<?php if ($message): ?>
    <div class="alert-banner alert-banner-<?= htmlspecialchars($messageType) ?>">
        <i class="fa-solid fa-circle-info"></i>
        <span><?= htmlspecialchars($message) ?></span>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-title">
        <span>Bookings Registry</span>
        <span style="font-size: 0.85rem; font-weight: normal; color: var(--text-secondary);">Total: <?= count($bookings) ?> bookings</span>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Contact</th>
                    <th>Service</th>
                    <th>Date &amp; Time</th>
                    <th>Price</th>
                    <th>Booking Status</th>
                    <th>Payment Status</th>
                    <th>Invoice</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 3rem;">No bookings found in the database.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): 
                        $serviceLabel = $servicesMap[$booking['service_type']] ?? htmlspecialchars($booking['service_type']);
                    ?>
                        <tr>
                            <td>#<?= $booking['id'] ?></td>
                            <td>
                                <strong style="display:block;"><?= htmlspecialchars($booking['customer_name']) ?></strong>
                                <span style="font-size:0.8rem;color:var(--text-secondary);display:block;">
                                    <i class="fa-solid fa-envelope" style="font-size:0.75rem;"></i> <?= htmlspecialchars($booking['customer_email']) ?>
                                </span>
                                <span style="font-size:0.8rem;color:var(--text-secondary);display:block;">
                                    <i class="fa-solid fa-phone" style="font-size:0.75rem;"></i> <?= htmlspecialchars($booking['customer_phone']) ?>
                                </span>
                            </td>
                            <td><?= $serviceLabel ?></td>
                            <td>
                                <strong><?= date('M j, Y', strtotime($booking['booking_date'])) ?></strong><br>
                                <span style="font-size:0.8rem;color:var(--text-secondary);"><?= htmlspecialchars($booking['booking_time']) ?></span>
                            </td>
                            <td style="font-weight:700;">$<?= number_format($booking['total_price'], 2) ?></td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($booking['status']) ?>">
                                    <?= htmlspecialchars($booking['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($booking['invoice_status'] === 'paid'): ?>
                                    <span class="badge badge-paid">Paid</span>
                                <?php else: ?>
                                    <span class="badge badge-unpaid">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($booking['invoice_id']): ?>
                                    <a href="../checkout.php?invoice_id=<?= $booking['invoice_id'] ?>" target="_blank" style="color:var(--primary);text-decoration:none;font-weight:600;">
                                        <?= htmlspecialchars($booking['invoice_number']) ?> &nbsp;<i class="fa-solid fa-up-right-from-square" style="font-size:0.75rem;"></i>
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--text-secondary);">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-group" style="justify-content: flex-end;">
                                    <?php if ($booking['status'] === 'pending'): ?>
                                        <a href="bookings.php?action=confirm&id=<?= $booking['id'] ?>" class="btn-admin btn-admin-success btn-admin-sm" title="Confirm Booking">
                                            <i class="fa-solid fa-check"></i> Confirm
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($booking['status'] !== 'cancelled'): ?>
                                        <a href="bookings.php?action=cancel&id=<?= $booking['id'] ?>" class="btn-admin btn-admin-outline btn-admin-sm" style="color:var(--danger); border-color:#fecaca;" title="Cancel Booking">
                                            <i class="fa-solid fa-ban"></i> Cancel
                                        </a>
                                    <?php endif; ?>

                                    <a href="bookings.php?action=delete&id=<?= $booking['id'] ?>" class="btn-admin btn-admin-danger btn-admin-sm" onclick="return confirm('Are you sure you want to delete this booking and all associated billing data? This cannot be undone.')" title="Delete Booking">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
