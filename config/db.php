<?php
/**
 * TrafficLens AI — Database Connection
 * 
 * Centralized PDO connection to Supabase PostgreSQL.
 * Credentials are loaded from environment variables.
 * 
 * Required Environment Variables:
 *   SUPABASE_HOST
 *   SUPABASE_PORT
 *   SUPABASE_DATABASE
 *   SUPABASE_USERNAME
 *   SUPABASE_PASSWORD
 */

// Load environment variables (fallback to defaults for development)
$supabase_host     = getenv('SUPABASE_HOST')     ?: 'aws-0-ap-northeast-1.pooler.supabase.com';
$supabase_port     = getenv('SUPABASE_PORT')     ?: '6543';
$supabase_database = getenv('SUPABASE_DATABASE') ?: 'postgres';
$supabase_username = getenv('SUPABASE_USERNAME') ?: 'postgres.khpkjlmsmyxrjvlaccnh';
$supabase_password = getenv('SUPABASE_PASSWORD') ?: 'Thaya@023008';

$dsn = "pgsql:host={$supabase_host};port={$supabase_port};dbname={$supabase_database};sslmode=require";

try {
    $pdo = new PDO($dsn, $supabase_username, $supabase_password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Display actual error message for debugging
    die("<div style='background:#101010;color:#ff4d4d;padding:24px;font-family:sans-serif;border-radius:8px;max-width:600px;margin:40px auto;border:1px solid #212121;'>" .
        "<h3 style='margin-top:0;'>Database Connection Failed</h3>" .
        "<p style='color:#f3f3f3;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>" .
        "<p style='color:#9c9c9c;font-size:14px;'>Host: {$supabase_host} | Port: {$supabase_port} | Database: {$supabase_database}</p>" .
        "</div>");
}
