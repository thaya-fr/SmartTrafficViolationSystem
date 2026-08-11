<?php
/**
 * TrafficLens AI — Save Payment
 * Inserts payment and updates violation status to 'Paid' in a transaction.
 */
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: view_payments.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$violation_id   = $_POST['violation_id'] ?? '';
$amount         = $_POST['amount'] ?? '';
$payment_method = trim($_POST['payment_method'] ?? '');
$transaction_id = trim($_POST['transaction_id'] ?? '');
$payment_date   = $_POST['payment_date'] ?? '';

if (empty($violation_id) || empty($amount) || empty($payment_method) || empty($transaction_id) || empty($payment_date)) {
    header('Location: add_payment.php?error=' . urlencode('All fields are required.'));
    exit;
}

try {
    // Check duplicate transaction ID
    $check = $pdo->prepare("SELECT payment_id FROM payments WHERE transaction_id = :tid");
    $check->execute([':tid' => $transaction_id]);
    if ($check->fetch()) {
        header('Location: add_payment.php?error=' . urlencode('Transaction ID already exists.'));
        exit;
    }

    // Transaction: insert payment + update violation status
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO payments (violation_id, amount, payment_method, transaction_id, payment_date, payment_status)
        VALUES (:violation_id, :amount, :payment_method, :transaction_id, :payment_date, 'Paid')
    ");
    $stmt->execute([
        ':violation_id'   => $violation_id,
        ':amount'         => $amount,
        ':payment_method' => $payment_method,
        ':transaction_id' => $transaction_id,
        ':payment_date'   => $payment_date,
    ]);

    // Update violation status to Paid
    $update = $pdo->prepare("UPDATE violations SET payment_status = 'Paid' WHERE violation_id = :id");
    $update->execute([':id' => $violation_id]);

    $pdo->commit();

    header('Location: view_payments.php?success=' . urlencode('Payment processed successfully.'));
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Save payment error: " . $e->getMessage());
    header('Location: add_payment.php?error=' . urlencode('Failed to process payment.'));
}
exit;
