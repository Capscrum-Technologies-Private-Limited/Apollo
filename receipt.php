<?php
// Receipt Page - Apollo Cleaning Platform
require_once __DIR__ . '/api/db.php';

$invoiceId = intval($_GET['invoice_id'] ?? 0);

if ($invoiceId <= 0) {
    http_response_code(400);
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Invalid invoice ID.</h1></body></html>';
    exit;
}

// Fetch invoice + booking details
$stmt = $pdo->prepare("
    SELECT i.*, b.customer_name, b.customer_email, b.customer_phone,
           b.service_type, b.booking_date, b.booking_time, b.total_price
    FROM invoices i
    JOIN bookings b ON i.booking_id = b.id
    WHERE i.id = :id
");
$stmt->execute([':id' => $invoiceId]);
$invoice = $stmt->fetch();

if (!$invoice) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>Not Found</title></head><body><h1>Invoice not found.</h1></body></html>';
    exit;
}

// Fetch payment details
$stmtPayment = $pdo->prepare("
    SELECT * FROM payments 
    WHERE invoice_id = :invoice_id AND status = 'completed'
    ORDER BY created_at DESC LIMIT 1
");
$stmtPayment->execute([':invoice_id' => $invoiceId]);
$payment = $stmtPayment->fetch();

// Format service name nicely
$servicesList = [
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
$serviceName = $servicesList[$invoice['service_type']] ?? htmlspecialchars($invoice['service_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt – Invoice <?= htmlspecialchars($invoice['invoice_number']) ?> | Apollo Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #1B3E6B;
            --primary-dark: #150F38;
            --bg: #f7f7f7;
            --text: #1a1a1a;
            --text-secondary: #636363;
            --border: #e8e8e8;
            --white: #ffffff;
            --success: #16a34a;
            --success-light: #f0fdf4;
            --success-border: #bbf7d0;
        }

        body {
            font-family: 'Manrope', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 3rem 1rem;
            line-height: 1.6;
        }

        /* ── Header ── */
        .receipt-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .receipt-header .brand {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            margin-bottom: .25rem;
        }
        .receipt-header p {
            color: var(--text-secondary);
            font-size: .95rem;
        }

        /* ── Card ── */
        .receipt-card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.06);
            width: 100%;
            max-width: 680px;
            padding: 2.5rem;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        /* ── Status Badge ── */
        .status-badge-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.35rem 0.8rem;
            border-radius: 9999px;
            letter-spacing: 0.05em;
        }
        .badge-paid {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid var(--success-border);
        }
        .badge-unpaid {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* ── Details Grid ── */
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .detail-group h3 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            margin-bottom: 0.4rem;
        }
        .detail-group p {
            font-size: 0.95rem;
            font-weight: 600;
        }

        /* ── Items Table ── */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
        }
        .invoice-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--border);
        }
        .invoice-table td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
        }
        .invoice-table td.amount-col {
            text-align: right;
            font-weight: 700;
        }
        .invoice-table th.amount-col {
            text-align: right;
        }

        /* ── Summary ── */
        .summary-block {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
            padding-top: 1rem;
            margin-bottom: 2rem;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 280px;
            font-size: 0.95rem;
        }
        .summary-row.total {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary);
            border-top: 2px solid var(--primary);
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }

        /* ── Actions ── */
        .receipt-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            width: 100%;
            max-width: 680px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            height: 48px;
            padding: 0 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }
        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: var(--white);
            color: var(--text);
            border: 1px solid var(--border);
        }
        .btn-secondary:hover {
            background: #fafafa;
            border-color: #d0d0d0;
            transform: translateY(-1px);
        }

        /* ── Print Styles ── */
        @media print {
            body {
                background: var(--white) !important;
                color: #000000 !important;
                padding: 0 !important;
                min-height: auto !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .receipt-actions, .receipt-header p {
                display: none !important;
            }
            .badge {
                border: 1px solid #000000 !important;
                color: #000000 !important;
                background: none !important;
            }
            .summary-row.total {
                color: #000000 !important;
                border-top: 2px solid #000000 !important;
            }
        }

        @media (max-width: 640px) {
            .details-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .receipt-card {
                padding: 1.5rem;
            }
            .receipt-actions {
                flex-direction: column;
            }
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <header class="receipt-header">
        <a href="index.html" class="brand">
            <i class="fa-solid fa-circle-nodes"></i>
            <span>APOLLO SERVICES</span>
        </a>
        <p>Thank you for choosing Apollo Services. Your booking is confirmed.</p>
    </header>

    <main class="receipt-card">
        <div class="status-badge-container">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 700;">Receipt &amp; Confirmation</h2>
                <p style="font-size: 0.85rem; color: var(--text-secondary);">Invoice No: <?= htmlspecialchars($invoice['invoice_number']) ?></p>
            </div>
            <div>
                <?php if ($invoice['status'] === 'paid'): ?>
                    <span class="badge badge-paid">
                        <i class="fa-solid fa-circle-check"></i> Paid
                    </span>
                <?php else: ?>
                    <span class="badge badge-unpaid">
                        <i class="fa-solid fa-circle-xmark"></i> Unpaid
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="details-grid">
            <div class="detail-group">
                <h3>Billed To</h3>
                <p><?= htmlspecialchars($invoice['customer_name']) ?></p>
                <p style="font-size: 0.9rem; font-weight: 400; color: var(--text-secondary);"><?= htmlspecialchars($invoice['customer_email']) ?></p>
                <p style="font-size: 0.9rem; font-weight: 400; color: var(--text-secondary);"><?= htmlspecialchars($invoice['customer_phone']) ?></p>
            </div>
            <div class="detail-group">
                <h3>Booking Schedule</h3>
                <p><?= date('F j, Y', strtotime($invoice['booking_date'])) ?></p>
                <p style="font-size: 0.9rem; font-weight: 400; color: var(--text-secondary);">Time: <?= htmlspecialchars($invoice['booking_time']) ?></p>
            </div>
            
            <?php if ($payment): ?>
                <div class="detail-group">
                    <h3>Payment Details</h3>
                    <p><?= htmlspecialchars($payment['payment_method']) ?></p>
                    <p style="font-size: 0.9rem; font-weight: 400; color: var(--text-secondary);">Transaction: <?= htmlspecialchars($payment['transaction_id']) ?></p>
                </div>
                <div class="detail-group">
                    <h3>Payment Date</h3>
                    <p><?= date('F j, Y, g:i a', strtotime($payment['created_at'])) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Service Description</th>
                    <th class="amount-col">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong style="display: block; font-weight: 700; color: var(--primary);"><?= $serviceName ?></strong>
                        <span style="font-size: 0.85rem; color: var(--text-secondary);">Professional quality service standard cleaning.</span>
                    </td>
                    <td class="amount-col">$<?= number_format($invoice['amount'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="summary-block">
            <div class="summary-row">
                <span style="color: var(--text-secondary);">Subtotal:</span>
                <span style="font-weight: 600;">$<?= number_format($invoice['amount'], 2) ?></span>
            </div>
            <div class="summary-row">
                <span style="color: var(--text-secondary);">GST (10% Inc.):</span>
                <span style="font-weight: 600;">$<?= number_format($invoice['amount'] * 0.1, 2) ?></span>
            </div>
            <div class="summary-row total">
                <span>Total Paid:</span>
                <span>$<?= number_format($invoice['amount'], 2) ?></span>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border); padding-top: 1.5rem; text-align: center;">
            <p style="font-size: 0.85rem; color: var(--text-secondary);">
                Apollo Support Services Pty Ltd. If you have any questions, please contact support@apolloservices.com.
            </p>
        </div>
    </main>

    <div class="receipt-actions">
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fa-solid fa-print"></i> Print Receipt
        </button>
        <a href="index.html" class="btn btn-primary">
            <i class="fa-solid fa-house"></i> Back to Home
        </a>
    </div>

</body>
</html>
