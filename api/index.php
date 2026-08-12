<?php
/**
 * TrafficLens AI — Vercel Serverless PHP Router
 * Handles request routing across all subdirectories on Vercel platform.
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Root page
if ($uri === '/' || $uri === '' || $uri === '/index.php') {
    require_once __DIR__ . '/../index.php';
    exit;
}

$file_path = __DIR__ . '/..' . $uri;

// If path is a directory, append index.php
if (is_dir($file_path)) {
    $file_path = rtrim($file_path, '/') . '/index.php';
}

// If PHP file exists, change directory and execute
if (file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'php') {
    chdir(dirname($file_path));
    require_once $file_path;
    exit;
}

// Fallback to landing page
require_once __DIR__ . '/../index.php';
