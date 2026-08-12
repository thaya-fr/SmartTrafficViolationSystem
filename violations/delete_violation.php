<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$violation_id = $_GET['id'] ?? '';
if (empty($violation_id)) { header('Location: view_violations.php?error=' . urlencode('Invalid violation ID.')); exit; }

try {
    // Check for linked payments
    $check = $pdo->prepare("SELECT COUNT(*) as count FROM payments WHERE violation_id = :id");
    $check->execute([':id' => $violation_id]);
    $count = $check->fetch()['count'];

    if ($count > 0) {
        header('Location: view_violations.php?error=' . urlencode("Cannot delete violation. Payment record exists. Delete the payment first."));
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM violations WHERE violation_id = :id");
    $stmt->execute([':id' => $violation_id]);

    header('Location: view_violations.php?success=' . urlencode('Violation deleted successfully.'));
} catch (PDOException $e) {
    error_log("Delete violation error: " . $e->getMessage());
    header('Location: view_violations.php?error=' . urlencode('Failed to delete violation.'));
}
exit;
