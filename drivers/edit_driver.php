<?php
/**
 * TrafficLens AI — Edit Driver
 * Pre-populated form to update driver details.
 */
$page_title = 'Edit Driver';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$driver_id = $_GET['id'] ?? '';

if (empty($driver_id)) {
    header('Location: view_drivers.php?error=' . urlencode('Invalid driver ID.'));
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM drivers WHERE driver_id = :id LIMIT 1");
    $stmt->execute([':id' => $driver_id]);
    $driver = $stmt->fetch();

    if (!$driver) {
        header('Location: view_drivers.php?error=' . urlencode('Driver not found.'));
        exit;
    }
} catch (PDOException $e) {
    error_log("Edit driver error: " . $e->getMessage());
    header('Location: view_drivers.php?error=' . urlencode('Error loading driver.'));
    exit;
}
?>

<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <h3 class="card-title">Edit Driver</h3>
        <a href="view_drivers.php" class="btn-ghost btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <form action="update_driver.php" method="POST" id="editDriverForm" onsubmit="return validateForm('editDriverForm')">
        <input type="hidden" name="driver_id" value="<?php echo htmlspecialchars($driver['driver_id']); ?>">

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" class="form-input" value="<?php echo htmlspecialchars($driver['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="license_number">License Number <span class="required">*</span></label>
                <input type="text" id="license_number" name="license_number" class="form-input" value="<?php echo htmlspecialchars($driver['license_number']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($driver['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($driver['email'] ?? ''); ?>">
            </div>

            <div class="form-group full-width">
                <label class="form-label" for="address">Address</label>
                <textarea id="address" name="address" class="form-textarea" rows="3"><?php echo htmlspecialchars($driver['address'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <i class="fas fa-check"></i> Update Driver
            </button>
            <a href="view_drivers.php" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
