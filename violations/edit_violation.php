<?php
$page_title = 'Edit Violation';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$violation_id = $_GET['id'] ?? '';
if (empty($violation_id)) { header('Location: view_violations.php?error=' . urlencode('Invalid violation ID.')); exit; }

try {
    $stmt = $pdo->prepare("SELECT * FROM violations WHERE violation_id = :id LIMIT 1");
    $stmt->execute([':id' => $violation_id]);
    $violation = $stmt->fetch();
    if (!$violation) { header('Location: view_violations.php?error=' . urlencode('Violation not found.')); exit; }

    $drivers = $pdo->query("SELECT driver_id, full_name, license_number FROM drivers ORDER BY full_name")->fetchAll();
    $vehicles = $pdo->query("SELECT vehicle_id, vehicle_number, vehicle_type, manufacturer, model FROM vehicles ORDER BY vehicle_number")->fetchAll();
    $rules = $pdo->query("SELECT rule_id, violation_type, fine_amount FROM violation_rules ORDER BY violation_type")->fetchAll();
} catch (PDOException $e) {
    header('Location: view_violations.php?error=' . urlencode('Error loading violation.'));
    exit;
}
?>

<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <h3 class="card-title">Edit Violation</h3>
        <a href="view_violations.php" class="btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <form action="update_violation.php" method="POST" id="editViolationForm" onsubmit="return validateForm('editViolationForm')">
        <input type="hidden" name="violation_id" value="<?php echo htmlspecialchars($violation['violation_id']); ?>">

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="driver_id">Driver <span class="required">*</span></label>
                <select id="driver_id" name="driver_id" class="form-select" required>
                    <?php foreach ($drivers as $d): ?>
                    <option value="<?php echo $d['driver_id']; ?>" <?php echo ($d['driver_id'] === $violation['driver_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($d['full_name'] . ' — ' . $d['license_number']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="vehicle_id">Vehicle <span class="required">*</span></label>
                <select id="vehicle_id" name="vehicle_id" class="form-select" required>
                    <?php foreach ($vehicles as $v): ?>
                    <option value="<?php echo $v['vehicle_id']; ?>" <?php echo ($v['vehicle_id'] === $violation['vehicle_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($v['vehicle_number'] . ' — ' . $v['vehicle_type'] . ' ' . $v['manufacturer'] . ' ' . $v['model']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="rule_id">Violation Type <span class="required">*</span></label>
                <select id="rule_id" name="rule_id" class="form-select" required>
                    <?php foreach ($rules as $r): ?>
                    <option value="<?php echo $r['rule_id']; ?>" data-fine="<?php echo $r['fine_amount']; ?>" <?php echo ($r['rule_id'] === $violation['rule_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($r['violation_type']); ?> — ₹<?php echo number_format($r['fine_amount'], 2); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Fine Amount</label>
                <div class="form-input" style="display:flex;align-items:center;color:var(--color-pulse-green);font-size:var(--text-heading-sm);" id="fine_display">
                    <?php 
                    foreach ($rules as $r) {
                        if ($r['rule_id'] === $violation['rule_id']) {
                            echo '₹' . number_format($r['fine_amount'], 2);
                            break;
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="form-group full-width">
                <label class="form-label" for="location">Location <span class="required">*</span></label>
                <input type="text" id="location" name="location" class="form-input" value="<?php echo htmlspecialchars($violation['location']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="officer_name">Officer Name <span class="required">*</span></label>
                <input type="text" id="officer_name" name="officer_name" class="form-input" value="<?php echo htmlspecialchars($violation['officer_name']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="violation_date">Date <span class="required">*</span></label>
                <input type="date" id="violation_date" name="violation_date" class="form-input" value="<?php echo $violation['violation_date']; ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="violation_time">Time <span class="required">*</span></label>
                <input type="time" id="violation_time" name="violation_time" class="form-input" value="<?php echo substr($violation['violation_time'], 0, 5); ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Update Violation</button>
            <a href="view_violations.php" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php
$page_scripts = '<script>
    document.getElementById("rule_id").addEventListener("change", function() {
        const selected = this.options[this.selectedIndex];
        const fine = selected.dataset.fine || "0";
        document.getElementById("fine_display").textContent = "₹" + parseFloat(fine).toFixed(2);
    });
</script>';
require_once __DIR__ . '/../includes/footer.php';
?>
