<?php
/**
 * TrafficLens AI — Edit Vehicle
 */
$page_title = 'Edit Vehicle';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$vehicle_id = $_GET['id'] ?? '';
if (empty($vehicle_id)) { header('Location: view_vehicles.php?error=' . urlencode('Invalid vehicle ID.')); exit; }

try {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE vehicle_id = :id LIMIT 1");
    $stmt->execute([':id' => $vehicle_id]);
    $vehicle = $stmt->fetch();
    if (!$vehicle) { header('Location: view_vehicles.php?error=' . urlencode('Vehicle not found.')); exit; }

    $drivers = $pdo->query("SELECT driver_id, full_name, license_number FROM drivers ORDER BY full_name ASC")->fetchAll();
} catch (PDOException $e) {
    error_log("Edit vehicle error: " . $e->getMessage());
    header('Location: view_vehicles.php?error=' . urlencode('Error loading vehicle.'));
    exit;
}
?>

<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <h3 class="card-title">Edit Vehicle</h3>
        <a href="view_vehicles.php" class="btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <form action="update_vehicle.php" method="POST" id="editVehicleForm" onsubmit="return validateForm('editVehicleForm')">
        <input type="hidden" name="vehicle_id" value="<?php echo htmlspecialchars($vehicle['vehicle_id']); ?>">

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="vehicle_number">Vehicle Number <span class="required">*</span></label>
                <input type="text" id="vehicle_number" name="vehicle_number" class="form-input" value="<?php echo htmlspecialchars($vehicle['vehicle_number']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="vehicle_type">Vehicle Type <span class="required">*</span></label>
                <select id="vehicle_type" name="vehicle_type" class="form-select" required>
                    <?php foreach (['Car','Bike','Truck','Bus','Auto Rickshaw','Scooter','Van','Other'] as $type): ?>
                    <option value="<?php echo $type; ?>" <?php echo ($vehicle['vehicle_type'] === $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="manufacturer">Manufacturer <span class="required">*</span></label>
                <input type="text" id="manufacturer" name="manufacturer" class="form-input" value="<?php echo htmlspecialchars($vehicle['manufacturer']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="model">Model <span class="required">*</span></label>
                <input type="text" id="model" name="model" class="form-input" value="<?php echo htmlspecialchars($vehicle['model']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="color">Color</label>
                <input type="text" id="color" name="color" class="form-input" value="<?php echo htmlspecialchars($vehicle['color'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="registration_date">Registration Date <span class="required">*</span></label>
                <input type="date" id="registration_date" name="registration_date" class="form-input" value="<?php echo $vehicle['registration_date']; ?>" required>
            </div>

            <div class="form-group full-width">
                <label class="form-label" for="driver_id">Owner (Driver) <span class="required">*</span></label>
                <select id="driver_id" name="driver_id" class="form-select" required>
                    <option value="">Select driver</option>
                    <?php foreach ($drivers as $d): ?>
                    <option value="<?php echo $d['driver_id']; ?>" <?php echo ($d['driver_id'] === $vehicle['driver_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($d['full_name'] . ' — ' . $d['license_number']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Update Vehicle</button>
            <a href="view_vehicles.php" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
