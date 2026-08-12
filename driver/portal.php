<?php
/**
 * TrafficLens AI — Driver Violation & Payment Dashboard
 * Displays driver info, recorded traffic violations, and online fine payment CTAs.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['driver_id'])) {
    header('Location: index.php');
    exit;
}

$driver_id = $_SESSION['driver_id'];

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['driver_id']);
    unset($_SESSION['driver_name']);
    unset($_SESSION['driver_license']);
    header('Location: index.php');
    exit;
}

try {
    // Driver Info
    $d_stmt = $pdo->prepare("SELECT * FROM drivers WHERE driver_id = :id LIMIT 1");
    $d_stmt->execute([':id' => $driver_id]);
    $driver = $d_stmt->fetch();

    // Vehicles
    $v_stmt = $pdo->prepare("SELECT * FROM vehicles WHERE driver_id = :id");
    $v_stmt->execute([':id' => $driver_id]);
    $vehicles = $v_stmt->fetchAll();

    // Violations
    $viol_stmt = $pdo->prepare("
        SELECT v.*, vr.violation_type, vr.fine_amount, vh.vehicle_number
        FROM violations v
        JOIN violation_rules vr ON v.rule_id = vr.rule_id
        JOIN vehicles vh ON v.vehicle_id = vh.vehicle_id
        WHERE v.driver_id = :id
        ORDER BY v.violation_date DESC, v.violation_time DESC
    ");
    $viol_stmt->execute([':id' => $driver_id]);
    $violations = $viol_stmt->fetchAll();

    // Stats
    $total_violations = count($violations);
    $pending_fines = 0;
    $total_outstanding = 0.00;

    foreach ($violations as $v) {
        if ($v['payment_status'] === 'Pending') {
            $pending_fines++;
            $total_outstanding += floatval($v['fine_amount']);
        }
    }
} catch (PDOException $e) {
    error_log("Driver portal fetch error: " . $e->getMessage());
    $violations = [];
    $vehicles = [];
    $total_violations = 0;
    $pending_fines = 0;
    $total_outstanding = 0.00;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TrafficLens AI — Driver E-Challan & Fine Payment Dashboard">
    <title>Driver Dashboard — TrafficLens AI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dark-portal-body">

    <!-- Header Navigation -->
    <header class="dark-header">
        <div class="dark-header-left">
            <a href="portal.php" class="dark-brand">
                <i class="fas fa-video"></i>
                <div>Traffic<span>Lens</span> AI</div>
            </a>
            <span class="dark-badge-pill">
                <span class="pill-dot"></span> Verified Driver Session
            </span>
        </div>

        <div class="dark-header-right">
            <div class="admin-badge" style="background-color: #141a26; border-color: rgba(0, 229, 255, 0.3);">
                <i class="fas fa-user" style="color: #00e5ff;"></i>
                <span style="color: #ffffff; font-weight: 600;"><?php echo htmlspecialchars($driver['full_name'] ?? 'Driver'); ?></span>
            </div>
            <a href="portal.php?action=logout" class="btn-dark-text" style="color: #ff4d4d;">
                <i class="fas fa-sign-out-alt"></i> Exit Portal
            </a>
        </div>
    </header>

    <main class="dark-main-container">
        
        <!-- Driver Profile Header Card -->
        <div class="dark-card mb-32" style="border-color: rgba(0, 229, 255, 0.3);">
            <div class="flex items-center justify-between" style="flex-wrap: wrap; gap: 16px;">
                <div class="flex items-center gap-16">
                    <div style="width: 54px; height: 54px; border-radius: 16px; background-color: rgba(0, 229, 255, 0.1); border: 1px solid rgba(0, 229, 255, 0.3); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #00e5ff;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 22px; font-weight: 800; color: #ffffff;"><?php echo htmlspecialchars($driver['full_name'] ?? $_SESSION['driver_name'] ?? 'Driver'); ?></h2>
                        <div style="font-family: var(--font-mono); font-size: 13px; color: #9ca3af; margin-top: 2px;">
                            License No: <strong style="color: #00e5ff;"><?php echo htmlspecialchars($driver['license_number'] ?? $_SESSION['driver_license'] ?? 'N/A'); ?></strong> &bull; Phone: <?php echo htmlspecialchars($driver['phone'] ?? 'N/A'); ?>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-12">
                    <span class="badge" style="background: rgba(152, 255, 56, 0.1); border-color: rgba(152, 255, 56, 0.3); color: #98ff38;">
                        <i class="fas fa-check-circle"></i> Verification Verified
                    </span>
                </div>
            </div>
        </div>

        <!-- Summary Stat Cards -->
        <div class="dark-cards-grid mb-32">
            <div class="dark-card">
                <div class="dark-card-icon">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <h3>Total Violations</h3>
                <div style="font-size: 32px; font-weight: 800; color: #ffffff;"><?php echo number_format($total_violations); ?></div>
                <div class="dark-card-meta">
                    <div>Records Found</div>
                    <span>System DB</span>
                </div>
            </div>

            <div class="dark-card">
                <div class="dark-card-icon" style="color: #f59e0b; background-color: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.2);">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Unpaid E-Challans</h3>
                <div style="font-size: 32px; font-weight: 800; color: <?php echo $pending_fines > 0 ? '#f59e0b' : '#98ff38'; ?>;"><?php echo number_format($pending_fines); ?></div>
                <div class="dark-card-meta">
                    <div>Payment Status</div>
                    <span style="color: <?php echo $pending_fines > 0 ? '#f59e0b' : '#98ff38'; ?>;"><?php echo $pending_fines > 0 ? 'Action Required' : 'All Clear'; ?></span>
                </div>
            </div>

            <div class="dark-card">
                <div class="dark-card-icon" style="color: #98ff38; background-color: rgba(152, 255, 56, 0.1); border-color: rgba(152, 255, 56, 0.2);">
                    <i class="fas fa-indian-rupee-sign"></i>
                </div>
                <h3>Total Outstanding</h3>
                <div style="font-size: 32px; font-weight: 800; color: #ffffff;">₹<?php echo number_format($total_outstanding, 2); ?></div>
                <div class="dark-card-meta">
                    <div>Fine Amount</div>
                    <span style="color: #98ff38;">Pay Online Ready</span>
                </div>
            </div>
        </div>

        <!-- Recorded Violations List -->
        <div class="dark-card">
            <div class="card-header">
                <div class="flex items-center gap-12">
                    <i class="fas fa-list-check" style="color: #00e5ff; font-size: 20px;"></i>
                    <h3 class="card-title">Traffic Violation Notices</h3>
                </div>
                <span class="text-meta"><?php echo number_format($total_violations); ?> notices logged</span>
            </div>

            <?php if (count($violations) > 0): ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Violation Type</th>
                            <th>Vehicle Number</th>
                            <th>Location</th>
                            <th>Date & Time</th>
                            <th>Fine Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($violations as $v): ?>
                        <tr>
                            <td style="font-weight: 600; color: #ffffff;">
                                <i class="fas fa-exclamation-triangle" style="color: #f59e0b; margin-right: 6px;"></i>
                                <?php echo htmlspecialchars($v['violation_type']); ?>
                            </td>
                            <td class="cell-mono" style="color: #00e5ff;"><?php echo htmlspecialchars($v['vehicle_number']); ?></td>
                            <td><?php echo htmlspecialchars($v['location']); ?></td>
                            <td class="cell-muted">
                                <?php echo date('d M Y', strtotime($v['violation_date'])); ?> 
                                <span style="font-size: 11px; opacity: 0.7;"><?php echo date('h:i A', strtotime($v['violation_time'])); ?></span>
                            </td>
                            <td class="cell-mono" style="font-size: 15px; font-weight: bold; color: #ffffff;">
                                ₹<?php echo number_format($v['fine_amount'], 2); ?>
                            </td>
                            <td>
                                <?php if ($v['payment_status'] === 'Paid'): ?>
                                    <span class="badge badge-paid"><span class="dot"></span> Paid</span>
                                <?php else: ?>
                                    <span class="badge badge-pending"><span class="dot"></span> Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($v['payment_status'] === 'Pending'): ?>
                                    <a href="pay.php?violation_id=<?php echo urlencode($v['violation_id']); ?>" class="btn-primary btn-sm">
                                        <i class="fas fa-credit-card"></i> Pay Online
                                    </a>
                                <?php else: ?>
                                    <a href="pay.php?receipt=1&violation_id=<?php echo urlencode($v['violation_id']); ?>" class="btn-ghost btn-sm" style="color: #98ff38; border-color: rgba(152, 255, 56, 0.4);">
                                        <i class="fas fa-receipt"></i> E-Receipt
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shield-cat" style="color: #98ff38;"></i>
                <p class="empty-title">Clean Driving Record!</p>
                <p class="empty-text">No traffic violations or unpaid e-challans found for your account.</p>
            </div>
            <?php endif; ?>
        </div>

    </main>

    <footer class="dark-footer">
        <div>© <?php echo date('Y'); ?> TrafficLens AI. Public E-Challan Services.</div>
    </footer>

</body>
</html>
