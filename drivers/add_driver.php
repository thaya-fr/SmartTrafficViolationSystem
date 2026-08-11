<?php
/**
 * TrafficLens AI — Add Driver
 * Form to register a new driver.
 */
$page_title = 'Add Driver';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <h3 class="card-title">Register New Driver</h3>
        <a href="view_drivers.php" class="btn-ghost btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <form action="save_driver.php" method="POST" id="addDriverForm" onsubmit="return validateForm('addDriverForm')">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" class="form-input" placeholder="Enter full name" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="license_number">License Number <span class="required">*</span></label>
                <input type="text" id="license_number" name="license_number" class="form-input" placeholder="e.g. DL-1420110012345" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" class="form-input" placeholder="e.g. +91 98765 43210" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="e.g. driver@email.com">
            </div>

            <div class="form-group full-width">
                <label class="form-label" for="address">Address</label>
                <textarea id="address" name="address" class="form-textarea" placeholder="Enter residential address" rows="3"></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <i class="fas fa-check"></i> Save Driver
            </button>
            <a href="view_drivers.php" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
