<?php
$pageTitle = 'Dashboard — Apollo Admin';
require_once __DIR__ . '/header.php';

try {
    // 1. Fetch total revenue (sum of paid invoices)
    $stmtRev = $pdo->prepare("SELECT SUM(amount) AS total_revenue FROM invoices WHERE status = 'paid'");
    $stmtRev->execute();
    $revenue = floatval($stmtRev->fetch()['total_revenue'] ?? 0);

    // 2. Fetch active bookings (pending & confirmed statuses)
    $stmtActive = $pdo->prepare("SELECT COUNT(*) AS active_count FROM bookings WHERE status IN ('pending', 'confirmed')");
    $stmtActive->execute();
    $activeBookings = intval($stmtActive->fetch()['active_count'] ?? 0);

    // 3. Fetch total bookings count
    $stmtTotalBookings = $pdo->prepare("SELECT COUNT(*) AS total_count FROM bookings");
    $stmtTotalBookings->execute();
    $totalBookings = intval($stmtTotalBookings->fetch()['total_count'] ?? 0);

    // 4. Fetch total services count
    $stmtServices = $pdo->prepare("SELECT COUNT(*) AS service_count FROM services");
    $stmtServices->execute();
    $totalServices = intval($stmtServices->fetch()['service_count'] ?? 0);

    // 5. Fetch last 10 bookings
    $stmtRecent = $pdo->prepare("
        SELECT b.*, i.id AS invoice_id, i.invoice_number, i.status AS invoice_status 
        FROM bookings b
        LEFT JOIN invoices i ON b.id = i.booking_id
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    $stmtRecent->execute();
    $recentBookings = $stmtRecent->fetchAll();

} catch (PDOException $e) {
    error_log('dashboard.php — ' . $e->getMessage());
    echo '<div style="background:#fee2e2;color:#b91c1c;padding:1.5rem;border-radius:8px;margin-bottom:1.5rem;">Database query error. See log.</div>';
    $revenue = $activeBookings = $totalBookings = $totalServices = 0;
    $recentBookings = [];
}
?>

<style>
    /* Overview Grid */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .metric-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.02);
    }
    .metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .metric-icon-revenue { background: #ecfdf5; color: #059669; }
    .metric-icon-active { background: #eff6ff; color: #2563eb; }
    .metric-icon-bookings { background: #faf5ff; color: #7c3aed; }
    .metric-icon-services { background: #fff7ed; color: #ea580c; }
    
    .metric-info h3 {
        font-size: 0.8rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    .metric-info p {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary-dark);
    }
</style>

<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-icon metric-icon-revenue">
            <i class="fa-solid fa-dollar-sign"></i>
        </div>
        <div class="metric-info">
            <h3>Total Revenue</h3>
            <p>$<?= number_format($revenue, 2) ?></p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon metric-icon-active">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="metric-info">
            <h3>Active Bookings</h3>
            <p><?= $activeBookings ?></p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon metric-icon-bookings">
            <i class="fa-solid fa-calendar-alt"></i>
        </div>
        <div class="metric-info">
            <h3>Total Bookings</h3>
            <p><?= $totalBookings ?></p>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon metric-icon-services">
            <i class="fa-solid fa-concierge-bell"></i>
        </div>
        <div class="metric-info">
            <h3>Services</h3>
            <p><?= $totalServices ?></p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <span>Recent Bookings</span>
        <a href="bookings.php" class="btn-admin btn-admin-outline btn-admin-sm">View All Bookings</a>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Service Type</th>
                    <th>Date &amp; Time</th>
                    <th>Price</th>
                    <th>Booking Status</th>
                    <th>Payment Status</th>
                    <th>Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentBookings)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 2rem;">No bookings found.</td>
                    </tr>
                <?php else: ?>
                    <?php 
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
                    foreach ($recentBookings as $booking): 
                        $serviceLabel = $servicesMap[$booking['service_type']] ?? htmlspecialchars($booking['service_type']);
                    ?>
                        <tr>
                            <td>#<?= $booking['id'] ?></td>
                            <td>
                                <strong style="display:block;"><?= htmlspecialchars($booking['customer_name']) ?></strong>
                                <span style="font-size:0.8rem;color:var(--text-secondary);"><?= htmlspecialchars($booking['customer_email']) ?></span>
                            </td>
                            <td><?= $serviceLabel ?></td>
                            <td>
                                <?= date('M j, Y', strtotime($booking['booking_date'])) ?><br>
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
                                    <a href="../checkout.php?invoice_id=<?= $booking['invoice_id'] ?>" target="_blank" style="color:var(--primary);text-decoration:none;font-weight:600;" title="View Invoice Page">
                                        <?= htmlspecialchars($booking['invoice_number']) ?> &nbsp;<i class="fa-solid fa-up-right-from-square" style="font-size:0.75rem;"></i>
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--text-secondary);">N/A</span>
                                <?php endif; ?>
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
