<?php
/**
 * TrafficLens AI — Header Component
 * Included at the top of every authenticated page.
 * Provides: HTML head, session check, top header bar.
 * 
 * Required variable: $page_title (set before including this file)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Route protection — redirect to login if not authenticated
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin/login.php');
    exit;
}

// Default page title
if (!isset($page_title)) {
    $page_title = 'TrafficLens AI';
}

// Determine active page for sidebar highlighting
$current_page = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TrafficLens AI — Smart Traffic Violation Management System">
    <title><?php echo htmlspecialchars($page_title); ?> — TrafficLens AI</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=IBM+Plex+Mono:wght@400&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- TrafficLens AI Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app-wrapper">

        <?php include __DIR__ . '/sidebar.php'; ?>

        <div class="main-content">
            <!-- Top Header Bar -->
            <header class="top-header">
                <div class="flex items-center gap-12">
                    <button class="mobile-menu-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="page-title"><?php echo htmlspecialchars($page_title); ?></h2>
                </div>
                <div class="header-actions">
                    <a href="../admin/settings.php" class="admin-badge" style="text-decoration:none;" title="Account Settings">
                        <span class="dot"></span>
                        <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                        <i class="fas fa-cog" style="font-size:12px; margin-left:4px; color:#00e5ff;"></i>
                    </a>
                </div>
            </header>

            <div class="page-container">
