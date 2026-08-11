<?php
$page_title = 'Edit Violation Rule';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$rule_id = $_GET['id'] ?? '';
if (empty($rule_id)) { header('Location: view_rules.php?error=' . urlencode('Invalid rule ID.')); exit; }

try {
    $stmt = $pdo->prepare("SELECT * FROM violation_rules WHERE rule_id = :id LIMIT 1");
    $stmt->execute([':id' => $rule_id]);
    $rule = $stmt->fetch();
    if (!$rule) { header('Location: view_rules.php?error=' . urlencode('Rule not found.')); exit; }
} catch (PDOException $e) {
    header('Location: view_rules.php?error=' . urlencode('Error loading rule.'));
    exit;
}
?>
<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <h3 class="card-title">Edit Violation Rule</h3>
        <a href="view_rules.php" class="btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <form action="update_rule.php" method="POST" id="editRuleForm" onsubmit="return validateForm('editRuleForm')">
        <input type="hidden" name="rule_id" value="<?php echo htmlspecialchars($rule['rule_id']); ?>">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="violation_type">Violation Type <span class="required">*</span></label>
                <input type="text" id="violation_type" name="violation_type" class="form-input" value="<?php echo htmlspecialchars($rule['violation_type']); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="fine_amount">Fine Amount (₹) <span class="required">*</span></label>
                <input type="number" id="fine_amount" name="fine_amount" class="form-input" value="<?php echo $rule['fine_amount']; ?>" min="0" step="0.01" required>
            </div>
            <div class="form-group full-width">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-textarea" rows="3"><?php echo htmlspecialchars($rule['description'] ?? ''); ?></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Update Rule</button>
            <a href="view_rules.php" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
