<?php
/**
 * TrafficLens AI — Delete Driver
 * Deletes a driver after checking for linked vehicles.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$driver_id = $_GET['id'] ?? '';

if (empty($driver_id)) {
    header('Location: view_drivers.php?error=' . urlencode('Invalid driver ID.'));
    exit;
}

try {
    // Check for linked vehicles
    $check = $pdo->prepare("SELECT COUNT(*) as count FROM vehicles WHERE driver_id = :id");
    $check->execute([':id' => $driver_id]);
    $vehicle_count = $check->fetch()['count'];

    if ($vehicle_count > 0) {
        header('Location: view_drivers.php?error=' . urlencode("Cannot delete driver. {$vehicle_count} vehicle(s) are linked to this driver. Delete vehicles first."));
        exit;
    }

    // Check for linked violations
    $check_violations = $pdo->prepare("SELECT COUNT(*) as count FROM violations WHERE driver_id = :id");
    $check_violations->execute([':id' => $driver_id]);
    $violation_count = $check_violations->fetch()['count'];

    if ($violation_count > 0) {
        header('Location: view_drivers.php?error=' . urlencode("Cannot delete driver. {$violation_count} violation(s) are linked to this driver."));
        exit;
    }

    // Delete driver
    $stmt = $pdo->prepare("DELETE FROM drivers WHERE driver_id = :id");
    $stmt->execute([':id' => $driver_id]);

    header('Location: view_drivers.php?success=' . urlencode('Driver deleted successfully.'));
    exit;

} catch (PDOException $e) {
    error_log("Delete driver error: " . $e->getMessage());
    header('Location: view_drivers.php?error=' . urlencode('Failed to delete driver.'));
    exit;
}
