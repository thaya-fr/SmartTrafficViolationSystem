<?php
/**
 * TrafficLens AI — Delete Vehicle
 */
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$vehicle_id = $_GET['id'] ?? '';
if (empty($vehicle_id)) { header('Location: view_vehicles.php?error=' . urlencode('Invalid vehicle ID.')); exit; }

try {
    $check = $pdo->prepare("SELECT COUNT(*) as count FROM violations WHERE vehicle_id = :id");
    $check->execute([':id' => $vehicle_id]);
    $count = $check->fetch()['count'];

    if ($count > 0) {
        header('Location: view_vehicles.php?error=' . urlencode("Cannot delete vehicle. {$count} violation(s) linked."));
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM vehicles WHERE vehicle_id = :id");
    $stmt->execute([':id' => $vehicle_id]);

    header('Location: view_vehicles.php?success=' . urlencode('Vehicle deleted successfully.'));
} catch (PDOException $e) {
    error_log("Delete vehicle error: " . $e->getMessage());
    header('Location: view_vehicles.php?error=' . urlencode('Failed to delete vehicle.'));
}
exit;
