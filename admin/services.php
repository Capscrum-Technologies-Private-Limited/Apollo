<?php
$pageTitle = 'Manage Services — Apollo Admin';
require_once __DIR__ . '/header.php';

$message = '';
$messageType = '';

// Handle Create / Update Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    $serviceId = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['service_id'] ?? '');
    $label = trim($_POST['label'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $accent = trim($_POST['accent'] ?? 'pink');
    $description = trim($_POST['description'] ?? '');
    $basePrice = floatval($_POST['base_price'] ?? 0.00);

    if ($serviceId === '' || $label === '' || $category === '') {
        $message = "Please fill in all required fields (Service ID, Name, and Category).";
        $messageType = 'error';
    } else {
        try {
            if ($action === 'create') {
                // Verify uniqueness
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) AS count FROM services WHERE service_id = ?");
                $stmtCheck->execute([$serviceId]);
                if ($stmtCheck->fetch()['count'] > 0) {
                    throw new Exception("Service ID '{$serviceId}' is already in use.");
                }

                $stmt = $pdo->prepare("
                    INSERT INTO services (service_id, label, category, accent, description, base_price) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$serviceId, $label, $category, $accent, $description, $basePrice]);
                $message = "New service '{$label}' created successfully.";
                $messageType = 'success';
            } elseif ($action === 'update') {
                $stmt = $pdo->prepare("
                    UPDATE services 
                    SET label = ?, category = ?, accent = ?, description = ?, base_price = ? 
                    WHERE service_id = ?
                ");
                $stmt->execute([$label, $category, $accent, $description, $basePrice, $serviceId]);
                $message = "Service '{$label}' updated successfully.";
                $messageType = 'success';
            }
            
            // Redirect to prevent form resubmission
            header("Location: services.php?msg=" . urlencode($message) . "&msg_type=" . urlencode($messageType));
            exit;
        } catch (Exception $e) {
            $message = "Operation failed: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Handle Delete Operation
if (isset($_GET['delete'])) {
    $deleteId = trim($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE service_id = ?");
        $stmt->execute([$deleteId]);
        $message = "Service '{$deleteId}' deleted successfully.";
        $messageType = 'warning';
        header("Location: services.php?msg=" . urlencode($message) . "&msg_type=" . urlencode($messageType));
        exit;
    } catch (PDOException $e) {
        $message = "Failed to delete service: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Check for redirect message
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['msg_type'] ?? 'success';
}

// Fetch single service for editing
$editService = null;
if (isset($_GET['edit'])) {
    $editId = trim($_GET['edit']);
    $stmtEdit = $pdo->prepare("SELECT * FROM services WHERE service_id = ?");
    $stmtEdit->execute([$editId]);
    $editService = $stmtEdit->fetch();
}

// Fetch all services
try {
    $stmt = $pdo->prepare("SELECT * FROM services ORDER BY label ASC");
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('services.php fetch error — ' . $e->getMessage());
    $services = [];
    $message = "Failed to load services: " . $e->getMessage();
    $messageType = 'error';
}

$categoriesList = ['residential', 'commercial', 'exterior', 'ndis'];
$accentsList = ['pink', 'teal', 'lavender', 'ochre', 'peach', 'mint', 'coral'];
?>

<style>
    .services-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.5rem;
    }
    
    .form-control {
        margin-bottom: 1.25rem;
    }
    .form-control label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
    }
    .form-control input, 
    .form-control select, 
    .form-control textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
        outline: none;
        background: #fafafa;
        transition: border-color 0.2s, background 0.2s;
    }
    .form-control input:focus, 
    .form-control select:focus, 
    .form-control textarea:focus {
        border-color: var(--primary);
        background: var(--white);
    }
    .form-control select {
        height: 42px;
    }
    
    .alert-banner {
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        font-weight: 600;
    }
    .alert-banner-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .alert-banner-warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .alert-banner-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    @media (max-width: 991px) {
        .services-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php if ($message): ?>
    <div class="alert-banner alert-banner-<?= htmlspecialchars($messageType) ?>">
        <i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="services-layout">
    <!-- Services Table List -->
    <div class="card">
        <div class="card-title">
            <span>Dynamic Service Configurations</span>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service Details</th>
                        <th>Category</th>
                        <th>Accent</th>
                        <th>Base Price</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 3rem;">No services defined.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($service['service_id']) ?></code></td>
                                <td>
                                    <strong style="color:var(--primary-dark);"><?= htmlspecialchars($service['label']) ?></strong>
                                    <p style="font-size:0.8rem;color:var(--text-secondary);margin-top:0.2rem;max-width:400px;line-height:1.4;">
                                        <?= htmlspecialchars(substr($service['description'], 0, 100)) ?><?= strlen($service['description']) > 100 ? '...' : '' ?>
                                    </p>
                                </td>
                                <td>
                                    <span class="badge" style="background:#e0e7ff;color:#3730a3;font-size:0.7rem;">
                                        <?= htmlspecialchars(ucfirst($service['category'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="background:var(--white);border:1px solid var(--border);color:var(--text);font-size:0.7rem;text-transform:lowercase;display:inline-flex;align-items:center;gap:0.3rem;">
                                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--<?= $service['accent'] ?? 'pink' ?>);"></span>
                                        <?= htmlspecialchars($service['accent'] ?? 'pink') ?>
                                    </span>
                                </td>
                                <td style="font-weight:700;">$<?= number_format($service['base_price'], 2) ?></td>
                                <td style="text-align: right;">
                                    <div class="action-group" style="justify-content: flex-end;">
                                        <a href="services.php?edit=<?= urlencode($service['service_id']) ?>" class="btn-admin btn-admin-outline btn-admin-sm" title="Edit Service">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>
                                        <a href="services.php?delete=<?= urlencode($service['service_id']) ?>" class="btn-admin btn-admin-danger btn-admin-sm" onclick="return confirm('Are you sure you want to delete the service \'<?= htmlspecialchars($service['label']) ?>\'? This may break active website pages referring to it.')" title="Delete Service">
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

    <!-- Add/Edit Form -->
    <div class="card">
        <div class="card-title">
            <span><?= $editService ? 'Modify Service' : 'Add New Service' ?></span>
        </div>
        
        <form method="POST" action="services.php">
            <input type="hidden" name="action" value="<?= $editService ? 'update' : 'create' ?>">
            
            <div class="form-control">
                <label for="service_id">Service ID (Unique Code)</label>
                <input type="text" id="service_id" name="service_id" value="<?= htmlspecialchars($editService['service_id'] ?? '') ?>" <?= $editService ? 'readonly style="background:#e9e9e9;color:#555;"' : 'required' ?> placeholder="e.g. office, window">
                <?php if (!$editService): ?>
                    <span style="font-size:0.75rem;color:var(--text-secondary);">Only lowercase letters, numbers, hyphens, underscores.</span>
                <?php endif; ?>
            </div>

            <div class="form-control">
                <label for="label">Service Name (Label)</label>
                <input type="text" id="label" name="label" value="<?= htmlspecialchars($editService['label'] ?? '') ?>" required placeholder="e.g. Office Cleaning">
            </div>

            <div class="form-control">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categoriesList as $cat): ?>
                        <option value="<?= $cat ?>" <?= (isset($editService['category']) && $editService['category'] === $cat) ? 'selected' : '' ?>>
                            <?= ucfirst($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-control">
                <label for="accent">Accent Color (UI Badge)</label>
                <select id="accent" name="accent">
                    <?php foreach ($accentsList as $acc): ?>
                        <option value="<?= $acc ?>" <?= (isset($editService['accent']) && $editService['accent'] === $acc) ? 'selected' : '' ?>>
                            <?= ucfirst($acc) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-control">
                <label for="base_price">Base Price ($)</label>
                <input type="number" step="0.01" id="base_price" name="base_price" value="<?= htmlspecialchars($editService['base_price'] ?? '0.00') ?>" required>
            </div>

            <div class="form-control">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" placeholder="Service explanation..." required><?= htmlspecialchars($editService['description'] ?? '') ?></textarea>
            </div>

            <div style="display:flex;gap:0.5rem;margin-top:1.5rem;">
                <button type="submit" class="btn-admin btn-admin-primary" style="flex-grow:1;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Configuration
                </button>
                <?php if ($editService): ?>
                    <a href="services.php" class="btn-admin btn-admin-outline" style="justify-content:center;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
