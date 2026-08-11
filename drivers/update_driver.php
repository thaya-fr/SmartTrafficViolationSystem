<?php
/**
 * TrafficLens AI — Update Driver
 * Handles POST from edit_driver.php to update an existing driver.
 */
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: view_drivers.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$driver_id      = $_POST['driver_id'] ?? '';
$full_name      = trim($_POST['full_name'] ?? '');
$license_number = trim($_POST['license_number'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$email          = trim($_POST['email'] ?? '') ?: null;
$address        = trim($_POST['address'] ?? '') ?: null;

// Validation
if (empty($driver_id) || empty($full_name) || empty($license_number) || empty($phone)) {
    header('Location: edit_driver.php?id=' . urlencode($driver_id) . '&error=' . urlencode('Required fields are missing.'));
    exit;
}

try {
    // Check for duplicate license number (excluding current driver)
    $check = $pdo->prepare("SELECT driver_id FROM drivers WHERE license_number = :license AND driver_id != :id");
    $check->execute([':license' => $license_number, ':id' => $driver_id]);
    if ($check->fetch()) {
        header('Location: edit_driver.php?id=' . urlencode($driver_id) . '&error=' . urlencode('License number already in use.'));
        exit;
    }

    // Check for duplicate email (excluding current driver)
    if ($email) {
        $check_email = $pdo->prepare("SELECT driver_id FROM drivers WHERE email = :email AND driver_id != :id");
        $check_email->execute([':email' => $email, ':id' => $driver_id]);
        if ($check_email->fetch()) {
            header('Location: edit_driver.php?id=' . urlencode($driver_id) . '&error=' . urlencode('Email already in use.'));
            exit;
        }
    }

    // Update
    $stmt = $pdo->prepare("
        UPDATE drivers 
        SET full_name = :full_name, license_number = :license_number, phone = :phone, email = :email, address = :address
        WHERE driver_id = :id
    ");
    $stmt->execute([
        ':full_name'      => $full_name,
        ':license_number' => $license_number,
        ':phone'          => $phone,
        ':email'          => $email,
        ':address'        => $address,
        ':id'             => $driver_id,
    ]);

    header('Location: view_drivers.php?success=' . urlencode('Driver updated successfully.'));
    exit;

} catch (PDOException $e) {
    error_log("Update driver error: " . $e->getMessage());
    header('Location: edit_driver.php?id=' . urlencode($driver_id) . '&error=' . urlencode('Failed to update driver.'));
    exit;
}
