<?php
/**
 * TrafficLens AI — Update Vehicle
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: view_vehicles.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$vehicle_id        = $_POST['vehicle_id'] ?? '';
$driver_id         = $_POST['driver_id'] ?? '';
$vehicle_number    = trim($_POST['vehicle_number'] ?? '');
$vehicle_type      = trim($_POST['vehicle_type'] ?? '');
$manufacturer      = trim($_POST['manufacturer'] ?? '');
$model             = trim($_POST['model'] ?? '');
$color             = trim($_POST['color'] ?? '') ?: null;
$registration_date = $_POST['registration_date'] ?? '';

if (empty($vehicle_id) || empty($driver_id) || empty($vehicle_number) || empty($vehicle_type) || empty($manufacturer) || empty($model) || empty($registration_date)) {
    header('Location: edit_vehicle.php?id=' . urlencode($vehicle_id) . '&error=' . urlencode('Required fields are missing.'));
    exit;
}

try {
    $check = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE vehicle_number = :num AND vehicle_id != :id");
    $check->execute([':num' => $vehicle_number, ':id' => $vehicle_id]);
    if ($check->fetch()) {
        header('Location: edit_vehicle.php?id=' . urlencode($vehicle_id) . '&error=' . urlencode('Vehicle number already in use.'));
        exit;
    }

    $stmt = $pdo->prepare("UPDATE vehicles SET driver_id = :driver_id, vehicle_number = :vehicle_number, vehicle_type = :vehicle_type, manufacturer = :manufacturer, model = :model, color = :color, registration_date = :registration_date WHERE vehicle_id = :id");
    $stmt->execute([
        ':driver_id' => $driver_id, ':vehicle_number' => $vehicle_number, ':vehicle_type' => $vehicle_type,
        ':manufacturer' => $manufacturer, ':model' => $model, ':color' => $color,
        ':registration_date' => $registration_date, ':id' => $vehicle_id,
    ]);

    header('Location: view_vehicles.php?success=' . urlencode('Vehicle updated successfully.'));
} catch (PDOException $e) {
    error_log("Update vehicle error: " . $e->getMessage());
    header('Location: edit_vehicle.php?id=' . urlencode($vehicle_id) . '&error=' . urlencode('Failed to update vehicle.'));
}
exit;
