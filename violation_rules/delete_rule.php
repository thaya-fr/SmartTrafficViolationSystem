<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$rule_id = $_GET['id'] ?? '';
if (empty($rule_id)) { header('Location: view_rules.php?error=' . urlencode('Invalid rule ID.')); exit; }

try {
    $check = $pdo->prepare("SELECT COUNT(*) as count FROM violations WHERE rule_id = :id");
    $check->execute([':id' => $rule_id]);
    $count = $check->fetch()['count'];

    if ($count > 0) {
        header('Location: view_rules.php?error=' . urlencode("Cannot delete rule. {$count} violation(s) use this rule."));
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM violation_rules WHERE rule_id = :id");
    $stmt->execute([':id' => $rule_id]);

    header('Location: view_rules.php?success=' . urlencode('Rule deleted successfully.'));
} catch (PDOException $e) {
    error_log("Delete rule error: " . $e->getMessage());
    header('Location: view_rules.php?error=' . urlencode('Failed to delete rule.'));
}
exit;
