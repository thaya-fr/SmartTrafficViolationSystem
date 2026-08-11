<?php
/**
 * TrafficLens AI — API: Get Vehicles by Driver
 * Returns JSON array of vehicles for a given driver_id.
 */
session_start();
if (!isset($_SESSION['admin_id'])) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

$driver_id = $_GET['driver_id'] ?? '';
if (empty($driver_id)) { echo json_encode([]); exit; }

try {
    $stmt = $pdo->prepare("SELECT vehicle_id, vehicle_number, vehicle_type, manufacturer, model FROM vehicles WHERE driver_id = :driver_id ORDER BY vehicle_number");
    $stmt->execute([':driver_id' => $driver_id]);
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
