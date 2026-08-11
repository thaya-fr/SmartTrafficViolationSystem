<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: view_violations.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$driver_id      = $_POST['driver_id'] ?? '';
$vehicle_id     = $_POST['vehicle_id'] ?? '';
$rule_id        = $_POST['rule_id'] ?? '';
$location       = trim($_POST['location'] ?? '');
$officer_name   = trim($_POST['officer_name'] ?? '');
$violation_date = $_POST['violation_date'] ?? '';
$violation_time = $_POST['violation_time'] ?? '';

if (empty($driver_id) || empty($vehicle_id) || empty($rule_id) || empty($location) || empty($officer_name) || empty($violation_date) || empty($violation_time)) {
    header('Location: add_violation.php?error=' . urlencode('All required fields must be filled.'));
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO violations (driver_id, vehicle_id, rule_id, location, officer_name, violation_date, violation_time, payment_status)
        VALUES (:driver_id, :vehicle_id, :rule_id, :location, :officer_name, :violation_date, :violation_time, 'Pending')
    ");
    $stmt->execute([
        ':driver_id'      => $driver_id,
        ':vehicle_id'     => $vehicle_id,
        ':rule_id'        => $rule_id,
        ':location'       => $location,
        ':officer_name'   => $officer_name,
        ':violation_date' => $violation_date,
        ':violation_time' => $violation_time,
    ]);

    header('Location: view_violations.php?success=' . urlencode('Violation recorded successfully.'));
} catch (PDOException $e) {
    error_log("Save violation error: " . $e->getMessage());
    header('Location: add_violation.php?error=' . urlencode('Failed to record violation.'));
}
exit;
