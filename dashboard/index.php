<?php
/**
 * TrafficLens AI — Dashboard
 * Central hub displaying statistics, charts, and recent activity.
 */
$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

// ---- Fetch Statistics ----
try {
    // Total Drivers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM drivers");
    $total_drivers = $stmt->fetch()['total'];

    // Total Vehicles
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM vehicles");
    $total_vehicles = $stmt->fetch()['total'];

    // Total Violations
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM violations");
    $total_violations = $stmt->fetch()['total'];

    // Total Payments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM payments");
    $total_payments = $stmt->fetch()['total'];

    // Total Revenue
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments");
    $total_revenue = $stmt->fetch()['total'];

    // Pending Payments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM violations WHERE payment_status = 'Pending'");
    $pending_payments = $stmt->fetch()['total'];

    // Monthly Violations (last 6 months)
    $stmt = $pdo->query("
        SELECT 
            TO_CHAR(violation_date, 'Mon') as month,
            TO_CHAR(violation_date, 'YYYY-MM') as sort_key,
            COUNT(*) as count
        FROM violations 
        WHERE violation_date >= CURRENT_DATE - INTERVAL '6 months'
        GROUP BY TO_CHAR(violation_date, 'Mon'), TO_CHAR(violation_date, 'YYYY-MM')
        ORDER BY sort_key ASC
    ");
    $monthly_violations = $stmt->fetchAll();

    // Monthly Revenue (last 6 months)
    $stmt = $pdo->query("
        SELECT 
            TO_CHAR(payment_date, 'Mon') as month,
            TO_CHAR(payment_date, 'YYYY-MM') as sort_key,
            COALESCE(SUM(amount), 0) as total
        FROM payments 
        WHERE payment_date >= CURRENT_DATE - INTERVAL '6 months'
        GROUP BY TO_CHAR(payment_date, 'Mon'), TO_CHAR(payment_date, 'YYYY-MM')
        ORDER BY sort_key ASC
    ");
    $monthly_revenue = $stmt->fetchAll();

    // Payment Status Distribution
    $stmt = $pdo->query("
        SELECT payment_status, COUNT(*) as count 
        FROM violations 
        GROUP BY payment_status
    ");
    $payment_status_dist = $stmt->fetchAll();

    // Vehicle Type Distribution
    $stmt = $pdo->query("
        SELECT vehicle_type, COUNT(*) as count 
        FROM vehicles 
        GROUP BY vehicle_type 
        ORDER BY count DESC 
        LIMIT 6
    ");
    $vehicle_types = $stmt->fetchAll();

    // Recent Violations (last 10)
    $stmt = $pdo->query("
        SELECT 
            v.violation_id,
            d.full_name as driver_name,
            vh.vehicle_number,
            vr.violation_type,
            vr.fine_amount,
            v.location,
            v.violation_date,
            v.payment_status
        FROM violations v
        JOIN drivers d ON v.driver_id = d.driver_id
        JOIN vehicles vh ON v.vehicle_id = vh.vehicle_id
        JOIN violation_rules vr ON v.rule_id = vr.rule_id
        ORDER BY v.created_at DESC
        LIMIT 10
    ");
    $recent_violations = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $total_drivers = $total_vehicles = $total_violations = $total_payments = 0;
    $total_revenue = 0;
    $pending_payments = 0;
    $monthly_violations = $monthly_revenue = $payment_status_dist = $vehicle_types = $recent_violations = [];
}

// Prepare chart data
$chart_months = array_column($monthly_violations, 'month');
$chart_violations = array_column($monthly_violations, 'count');
$chart_rev_months = array_column($monthly_revenue, 'month');
$chart_rev_amounts = array_column($monthly_revenue, 'total');

$status_labels = array_column($payment_status_dist, 'payment_status');
$status_counts = array_column($payment_status_dist, 'count');

$vtype_labels = array_column($vehicle_types, 'vehicle_type');
$vtype_counts = array_column($vehicle_types, 'count');
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-id-card"></i>
        </div>
        <div class="stat-value"><?php echo number_format($total_drivers); ?></div>
        <div class="stat-label">Total Drivers</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-car"></i>
        </div>
        <div class="stat-value"><?php echo number_format($total_vehicles); ?></div>
        <div class="stat-label">Total Vehicles</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-value"><?php echo number_format($total_violations); ?></div>
        <div class="stat-label">Total Violations</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-credit-card"></i>
        </div>
        <div class="stat-value"><?php echo number_format($total_payments); ?></div>
        <div class="stat-label">Total Payments</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="border-color: var(--color-pulse-green); color: var(--color-pulse-green);">
            <i class="fas fa-indian-rupee-sign"></i>
        </div>
        <div class="stat-value">₹<?php echo number_format($total_revenue, 2); ?></div>
        <div class="stat-label">Revenue Collected</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="border-color: var(--color-warning); color: var(--color-warning);">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-value"><?php echo number_format($pending_payments); ?></div>
        <div class="stat-label">Pending Payments</div>
    </div>
</div>

<!-- Charts Grid -->
<div class="charts-grid">
    <!-- Monthly Violations Bar Chart -->
    <div class="chart-card">
        <div class="chart-title">Monthly Violations</div>
        <div class="chart-container">
            <canvas id="violationsChart"></canvas>
        </div>
    </div>

    <!-- Revenue Trend Line Chart -->
    <div class="chart-card">
        <div class="chart-title">Revenue Trend</div>
        <div class="chart-container">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Payment Status Pie Chart -->
    <div class="chart-card">
        <div class="chart-title">Payment Status</div>
        <div class="chart-container">
            <canvas id="paymentStatusChart"></canvas>
        </div>
    </div>

    <!-- Vehicle Type Distribution -->
    <div class="chart-card">
        <div class="chart-title">Vehicle Types</div>
        <div class="chart-container">
            <canvas id="vehicleTypeChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Violations Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Violations</h3>
        <a href="../violations/view_violations.php" class="btn-ghost btn-sm">
            View All <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <?php if (count($recent_violations) > 0): ?>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Driver</th>
                    <th>Vehicle</th>
                    <th>Violation</th>
                    <th>Fine</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_violations as $v): ?>
                <tr>
                    <td><?php echo htmlspecialchars($v['driver_name']); ?></td>
                    <td class="cell-mono"><?php echo htmlspecialchars($v['vehicle_number']); ?></td>
                    <td><?php echo htmlspecialchars($v['violation_type']); ?></td>
                    <td class="cell-mono">₹<?php echo number_format($v['fine_amount'], 2); ?></td>
                    <td class="cell-muted"><?php echo htmlspecialchars($v['location']); ?></td>
                    <td class="cell-muted"><?php echo date('d M Y', strtotime($v['violation_date'])); ?></td>
                    <td>
                        <span class="badge badge-<?php echo strtolower($v['payment_status']); ?>">
                            <span class="dot"></span>
                            <?php echo htmlspecialchars($v['payment_status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-clipboard-list"></i>
        <p class="empty-title">No violations recorded</p>
        <p class="empty-text">Start by recording your first traffic violation.</p>
        <a href="../violations/add_violation.php" class="btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Violation
        </a>
    </div>
    <?php endif; ?>
</div>

<?php
// Chart initialization scripts
$page_scripts = '
<script>
    // Chart.js defaults for Hyperstudio theme
    Chart.defaults.color = "#9c9c9c";
    Chart.defaults.borderColor = "#212121";
    Chart.defaults.font.family = "Inter, sans-serif";
    Chart.defaults.font.weight = 400;
    Chart.defaults.font.size = 12;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.pointStyle = "circle";
    Chart.defaults.plugins.legend.labels.padding = 16;

    // Monthly Violations Bar Chart
    new Chart(document.getElementById("violationsChart"), {
        type: "bar",
        data: {
            labels: ' . json_encode($chart_months) . ',
            datasets: [{
                label: "Violations",
                data: ' . json_encode($chart_violations) . ',
                backgroundColor: "#f3f3f3",
                borderColor: "transparent",
                borderRadius: 4,
                barThickness: 24,
                maxBarThickness: 32
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, border: { color: "#212121" } },
                y: { grid: { color: "#212121" }, border: { display: false }, beginAtZero: true }
            }
        }
    });

    // Revenue Trend Line Chart
    new Chart(document.getElementById("revenueChart"), {
        type: "line",
        data: {
            labels: ' . json_encode($chart_rev_months) . ',
            datasets: [{
                label: "Revenue (₹)",
                data: ' . json_encode($chart_rev_amounts) . ',
                borderColor: "#98ff38",
                backgroundColor: "rgba(152, 255, 56, 0.05)",
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: "#98ff38",
                pointBorderColor: "#101010",
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, border: { color: "#212121" } },
                y: { grid: { color: "#212121" }, border: { display: false }, beginAtZero: true }
            }
        }
    });

    // Payment Status Pie Chart
    new Chart(document.getElementById("paymentStatusChart"), {
        type: "doughnut",
        data: {
            labels: ' . json_encode($status_labels) . ',
            datasets: [{
                data: ' . json_encode($status_counts) . ',
                backgroundColor: ["#98ff38", "#ffb020", "#ff4d4d"],
                borderColor: "#101010",
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "65%",
            plugins: {
                legend: { position: "bottom" }
            }
        }
    });

    // Vehicle Type Chart
    new Chart(document.getElementById("vehicleTypeChart"), {
        type: "bar",
        data: {
            labels: ' . json_encode($vtype_labels) . ',
            datasets: [{
                label: "Vehicles",
                data: ' . json_encode($vtype_counts) . ',
                backgroundColor: "#6f6759",
                borderColor: "transparent",
                borderRadius: 4,
                barThickness: 24
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: "y",
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: "#212121" }, border: { display: false }, beginAtZero: true },
                y: { grid: { display: false }, border: { color: "#212121" } }
            }
        }
    });
</script>';

require_once __DIR__ . '/../includes/footer.php';
?>
