<?php
/**
 * TrafficLens AI — Save Vehicle
 */
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: view_vehicles.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$driver_id         = $_POST['driver_id'] ?? '';
$vehicle_number    = trim($_POST['vehicle_number'] ?? '');
$vehicle_type      = trim($_POST['vehicle_type'] ?? '');
$manufacturer      = trim($_POST['manufacturer'] ?? '');
$model             = trim($_POST['model'] ?? '');
$color             = trim($_POST['color'] ?? '') ?: null;
$registration_date = $_POST['registration_date'] ?? '';

if (empty($driver_id) || empty($vehicle_number) || empty($vehicle_type) || empty($manufacturer) || empty($model) || empty($registration_date)) {
    header('Location: add_vehicle.php?error=' . urlencode('All required fields must be filled.'));
    exit;
}

try {
    $check = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE vehicle_number = :num");
    $check->execute([':num' => $vehicle_number]);
    if ($check->fetch()) {
        header('Location: add_vehicle.php?error=' . urlencode('Vehicle number already exists.'));
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO vehicles (driver_id, vehicle_number, vehicle_type, manufacturer, model, color, registration_date) 
        VALUES (:driver_id, :vehicle_number, :vehicle_type, :manufacturer, :model, :color, :registration_date)
    ");
    $stmt->execute([
        ':driver_id'         => $driver_id,
        ':vehicle_number'    => $vehicle_number,
        ':vehicle_type'      => $vehicle_type,
        ':manufacturer'      => $manufacturer,
        ':model'             => $model,
        ':color'             => $color,
        ':registration_date' => $registration_date,
    ]);

    header('Location: view_vehicles.php?success=' . urlencode('Vehicle registered successfully.'));
} catch (PDOException $e) {
    error_log("Save vehicle error: " . $e->getMessage());
    header('Location: add_vehicle.php?error=' . urlencode('Failed to register vehicle.'));
}
exit;
