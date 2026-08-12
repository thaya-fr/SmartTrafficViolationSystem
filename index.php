<?php
/**
 * TrafficLens AI — Landing Page
 * Redirects to dashboard if logged in, otherwise to login page.
 */
require_once __DIR__ . '/config/session.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard/index.php');
} else {
    header('Location: admin/login.php');
}
exit;
