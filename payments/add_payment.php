<?php
/**
 * TrafficLens AI — Add Payment
 * Shows only pending violations and processes payment.
 */
$page_title = 'Add Payment';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

try {
    // Only show violations with Pending status
    $stmt = $pdo->query("
        SELECT v.violation_id, d.full_name, vh.vehicle_number, vr.violation_type, vr.fine_amount, v.violation_date
        FROM violations v
        JOIN drivers d ON v.driver_id = d.driver_id
        JOIN vehicles vh ON v.vehicle_id = vh.vehicle_id
        JOIN violation_rules vr ON v.rule_id = vr.rule_id
        WHERE v.payment_status = 'Pending'
        ORDER BY v.violation_date DESC
    ");
    $pending_violations = $stmt->fetchAll();
} catch (PDOException $e) {
    $pending_violations = [];
}
?>

<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <h3 class="card-title">Process Payment</h3>
        <a href="view_payments.php" class="btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <?php if (count($pending_violations) === 0): ?>
    <div class="empty-state">
        <i class="fas fa-check-circle" style="color: var(--color-pulse-green);"></i>
        <p class="empty-title">No pending violations</p>
        <p class="empty-text">All violations have been paid.</p>
    </div>
    <?php else: ?>
    <form action="save_payment.php" method="POST" id="addPaymentForm" onsubmit="return validateForm('addPaymentForm')">
        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label" for="violation_id">Select Violation <span class="required">*</span></label>
                <select id="violation_id" name="violation_id" class="form-select" required>
                    <option value="">Select pending violation</option>
                    <?php foreach ($pending_violations as $pv): ?>
                    <option value="<?php echo $pv['violation_id']; ?>" data-fine="<?php echo $pv['fine_amount']; ?>">
                        <?php echo htmlspecialchars($pv['full_name'] . ' | ' . $pv['vehicle_number'] . ' | ' . $pv['violation_type'] . ' | ₹' . number_format($pv['fine_amount'], 2) . ' | ' . date('d M Y', strtotime($pv['violation_date']))); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Fine Amount</label>
                <div class="form-input" style="display:flex;align-items:center;color:var(--color-pulse-green);font-size:var(--text-heading-sm);" id="fine_display">₹0.00</div>
                <input type="hidden" name="amount" id="amount" value="">
            </div>

            <div class="form-group">
                <label class="form-label" for="payment_method">Payment Method <span class="required">*</span></label>
                <select id="payment_method" name="payment_method" class="form-select" required>
                    <option value="">Select method</option>
                    <option value="Cash">Cash</option>
                    <option value="UPI">UPI</option>
                    <option value="Card">Card</option>
                    <option value="Net Banking">Net Banking</option>
                    <option value="Cheque">Cheque</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="transaction_id">Transaction ID <span class="required">*</span></label>
                <input type="text" id="transaction_id" name="transaction_id" class="form-input" placeholder="e.g. TXN123456789" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="payment_date">Payment Date <span class="required">*</span></label>
                <input type="date" id="payment_date" name="payment_date" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Process Payment</button>
            <a href="view_payments.php" class="btn-ghost">Cancel</a>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php
$page_scripts = '
<script>
    document.getElementById("violation_id")?.addEventListener("change", function() {
        const selected = this.options[this.selectedIndex];
        const fine = selected.dataset.fine || "0";
        document.getElementById("fine_display").textContent = "₹" + parseFloat(fine).toFixed(2);
        document.getElementById("amount").value = fine;
    });
</script>';
require_once __DIR__ . '/../includes/footer.php';
?>
