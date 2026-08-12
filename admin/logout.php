<?php
/**
 * TrafficLens AI — Logout Handler
 * Destroys session and redirects to login page.
 */
require_once __DIR__ . '/../config/session.php';
clear_user_session();

// Redirect to login
header('Location: login.php');
exit;
