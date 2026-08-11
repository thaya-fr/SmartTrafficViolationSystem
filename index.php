<?php
/**
 * TrafficLens AI — Landing Page
 * Redirects to dashboard if logged in, otherwise to login page.
 */
session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard/index.php');
} else {
    header('Location: admin/login.php');
}
exit;
