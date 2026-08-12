<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: view_rules.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$violation_type = trim($_POST['violation_type'] ?? '');
$fine_amount    = $_POST['fine_amount'] ?? '';
$description    = trim($_POST['description'] ?? '') ?: null;

if (empty($violation_type) || $fine_amount === '') {
    header('Location: add_rule.php?error=' . urlencode('Violation type and fine amount are required.'));
    exit;
}

try {
    $check = $pdo->prepare("SELECT rule_id FROM violation_rules WHERE violation_type = :type");
    $check->execute([':type' => $violation_type]);
    if ($check->fetch()) {
        header('Location: add_rule.php?error=' . urlencode('This violation type already exists.'));
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO violation_rules (violation_type, fine_amount, description) VALUES (:type, :amount, :desc)");
    $stmt->execute([':type' => $violation_type, ':amount' => $fine_amount, ':desc' => $description]);

    header('Location: view_rules.php?success=' . urlencode('Violation rule added successfully.'));
} catch (PDOException $e) {
    error_log("Save rule error: " . $e->getMessage());
    header('Location: add_rule.php?error=' . urlencode('Failed to add rule.'));
}
exit;
