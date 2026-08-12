<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: view_violations.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$violation_id   = $_POST['violation_id'] ?? '';
$driver_id      = $_POST['driver_id'] ?? '';
$vehicle_id     = $_POST['vehicle_id'] ?? '';
$rule_id        = $_POST['rule_id'] ?? '';
$location       = trim($_POST['location'] ?? '');
$officer_name   = trim($_POST['officer_name'] ?? '');
$violation_date = $_POST['violation_date'] ?? '';
$violation_time = $_POST['violation_time'] ?? '';

if (empty($violation_id) || empty($driver_id) || empty($vehicle_id) || empty($rule_id) || empty($location) || empty($officer_name) || empty($violation_date) || empty($violation_time)) {
    header('Location: edit_violation.php?id=' . urlencode($violation_id) . '&error=' . urlencode('Required fields missing.'));
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE violations 
        SET driver_id = :driver_id, vehicle_id = :vehicle_id, rule_id = :rule_id, location = :location, 
            officer_name = :officer_name, violation_date = :violation_date, violation_time = :violation_time
        WHERE violation_id = :id
    ");
    $stmt->execute([
        ':driver_id' => $driver_id, ':vehicle_id' => $vehicle_id, ':rule_id' => $rule_id,
        ':location' => $location, ':officer_name' => $officer_name,
        ':violation_date' => $violation_date, ':violation_time' => $violation_time,
        ':id' => $violation_id,
    ]);

    header('Location: view_violations.php?success=' . urlencode('Violation updated successfully.'));
} catch (PDOException $e) {
    error_log("Update violation error: " . $e->getMessage());
    header('Location: edit_violation.php?id=' . urlencode($violation_id) . '&error=' . urlencode('Failed to update violation.'));
}
exit;
