<?php
// Checkout Page - Apollo Cleaning Platform
require_once __DIR__ . '/api/db.php';

$invoiceId = intval($_GET['invoice_id'] ?? 0);

if ($invoiceId <= 0) {
    http_response_code(400);
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Invalid invoice ID.</h1></body></html>';
    exit;
}

// Fetch invoice + booking
$stmt = $pdo->prepare("
    SELECT i.*, b.customer_name, b.customer_email, b.customer_phone,
           b.service_type, b.booking_date, b.booking_time, b.total_price
    FROM invoices i
    JOIN bookings b ON i.booking_id = b.id
    WHERE i.id = :id
");
$stmt->execute([':id' => $invoiceId]);
$data = $stmt->fetch();

if (!$data) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>Not Found</title></head><body><h1>Invoice not found.</h1></body></html>';
    exit;
}

// If already paid, redirect to receipt
if ($data['status'] === 'paid') {
    header('Location: receipt.php?invoice_id=' . $invoiceId);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout – Invoice <?= htmlspecialchars($data['invoice_number']) ?> | Apollo Services</title>
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
            --danger: #dc2626;
        }

        body {
            font-family: 'Manrope', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
            line-height: 1.6;
        }

        /* ── Header ── */
        .checkout-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .checkout-header .brand {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            margin-bottom: .25rem;
        }
        .checkout-header .brand i { font-size: 1.3rem; }
        .checkout-header p {
            color: var(--text-secondary);
            font-size: .95rem;
        }

        /* ── Card ── */
        .checkout-card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.06);
            width: 100%;
            max-width: 820px;
            overflow: hidden;
        }

        .card-section {
            padding: 1.75rem 2rem;
        }
        .card-section + .card-section {
            border-top: 1px solid var(--border);
        }
        .card-section h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .card-section h2 i {
            font-size: .95rem;
            opacity: .8;
        }

        /* ── Info Grid ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem 2rem;
        }
        .info-item label {
            display: block;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-secondary);
            margin-bottom: .15rem;
        }
        .info-item span {
            font-size: .95rem;
            font-weight: 500;
            color: var(--text);
        }

        /* ── Amount Banner ── */
        .amount-banner {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            padding: 1.25rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .amount-banner .label {
            font-size: .95rem;
            font-weight: 500;
            opacity: .9;
        }
        .amount-banner .value {
            font-size: 1.75rem;
            font-weight: 800;
        }

        /* ── Form ── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-row.full {
            grid-template-columns: 1fr;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: .4rem;
            color: var(--text);
        }
        .form-group input {
            width: 100%;
            padding: .75rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: 'Manrope', sans-serif;
            font-size: .95rem;
            color: var(--text);
            background: var(--bg);
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(27,62,107,.12);
        }
        .form-group input.error {
            border-color: var(--danger);
        }
        .form-group .input-icon {
            position: relative;
        }
        .form-group .input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: .85rem;
        }
        .form-group .input-icon input {
            padding-left: 2.6rem;
        }

        /* ── Button ── */
        .pay-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            border: none;
            border-radius: 12px;
            font-family: 'Manrope', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            transition: transform .15s, box-shadow .2s;
            margin-top: .5rem;
        }
        .pay-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(27,62,107,.3);
        }
        .pay-btn:active { transform: translateY(0); }
        .pay-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .pay-btn .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2.5px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }
        .pay-btn.loading .spinner { display: block; }
        .pay-btn.loading .btn-text { display: none; }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Error Message ── */
        .form-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: var(--danger);
            padding: .75rem 1rem;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 500;
            margin-bottom: 1rem;
            display: none;
            align-items: center;
            gap: .5rem;
        }
        .form-error.visible { display: flex; }

        /* ── Secure Note ── */
        .secure-note {
            text-align: center;
            font-size: .8rem;
            color: var(--text-secondary);
            margin-top: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
        }
        .secure-note i { color: var(--success); }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            body { padding: 1rem .5rem; }
            .card-section { padding: 1.25rem 1.25rem; }
            .amount-banner { padding: 1rem 1.25rem; flex-direction: column; gap: .25rem; text-align: center; }
            .amount-banner .value { font-size: 1.5rem; }
            .info-grid { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="checkout-header">
        <a href="index.html" class="brand">
            <i class="fa-solid fa-broom"></i> Apollo Services
        </a>
        <p>Secure Checkout</p>
    </div>

    <!-- Main Card -->
    <div class="checkout-card">

        <!-- Invoice Summary -->
        <div class="card-section">
            <h2><i class="fa-solid fa-file-invoice"></i> Invoice Summary</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Invoice Number</label>
                    <span><?= htmlspecialchars($data['invoice_number']) ?></span>
                </div>
                <div class="info-item">
                    <label>Service</label>
                    <span><?= htmlspecialchars(ucwords(str_replace('-', ' ', $data['service_type']))) ?></span>
                </div>
                <div class="info-item">
                    <label>Booking Date</label>
                    <span><?= date('M j, Y', strtotime($data['booking_date'])) ?> at <?= htmlspecialchars($data['booking_time']) ?></span>
                </div>
                <div class="info-item">
                    <label>Due Date</label>
                    <span><?= date('M j, Y', strtotime($data['due_date'])) ?></span>
                </div>
            </div>
        </div>

        <!-- Customer Details -->
        <div class="card-section">
            <h2><i class="fa-solid fa-user"></i> Customer Details</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Name</label>
                    <span><?= htmlspecialchars($data['customer_name']) ?></span>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <span><?= htmlspecialchars($data['customer_email']) ?></span>
                </div>
                <div class="info-item">
                    <label>Phone</label>
                    <span><?= htmlspecialchars($data['customer_phone']) ?></span>
                </div>
            </div>
        </div>

        <!-- Amount -->
        <div class="amount-banner">
            <span class="label">Total Amount Due</span>
            <span class="value">$<?= number_format($data['amount'], 2) ?></span>
        </div>

        <!-- Payment Form -->
        <div class="card-section">
            <h2><i class="fa-solid fa-credit-card"></i> Payment Details</h2>

            <div class="form-error" id="formError">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span id="errorText"></span>
            </div>

            <form id="paymentForm" autocomplete="off" novalidate>
                <div class="form-row full">
                    <div class="form-group">
                        <label for="cardName">Cardholder Name</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" id="cardName" placeholder="Full name on card" required
                                   value="<?= htmlspecialchars($data['customer_name']) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="cardNumber">Card Number</label>
                        <div class="input-icon">
                            <i class="fa-brands fa-cc-visa"></i>
                            <input type="text" id="cardNumber" placeholder="1234  5678  9012  3456"
                                   maxlength="19" inputmode="numeric" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cardExpiry">Expiry Date</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-calendar"></i>
                            <input type="text" id="cardExpiry" placeholder="MM / YY" maxlength="7" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="cardCvv">CVV</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-lock"></i>
                            <input type="text" id="cardCvv" placeholder="•••" maxlength="4" inputmode="numeric" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="pay-btn" id="payBtn">
                    <span class="btn-text"><i class="fa-solid fa-shield-halved"></i> Pay $<?= number_format($data['amount'], 2) ?></span>
                    <span class="spinner"></span>
                </button>
            </form>

            <div class="secure-note">
                <i class="fa-solid fa-lock"></i>
                Simulated payment — no real charges will be made
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form      = document.getElementById('paymentForm');
    const payBtn    = document.getElementById('payBtn');
    const errorBox  = document.getElementById('formError');
    const errorText = document.getElementById('errorText');

    const cardNumberInput = document.getElementById('cardNumber');
    const cardExpiryInput = document.getElementById('cardExpiry');
    const cardCvvInput    = document.getElementById('cardCvv');
    const cardNameInput   = document.getElementById('cardName');

    // ── Format card number with spaces ──
    cardNumberInput.addEventListener('input', (e) => {
        let v = e.target.value.replace(/\D/g, '').substring(0, 16);
        e.target.value = v.replace(/(.{4})/g, '$1  ').trim();

        // Detect card type and switch icon
        const icon = e.target.closest('.input-icon').querySelector('i');
        if (v.startsWith('4'))      icon.className = 'fa-brands fa-cc-visa';
        else if (v.startsWith('5')) icon.className = 'fa-brands fa-cc-mastercard';
        else if (v.startsWith('3')) icon.className = 'fa-brands fa-cc-amex';
        else                        icon.className = 'fa-solid fa-credit-card';
    });

    // ── Format expiry ──
    cardExpiryInput.addEventListener('input', (e) => {
        let v = e.target.value.replace(/\D/g, '').substring(0, 4);
        if (v.length >= 3) v = v.substring(0,2) + ' / ' + v.substring(2);
        e.target.value = v;
    });

    // ── CVV digits only ──
    cardCvvInput.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
    });

    // ── Show error ──
    function showError(msg) {
        errorText.textContent = msg;
        errorBox.classList.add('visible');
    }
    function clearError() {
        errorBox.classList.remove('visible');
    }

    // ── Validation ──
    function validate() {
        clearError();
        document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

        const name   = cardNameInput.value.trim();
        const number = cardNumberInput.value.replace(/\D/g, '');
        const expiry = cardExpiryInput.value.replace(/\D/g, '');
        const cvv    = cardCvvInput.value.trim();

        if (!name) {
            cardNameInput.classList.add('error');
            showError('Please enter the cardholder name.');
            return false;
        }
        if (number.length < 13 || number.length > 16) {
            cardNumberInput.classList.add('error');
            showError('Please enter a valid card number (13–16 digits).');
            return false;
        }
        if (expiry.length !== 4) {
            cardExpiryInput.classList.add('error');
            showError('Please enter a valid expiry date (MM/YY).');
            return false;
        }
        const month = parseInt(expiry.substring(0,2), 10);
        if (month < 1 || month > 12) {
            cardExpiryInput.classList.add('error');
            showError('Expiry month must be between 01 and 12.');
            return false;
        }
        if (cvv.length < 3 || cvv.length > 4) {
            cardCvvInput.classList.add('error');
            showError('Please enter a valid CVV (3 or 4 digits).');
            return false;
        }

        return true;
    }

    // ── Submit ──
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!validate()) return;

        payBtn.classList.add('loading');
        payBtn.disabled = true;
        clearError();

        const transactionId = 'TXN-' + Date.now();

        try {
            const res = await fetch('api/pay.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    invoice_id:     <?= $invoiceId ?>,
                    payment_method: 'Credit Card',
                    transaction_id: transactionId,
                    amount:         <?= $data['amount'] ?>
                })
            });

            const result = await res.json();

            if (result.status === 'success') {
                window.location.href = 'receipt.php?invoice_id=<?= $invoiceId ?>';
            } else {
                showError(result.message || 'Payment failed. Please try again.');
                payBtn.classList.remove('loading');
                payBtn.disabled = false;
            }
        } catch (err) {
            showError('Network error. Please check your connection and try again.');
            payBtn.classList.remove('loading');
            payBtn.disabled = false;
        }
    });
});
</script>

</body>
</html>
