<?php
/**
 * TrafficLens AI — Save Driver
 * Handles POST from add_driver.php to insert a new driver.
 */
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: view_drivers.php'); exit; }

require_once __DIR__ . '/../config/db.php';

$full_name      = trim($_POST['full_name'] ?? '');
$license_number = trim($_POST['license_number'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$email          = trim($_POST['email'] ?? '') ?: null;
$address        = trim($_POST['address'] ?? '') ?: null;

// Server-side validation
if (empty($full_name) || empty($license_number) || empty($phone)) {
    header('Location: add_driver.php?error=' . urlencode('Full name, license number, and phone are required.'));
    exit;
}

try {
    // Check for duplicate license number
    $check = $pdo->prepare("SELECT driver_id FROM drivers WHERE license_number = :license");
    $check->execute([':license' => $license_number]);
    if ($check->fetch()) {
        header('Location: add_driver.php?error=' . urlencode('A driver with this license number already exists.'));
        exit;
    }

    // Check for duplicate email
    if ($email) {
        $check_email = $pdo->prepare("SELECT driver_id FROM drivers WHERE email = :email");
        $check_email->execute([':email' => $email]);
        if ($check_email->fetch()) {
            header('Location: add_driver.php?error=' . urlencode('A driver with this email already exists.'));
            exit;
        }
    }

    // Insert driver
    $stmt = $pdo->prepare("
        INSERT INTO drivers (full_name, license_number, phone, email, address) 
        VALUES (:full_name, :license_number, :phone, :email, :address)
    ");
    $stmt->execute([
        ':full_name'      => $full_name,
        ':license_number' => $license_number,
        ':phone'          => $phone,
        ':email'          => $email,
        ':address'        => $address,
    ]);

    header('Location: view_drivers.php?success=' . urlencode('Driver added successfully.'));
    exit;

} catch (PDOException $e) {
    error_log("Save driver error: " . $e->getMessage());
    header('Location: add_driver.php?error=' . urlencode('Failed to add driver. Please try again.'));
    exit;
}
