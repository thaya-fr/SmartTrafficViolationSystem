<?php
/**
 * TrafficLens AI — Add Violation
 * Records a new traffic violation with cascading dropdowns and auto-fine.
 */
$page_title = 'Add Violation';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

try {
    $drivers = $pdo->query("SELECT driver_id, full_name, license_number FROM drivers ORDER BY full_name ASC")->fetchAll();
    $rules = $pdo->query("SELECT rule_id, violation_type, fine_amount FROM violation_rules ORDER BY violation_type ASC")->fetchAll();
} catch (PDOException $e) {
    $drivers = [];
    $rules = [];
}
?>

<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <h3 class="card-title">Record Violation</h3>
        <a href="view_violations.php" class="btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <form action="save_violation.php" method="POST" id="addViolationForm" onsubmit="return validateForm('addViolationForm')">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="driver_id">Driver <span class="required">*</span></label>
                <select id="driver_id" name="driver_id" class="form-select" required>
                    <option value="">Select driver</option>
                    <?php foreach ($drivers as $d): ?>
                    <option value="<?php echo $d['driver_id']; ?>"><?php echo htmlspecialchars($d['full_name'] . ' — ' . $d['license_number']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="vehicle_id">Vehicle <span class="required">*</span></label>
                <select id="vehicle_id" name="vehicle_id" class="form-select" required>
                    <option value="">Select driver first</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="rule_id">Violation Type <span class="required">*</span></label>
                <select id="rule_id" name="rule_id" class="form-select" required>
                    <option value="">Select violation type</option>
                    <?php foreach ($rules as $r): ?>
                    <option value="<?php echo $r['rule_id']; ?>" data-fine="<?php echo $r['fine_amount']; ?>">
                        <?php echo htmlspecialchars($r['violation_type']); ?> — ₹<?php echo number_format($r['fine_amount'], 2); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Fine Amount</label>
                <div class="form-input" style="display: flex; align-items: center; color: var(--color-pulse-green); font-size: var(--text-heading-sm);" id="fine_display">₹0.00</div>
            </div>

            <div class="form-group full-width">
                <label class="form-label" for="location">Location <span class="required">*</span></label>
                <input type="text" id="location" name="location" class="form-input" placeholder="e.g. MG Road, Near Signal No. 3" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="officer_name">Officer Name <span class="required">*</span></label>
                <input type="text" id="officer_name" name="officer_name" class="form-input" placeholder="e.g. Officer Sharma" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="violation_date">Date <span class="required">*</span></label>
                <input type="date" id="violation_date" name="violation_date" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="violation_time">Time <span class="required">*</span></label>
                <input type="time" id="violation_time" name="violation_time" class="form-input" value="<?php echo date('H:i'); ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Save Violation</button>
            <a href="view_violations.php" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php
$page_scripts = '
<script>
    // Cascading dropdown: Driver → Vehicle
    loadVehiclesByDriver("driver_id", "vehicle_id");

    // Auto-fine calculation from rule selection
    document.getElementById("rule_id").addEventListener("change", function() {
        const selected = this.options[this.selectedIndex];
        const fine = selected.dataset.fine || "0";
        document.getElementById("fine_display").textContent = "₹" + parseFloat(fine).toFixed(2);
    });
</script>';

require_once __DIR__ . '/../includes/footer.php';
?>
