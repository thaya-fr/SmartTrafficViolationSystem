<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: view_payments.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$payment_id     = $_POST['payment_id'] ?? '';
$amount         = $_POST['amount'] ?? '';
$payment_method = trim($_POST['payment_method'] ?? '');
$transaction_id = trim($_POST['transaction_id'] ?? '');
$payment_date   = $_POST['payment_date'] ?? '';

if (empty($payment_id) || empty($amount) || empty($payment_method) || empty($transaction_id) || empty($payment_date)) {
    header('Location: edit_payment.php?id=' . urlencode($payment_id) . '&error=' . urlencode('Required fields missing.'));
    exit;
}

try {
    $check = $pdo->prepare("SELECT payment_id FROM payments WHERE transaction_id = :tid AND payment_id != :id");
    $check->execute([':tid' => $transaction_id, ':id' => $payment_id]);
    if ($check->fetch()) {
        header('Location: edit_payment.php?id=' . urlencode($payment_id) . '&error=' . urlencode('Transaction ID already in use.'));
        exit;
    }

    $stmt = $pdo->prepare("UPDATE payments SET amount = :amount, payment_method = :method, transaction_id = :tid, payment_date = :pdate WHERE payment_id = :id");
    $stmt->execute([':amount' => $amount, ':method' => $payment_method, ':tid' => $transaction_id, ':pdate' => $payment_date, ':id' => $payment_id]);

    header('Location: view_payments.php?success=' . urlencode('Payment updated successfully.'));
} catch (PDOException $e) {
    error_log("Update payment error: " . $e->getMessage());
    header('Location: edit_payment.php?id=' . urlencode($payment_id) . '&error=' . urlencode('Failed to update payment.'));
}
exit;
