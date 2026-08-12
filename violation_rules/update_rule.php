<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: view_rules.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$rule_id        = $_POST['rule_id'] ?? '';
$violation_type = trim($_POST['violation_type'] ?? '');
$fine_amount    = $_POST['fine_amount'] ?? '';
$description    = trim($_POST['description'] ?? '') ?: null;

if (empty($rule_id) || empty($violation_type) || $fine_amount === '') {
    header('Location: edit_rule.php?id=' . urlencode($rule_id) . '&error=' . urlencode('Required fields missing.'));
    exit;
}

try {
    $check = $pdo->prepare("SELECT rule_id FROM violation_rules WHERE violation_type = :type AND rule_id != :id");
    $check->execute([':type' => $violation_type, ':id' => $rule_id]);
    if ($check->fetch()) {
        header('Location: edit_rule.php?id=' . urlencode($rule_id) . '&error=' . urlencode('Violation type already exists.'));
        exit;
    }

    $stmt = $pdo->prepare("UPDATE violation_rules SET violation_type = :type, fine_amount = :amount, description = :desc WHERE rule_id = :id");
    $stmt->execute([':type' => $violation_type, ':amount' => $fine_amount, ':desc' => $description, ':id' => $rule_id]);

    header('Location: view_rules.php?success=' . urlencode('Rule updated successfully.'));
} catch (PDOException $e) {
    error_log("Update rule error: " . $e->getMessage());
    header('Location: edit_rule.php?id=' . urlencode($rule_id) . '&error=' . urlencode('Failed to update rule.'));
}
exit;
