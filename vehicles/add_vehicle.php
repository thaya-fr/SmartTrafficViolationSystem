<?php
/**
 * TrafficLens AI — Add Vehicle
 * Form to register a new vehicle linked to a driver.
 */
$page_title = 'Add Vehicle';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

// Fetch drivers for dropdown
try {
    $drivers = $pdo->query("SELECT driver_id, full_name, license_number FROM drivers ORDER BY full_name ASC")->fetchAll();
} catch (PDOException $e) {
    $drivers = [];
}
?>

<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <h3 class="card-title">Register New Vehicle</h3>
        <a href="view_vehicles.php" class="btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <form action="save_vehicle.php" method="POST" id="addVehicleForm" onsubmit="return validateForm('addVehicleForm')">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="vehicle_number">Vehicle Number <span class="required">*</span></label>
                <input type="text" id="vehicle_number" name="vehicle_number" class="form-input" placeholder="e.g. MH-12-AB-1234" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="vehicle_type">Vehicle Type <span class="required">*</span></label>
                <select id="vehicle_type" name="vehicle_type" class="form-select" required>
                    <option value="">Select type</option>
                    <option value="Car">Car</option>
                    <option value="Bike">Bike</option>
                    <option value="Truck">Truck</option>
                    <option value="Bus">Bus</option>
                    <option value="Auto Rickshaw">Auto Rickshaw</option>
                    <option value="Scooter">Scooter</option>
                    <option value="Van">Van</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="manufacturer">Manufacturer <span class="required">*</span></label>
                <input type="text" id="manufacturer" name="manufacturer" class="form-input" placeholder="e.g. Maruti Suzuki" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="model">Model <span class="required">*</span></label>
                <input type="text" id="model" name="model" class="form-input" placeholder="e.g. Swift Dzire" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="color">Color</label>
                <input type="text" id="color" name="color" class="form-input" placeholder="e.g. White">
            </div>

            <div class="form-group">
                <label class="form-label" for="registration_date">Registration Date <span class="required">*</span></label>
                <input type="date" id="registration_date" name="registration_date" class="form-input" required>
            </div>

            <div class="form-group full-width">
                <label class="form-label" for="driver_id">Owner (Driver) <span class="required">*</span></label>
                <select id="driver_id" name="driver_id" class="form-select" required>
                    <option value="">Select driver</option>
                    <?php foreach ($drivers as $d): ?>
                    <option value="<?php echo $d['driver_id']; ?>">
                        <?php echo htmlspecialchars($d['full_name'] . ' — ' . $d['license_number']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Save Vehicle</button>
            <a href="view_vehicles.php" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
