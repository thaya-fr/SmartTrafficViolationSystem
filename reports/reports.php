<?php
/**
 * TrafficLens AI — Reports Module
 * Tab-based reports: Drivers, Vehicles, Violations, Payments.
 * Supports search, date filters, CSV export, and print.
 */
$page_title = 'Reports';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$active_tab   = $_GET['tab'] ?? 'violations';
$search       = trim($_GET['search'] ?? '');
$date_from    = $_GET['date_from'] ?? '';
$date_to      = $_GET['date_to'] ?? '';
$export       = $_GET['export'] ?? '';

// ---- Fetch report data based on active tab ----
try {
    switch ($active_tab) {
        case 'drivers':
            $query = "SELECT d.*, 
                      (SELECT COUNT(*) FROM vehicles WHERE driver_id = d.driver_id) as vehicle_count,
                      (SELECT COUNT(*) FROM violations WHERE driver_id = d.driver_id) as violation_count
                      FROM drivers d WHERE 1=1";
            $params = [];
            if ($search) {
                $query .= " AND (d.full_name ILIKE :search OR d.license_number ILIKE :search OR d.phone ILIKE :search OR d.email ILIKE :search)";
                $params[':search'] = "%{$search}%";
            }
            if ($date_from) { $query .= " AND d.created_at >= :date_from"; $params[':date_from'] = $date_from; }
            if ($date_to)   { $query .= " AND d.created_at <= :date_to"; $params[':date_to'] = $date_to . ' 23:59:59'; }
            $query .= " ORDER BY d.full_name ASC";
            break;

        case 'vehicles':
            $query = "SELECT v.*, d.full_name as driver_name,
                      (SELECT COUNT(*) FROM violations WHERE vehicle_id = v.vehicle_id) as violation_count
                      FROM vehicles v JOIN drivers d ON v.driver_id = d.driver_id WHERE 1=1";
            $params = [];
            if ($search) {
                $query .= " AND (v.vehicle_number ILIKE :search OR v.vehicle_type ILIKE :search OR v.manufacturer ILIKE :search OR d.full_name ILIKE :search)";
                $params[':search'] = "%{$search}%";
            }
            if ($date_from) { $query .= " AND v.created_at >= :date_from"; $params[':date_from'] = $date_from; }
            if ($date_to)   { $query .= " AND v.created_at <= :date_to"; $params[':date_to'] = $date_to . ' 23:59:59'; }
            $query .= " ORDER BY v.vehicle_number ASC";
            break;

        case 'payments':
            $query = "SELECT p.*, d.full_name as driver_name, vh.vehicle_number, vr.violation_type
                      FROM payments p
                      JOIN violations v ON p.violation_id = v.violation_id
                      JOIN drivers d ON v.driver_id = d.driver_id
                      JOIN vehicles vh ON v.vehicle_id = vh.vehicle_id
                      JOIN violation_rules vr ON v.rule_id = vr.rule_id
                      WHERE 1=1";
            $params = [];
            if ($search) {
                $query .= " AND (d.full_name ILIKE :search OR vh.vehicle_number ILIKE :search OR p.transaction_id ILIKE :search OR p.payment_method ILIKE :search)";
                $params[':search'] = "%{$search}%";
            }
            if ($date_from) { $query .= " AND p.payment_date >= :date_from"; $params[':date_from'] = $date_from; }
            if ($date_to)   { $query .= " AND p.payment_date <= :date_to"; $params[':date_to'] = $date_to; }
            $query .= " ORDER BY p.payment_date DESC";
            break;

        default: // violations
            $active_tab = 'violations';
            $query = "SELECT v.*, d.full_name as driver_name, vh.vehicle_number, vr.violation_type, vr.fine_amount
                      FROM violations v
                      JOIN drivers d ON v.driver_id = d.driver_id
                      JOIN vehicles vh ON v.vehicle_id = vh.vehicle_id
                      JOIN violation_rules vr ON v.rule_id = vr.rule_id
                      WHERE 1=1";
            $params = [];
            if ($search) {
                $query .= " AND (d.full_name ILIKE :search OR vh.vehicle_number ILIKE :search OR vr.violation_type ILIKE :search OR v.location ILIKE :search OR v.officer_name ILIKE :search)";
                $params[':search'] = "%{$search}%";
            }
            if ($date_from) { $query .= " AND v.violation_date >= :date_from"; $params[':date_from'] = $date_from; }
            if ($date_to)   { $query .= " AND v.violation_date <= :date_to"; $params[':date_to'] = $date_to; }
            $query .= " ORDER BY v.violation_date DESC";
            break;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $report_data = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Reports error: " . $e->getMessage());
    $report_data = [];
}

// ---- CSV Export ----
if ($export === 'csv' && count($report_data) > 0) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="trafficlens_' . $active_tab . '_report_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, array_keys($report_data[0]));
    foreach ($report_data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}
?>

<!-- Report Tabs -->
<div class="tabs">
    <button class="tab <?php echo $active_tab === 'violations' ? 'active' : ''; ?>" onclick="window.location='?tab=violations'">
        <i class="fas fa-exclamation-triangle"></i>&nbsp; Violations
    </button>
    <button class="tab <?php echo $active_tab === 'drivers' ? 'active' : ''; ?>" onclick="window.location='?tab=drivers'">
        <i class="fas fa-id-card"></i>&nbsp; Drivers
    </button>
    <button class="tab <?php echo $active_tab === 'vehicles' ? 'active' : ''; ?>" onclick="window.location='?tab=vehicles'">
        <i class="fas fa-car"></i>&nbsp; Vehicles
    </button>
    <button class="tab <?php echo $active_tab === 'payments' ? 'active' : ''; ?>" onclick="window.location='?tab=payments'">
        <i class="fas fa-credit-card"></i>&nbsp; Payments
    </button>
</div>

<!-- Filters -->
<div class="toolbar">
    <form method="GET" class="flex items-center gap-12" style="flex-wrap: wrap; flex: 1;">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($active_tab); ?>">
        <div class="search-box" style="max-width: 260px;">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="flex items-center gap-8">
            <label class="form-label" style="margin: 0;">From</label>
            <input type="date" name="date_from" class="form-input" style="width: auto;" value="<?php echo htmlspecialchars($date_from); ?>">
        </div>
        <div class="flex items-center gap-8">
            <label class="form-label" style="margin: 0;">To</label>
            <input type="date" name="date_to" class="form-input" style="width: auto;" value="<?php echo htmlspecialchars($date_to); ?>">
        </div>
        <button type="submit" class="btn-ghost btn-sm"><i class="fas fa-filter"></i> Filter</button>
        <a href="?tab=<?php echo $active_tab; ?>" class="btn-ghost btn-sm"><i class="fas fa-times"></i> Clear</a>
    </form>
    <div class="btn-group">
        <span class="text-meta"><?php echo count($report_data); ?> records</span>
        <?php if (count($report_data) > 0): ?>
        <a href="?tab=<?php echo $active_tab; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&export=csv" class="btn-ghost btn-sm">
            <i class="fas fa-download"></i> CSV
        </a>
        <button onclick="window.print()" class="btn-ghost btn-sm">
            <i class="fas fa-print"></i> Print
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Report Table -->
<?php if (count($report_data) > 0): ?>
<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <?php if ($active_tab === 'violations'): ?>
                    <th>Driver</th><th>Vehicle</th><th>Violation</th><th>Fine</th><th>Location</th><th>Officer</th><th>Date</th><th>Status</th>
                <?php elseif ($active_tab === 'drivers'): ?>
                    <th>Full Name</th><th>License</th><th>Phone</th><th>Email</th><th>Vehicles</th><th>Violations</th><th>Registered</th>
                <?php elseif ($active_tab === 'vehicles'): ?>
                    <th>Number</th><th>Type</th><th>Manufacturer</th><th>Model</th><th>Owner</th><th>Violations</th><th>Reg. Date</th>
                <?php elseif ($active_tab === 'payments'): ?>
                    <th>Driver</th><th>Vehicle</th><th>Violation</th><th>Amount</th><th>Method</th><th>Transaction ID</th><th>Date</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report_data as $row): ?>
            <tr>
                <?php if ($active_tab === 'violations'): ?>
                    <td><?php echo htmlspecialchars($row['driver_name']); ?></td>
                    <td class="cell-mono"><?php echo htmlspecialchars($row['vehicle_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['violation_type']); ?></td>
                    <td class="cell-mono">₹<?php echo number_format($row['fine_amount'], 2); ?></td>
                    <td class="cell-muted"><?php echo htmlspecialchars(mb_strimwidth($row['location'], 0, 30, '...')); ?></td>
                    <td class="cell-muted"><?php echo htmlspecialchars($row['officer_name']); ?></td>
                    <td class="cell-muted"><?php echo date('d M Y', strtotime($row['violation_date'])); ?></td>
                    <td><span class="badge badge-<?php echo strtolower($row['payment_status']); ?>"><span class="dot"></span> <?php echo $row['payment_status']; ?></span></td>

                <?php elseif ($active_tab === 'drivers'): ?>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td class="cell-mono"><?php echo htmlspecialchars($row['license_number']); ?></td>
                    <td class="cell-mono"><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td class="cell-muted"><?php echo htmlspecialchars($row['email'] ?? '—'); ?></td>
                    <td><?php echo $row['vehicle_count']; ?></td>
                    <td><?php echo $row['violation_count']; ?></td>
                    <td class="cell-muted"><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>

                <?php elseif ($active_tab === 'vehicles'): ?>
                    <td class="cell-mono"><?php echo htmlspecialchars($row['vehicle_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['vehicle_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['manufacturer']); ?></td>
                    <td><?php echo htmlspecialchars($row['model']); ?></td>
                    <td><?php echo htmlspecialchars($row['driver_name']); ?></td>
                    <td><?php echo $row['violation_count']; ?></td>
                    <td class="cell-muted"><?php echo date('d M Y', strtotime($row['registration_date'])); ?></td>

                <?php elseif ($active_tab === 'payments'): ?>
                    <td><?php echo htmlspecialchars($row['driver_name']); ?></td>
                    <td class="cell-mono"><?php echo htmlspecialchars($row['vehicle_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['violation_type']); ?></td>
                    <td class="cell-mono">₹<?php echo number_format($row['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                    <td class="cell-mono"><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                    <td class="cell-muted"><?php echo date('d M Y', strtotime($row['payment_date'])); ?></td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($active_tab === 'payments' && count($report_data) > 0): ?>
<div class="card mt-24" style="max-width: 300px;">
    <div class="stat-label mb-8">Total Revenue</div>
    <div class="stat-value" style="color: var(--color-pulse-green);">
        ₹<?php echo number_format(array_sum(array_column($report_data, 'amount')), 2); ?>
    </div>
</div>
<?php endif; ?>

<?php if ($active_tab === 'violations' && count($report_data) > 0): ?>
<div class="flex gap-20 mt-24">
    <div class="card" style="max-width: 200px;">
        <div class="stat-label mb-8">Total Fines</div>
        <div class="stat-value">₹<?php echo number_format(array_sum(array_column($report_data, 'fine_amount')), 2); ?></div>
    </div>
    <div class="card" style="max-width: 200px;">
        <div class="stat-label mb-8">Pending</div>
        <div class="stat-value" style="color: var(--color-warning);">
            <?php echo count(array_filter($report_data, fn($r) => $r['payment_status'] === 'Pending')); ?>
        </div>
    </div>
    <div class="card" style="max-width: 200px;">
        <div class="stat-label mb-8">Paid</div>
        <div class="stat-value" style="color: var(--color-pulse-green);">
            <?php echo count(array_filter($report_data, fn($r) => $r['payment_status'] === 'Paid')); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="empty-state">
    <i class="fas fa-chart-bar"></i>
    <p class="empty-title">No records found</p>
    <p class="empty-text">Adjust your filters or select a different report type.</p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
