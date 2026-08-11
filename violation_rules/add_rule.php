<?php
$page_title = 'Add Violation Rule';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <h3 class="card-title">Add Violation Rule</h3>
        <a href="view_rules.php" class="btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <form action="save_rule.php" method="POST" id="addRuleForm" onsubmit="return validateForm('addRuleForm')">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="violation_type">Violation Type <span class="required">*</span></label>
                <input type="text" id="violation_type" name="violation_type" class="form-input" placeholder="e.g. Helmet Not Worn" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="fine_amount">Fine Amount (₹) <span class="required">*</span></label>
                <input type="number" id="fine_amount" name="fine_amount" class="form-input" placeholder="e.g. 500" min="0" step="0.01" required>
            </div>
            <div class="form-group full-width">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-textarea" placeholder="Describe the violation rule" rows="3"></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Save Rule</button>
            <a href="view_rules.php" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
