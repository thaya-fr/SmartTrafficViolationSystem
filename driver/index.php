<?php
/**
 * TrafficLens AI — Public Driver Verification & Online Fine Portal
 * Verification Page for Drivers and Vehicle Owners
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

// Auto-seed sample driver, vehicle, and violation if database has no drivers
try {
    $driver_count = $pdo->query("SELECT COUNT(*) FROM drivers")->fetchColumn();
    if ($driver_count == 0) {
        // Seed Driver 1
        $stmt = $pdo->prepare("
            INSERT INTO drivers (driver_id, full_name, license_number, phone, email, address)
            VALUES ('11111111-1111-1111-1111-111111111111', 'Rajesh Kumar', 'DL-1420110012345', '9876543210', 'rajesh.kumar@gmail.com', 'MG Road, Connaught Place, New Delhi')
            ON CONFLICT DO NOTHING
        ");
        $stmt->execute();

        // Seed Driver 2
        $stmt2 = $pdo->prepare("
            INSERT INTO drivers (driver_id, full_name, license_number, phone, email, address)
            VALUES ('22222222-2222-2222-2222-222222222222', 'Anita Sharma', 'KA-0120220098765', '9812345678', 'anita.sharma@yahoo.com', 'Indiranagar 100ft Road, Bengaluru')
            ON CONFLICT DO NOTHING
        ");
        $stmt2->execute();

        // Seed Vehicle 1
        $v_stmt = $pdo->prepare("
            INSERT INTO vehicles (vehicle_id, driver_id, vehicle_number, vehicle_type, manufacturer, model, color, registration_date)
            VALUES ('33333333-3333-3333-3333-333333333333', '11111111-1111-1111-1111-111111111111', 'MH12AB4321', 'Car', 'Hyundai', 'Creta', 'White', '2022-04-15')
            ON CONFLICT DO NOTHING
        ");
        $v_stmt->execute();

        // Seed Vehicle 2
        $v_stmt2 = $pdo->prepare("
            INSERT INTO vehicles (vehicle_id, driver_id, vehicle_number, vehicle_type, manufacturer, model, color, registration_date)
            VALUES ('44444444-4444-4444-4444-444444444444', '22222222-2222-2222-2222-222222222222', 'KA01MJ9999', 'Motorcycle', 'Royal Enfield', 'Classic 350', 'Black', '2023-01-10')
            ON CONFLICT DO NOTHING
        ");
        $v_stmt2->execute();

        // Fetch a violation rule
        $rule = $pdo->query("SELECT rule_id FROM violation_rules LIMIT 1")->fetch();
        if ($rule) {
            $rule_id = $rule['rule_id'];
            // Seed Pending Violation for Driver 1
            $viol_stmt = $pdo->prepare("
                INSERT INTO violations (driver_id, vehicle_id, rule_id, location, officer_name, violation_date, violation_time, payment_status)
                VALUES ('11111111-1111-1111-1111-111111111111', '33333333-3333-3333-3333-333333333333', :rule_id, 'Silicon City Junction, Outer Ring Rd', 'Officer Vikram Singh', CURRENT_DATE - INTERVAL '2 days', '14:30:00', 'Pending')
            ");
            $viol_stmt->execute([':rule_id' => $rule_id]);
        }
    }
} catch (PDOException $e) {
    error_log("Driver auto-seed error: " . $e->getMessage());
}

$error = '';
$search_term = '';

// Process Verification Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_term = trim($_POST['search_term'] ?? '');
    
    if (empty($search_term)) {
        $error = 'Please enter your Driving License Number or Vehicle Registration Number.';
    } else {
        try {
            $clean_search = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $search_term));
            $raw_search = strtoupper($search_term);

            // Check by License Number
            $stmt = $pdo->prepare("
                SELECT * FROM drivers 
                WHERE REPLACE(REPLACE(UPPER(license_number), '-', ''), ' ', '') LIKE :clean
                   OR UPPER(license_number) LIKE :raw
                   OR UPPER(full_name) LIKE :raw
                LIMIT 1
            ");
            $stmt->execute([':clean' => "%{$clean_search}%", ':raw' => "%{$raw_search}%"]);
            $driver = $stmt->fetch();

            if (!$driver) {
                // Check by Vehicle Number
                $v_stmt = $pdo->prepare("
                    SELECT d.* FROM vehicles v
                    JOIN drivers d ON v.driver_id = d.driver_id
                    WHERE REPLACE(REPLACE(UPPER(v.vehicle_number), '-', ''), ' ', '') LIKE :clean
                       OR UPPER(v.vehicle_number) LIKE :raw
                    LIMIT 1
                ");
                $v_stmt->execute([':clean' => "%{$clean_search}%", ':raw' => "%{$raw_search}%"]);
                $driver = $v_stmt->fetch();
            }

            if ($driver) {
                require_once __DIR__ . '/../config/session.php';
                set_driver_session($driver['driver_id'], $driver['full_name'], $driver['license_number']);
                header('Location: portal.php');
                exit;
            } else {
                $error = 'No driver or vehicle record found matching "' . $search_term . '". Please check and try again.';
            }
        } catch (PDOException $e) {
            error_log("Driver login error: " . $e->getMessage());
            $error = 'Database error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TrafficLens AI — Public Driver E-Challan & Fine Verification Portal">
    <title>Driver Verification & Pay Fine Online — TrafficLens AI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dark-portal-body">

    <!-- Top Navigation Bar -->
    <header class="dark-header">
        <div class="dark-header-left">
            <a href="../admin/login.php" class="dark-brand">
                <i class="fas fa-video"></i>
                <div>Traffic<span>Lens</span> AI</div>
            </a>
            <span class="dark-badge-pill" style="margin-left: 12px;">
                <span class="pill-dot"></span> Driver E-Challan Portal
            </span>
        </div>

        <div class="dark-header-right">
            <a href="../admin/login.php" class="btn-dark-text">Officer Console</a>
        </div>
    </header>

    <main class="dark-main-container" style="max-width: 600px; display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 160px);">
        
        <div class="dark-modal-card" style="position: relative; opacity: 1; transform: none; width: 100%; border-color: rgba(0, 229, 255, 0.3);">
            <div class="dark-modal-brand">
                <div class="dark-modal-logo" style="background-color: rgba(152, 255, 56, 0.1); border-color: rgba(152, 255, 56, 0.3); color: #98ff38;">
                    <i class="fas fa-id-card"></i>
                </div>
                <h2>Driver Verification Portal</h2>
                <p>Check traffic violations & pay e-challans online</p>
            </div>

            <?php if ($error): ?>
                <div class="login-error mb-20" style="background-color: rgba(255, 77, 77, 0.1); border: 1px solid rgba(255, 77, 77, 0.3); color: #ff4d4d; padding: 12px 16px; border-radius: 12px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php" class="dark-modal-form">
                <div class="dark-field-group">
                    <label for="search_term">License Number or Vehicle Number</label>
                    <input 
                        type="text" 
                        id="search_term" 
                        name="search_term" 
                        class="dark-field-input" 
                        placeholder="e.g. DL-1420110012345 or MH12AB4321" 
                        value="<?php echo htmlspecialchars($search_term); ?>"
                        required
                        autofocus
                    >
                </div>

                <button type="submit" class="btn-cyan-glow" style="width: 100%; justify-content: center; padding: 14px; margin-top: 8px;">
                    <span>Verify & View Violations</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="dark-demo-helper" style="margin-top: 24px;">
                <div>Sample Search: <code>DL-1420110012345</code></div>
                <button type="button" class="btn-autofill-cyan" onclick="document.getElementById('search_term').value='DL-1420110012345';">Try Sample</button>
            </div>
        </div>

    </main>

    <footer class="dark-footer">
        <div>© <?php echo date('Y'); ?> TrafficLens AI. Public E-Challan Services.</div>
    </footer>

</body>
</html>
