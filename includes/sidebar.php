<?php
/**
 * TrafficLens AI — Sidebar Navigation Component
 * Included inside header.php — renders the sidebar with navigation links.
 * Uses $current_page to highlight the active link.
 */
?>
<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="fas fa-video"></i>
        </div>
        <div class="brand-text">Traffic<span>Lens</span> AI</div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Main</div>

        <a href="../dashboard/index.php" class="<?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>

        <div class="sidebar-section-label">Management</div>

        <a href="../drivers/view_drivers.php" class="<?php echo ($current_page === 'drivers') ? 'active' : ''; ?>">
            <i class="fas fa-id-card"></i>
            <span>Drivers</span>
        </a>

        <a href="../vehicles/view_vehicles.php" class="<?php echo ($current_page === 'vehicles') ? 'active' : ''; ?>">
            <i class="fas fa-car"></i>
            <span>Vehicles</span>
        </a>

        <a href="../violation_rules/view_rules.php" class="<?php echo ($current_page === 'violation_rules') ? 'active' : ''; ?>">
            <i class="fas fa-gavel"></i>
            <span>Violation Rules</span>
        </a>

        <a href="../violations/view_violations.php" class="<?php echo ($current_page === 'violations') ? 'active' : ''; ?>">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Violations</span>
        </a>

        <a href="../payments/view_payments.php" class="<?php echo ($current_page === 'payments') ? 'active' : ''; ?>">
            <i class="fas fa-credit-card"></i>
            <span>Payments</span>
        </a>

        <div class="sidebar-section-label">Analytics & Account</div>

        <a href="../reports/reports.php" class="<?php echo ($current_page === 'reports') ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>

        <a href="../admin/settings.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'settings.php') ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
    </nav>

    <!-- Footer / Logout -->
    <div class="sidebar-footer">
        <a href="../admin/logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
