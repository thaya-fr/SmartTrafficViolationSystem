<?php
/**
 * TrafficLens AI — Delete Payment
 * Deletes payment and reverts violation status to 'Pending'.
 */
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$payment_id = $_GET['id'] ?? '';
if (empty($payment_id)) { header('Location: view_payments.php?error=' . urlencode('Invalid payment ID.')); exit; }

try {
    // Get violation_id before deleting
    $stmt = $pdo->prepare("SELECT violation_id FROM payments WHERE payment_id = :id LIMIT 1");
    $stmt->execute([':id' => $payment_id]);
    $payment = $stmt->fetch();

    if (!$payment) {
        header('Location: view_payments.php?error=' . urlencode('Payment not found.'));
        exit;
    }

    // Transaction: delete payment + revert violation status
    $pdo->beginTransaction();

    $del = $pdo->prepare("DELETE FROM payments WHERE payment_id = :id");
    $del->execute([':id' => $payment_id]);

    // Revert violation status to Pending
    $update = $pdo->prepare("UPDATE violations SET payment_status = 'Pending' WHERE violation_id = :vid");
    $update->execute([':vid' => $payment['violation_id']]);

    $pdo->commit();

    header('Location: view_payments.php?success=' . urlencode('Payment deleted. Violation status reverted to Pending.'));
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Delete payment error: " . $e->getMessage());
    header('Location: view_payments.php?error=' . urlencode('Failed to delete payment.'));
}
exit;
