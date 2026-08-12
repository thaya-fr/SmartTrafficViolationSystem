<?php
/**
 * TrafficLens AI — API: Get Fine by Rule
 * Returns JSON with fine_amount for a given rule_id.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

$rule_id = $_GET['rule_id'] ?? '';
if (empty($rule_id)) { echo json_encode(['fine_amount' => 0]); exit; }

try {
    $stmt = $pdo->prepare("SELECT fine_amount FROM violation_rules WHERE rule_id = :id LIMIT 1");
    $stmt->execute([':id' => $rule_id]);
    $rule = $stmt->fetch();
    echo json_encode(['fine_amount' => $rule ? $rule['fine_amount'] : 0]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
