<?php
$page_title = 'Edit Payment';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$payment_id = $_GET['id'] ?? '';
if (empty($payment_id)) { header('Location: view_payments.php?error=' . urlencode('Invalid payment ID.')); exit; }

try {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = :id LIMIT 1");
    $stmt->execute([':id' => $payment_id]);
    $payment = $stmt->fetch();
    if (!$payment) { header('Location: view_payments.php?error=' . urlencode('Payment not found.')); exit; }
} catch (PDOException $e) {
    header('Location: view_payments.php?error=' . urlencode('Error loading payment.'));
    exit;
}
?>

<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <h3 class="card-title">Edit Payment</h3>
        <a href="view_payments.php" class="btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <form action="update_payment.php" method="POST" id="editPaymentForm" onsubmit="return validateForm('editPaymentForm')">
        <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars($payment['payment_id']); ?>">
        <input type="hidden" name="violation_id" value="<?php echo htmlspecialchars($payment['violation_id']); ?>">

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="amount">Amount (₹) <span class="required">*</span></label>
                <input type="number" id="amount" name="amount" class="form-input" value="<?php echo $payment['amount']; ?>" min="0" step="0.01" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="payment_method">Payment Method <span class="required">*</span></label>
                <select id="payment_method" name="payment_method" class="form-select" required>
                    <?php foreach (['Cash','UPI','Card','Net Banking','Cheque'] as $method): ?>
                    <option value="<?php echo $method; ?>" <?php echo ($payment['payment_method'] === $method) ? 'selected' : ''; ?>><?php echo $method; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="transaction_id">Transaction ID <span class="required">*</span></label>
                <input type="text" id="transaction_id" name="transaction_id" class="form-input" value="<?php echo htmlspecialchars($payment['transaction_id']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="payment_date">Payment Date <span class="required">*</span></label>
                <input type="date" id="payment_date" name="payment_date" class="form-input" value="<?php echo $payment['payment_date']; ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Update Payment</button>
            <a href="view_payments.php" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
