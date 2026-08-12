<?php
/**
 * TrafficLens AI — Stateless Session Handler for Serverless Deployments (Vercel)
 * Uses HMAC-SHA256 signed HTTP cookies to persist sessions across stateless containers.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session_secret = getenv('SESSION_SECRET') ?: 'TrafficLensAI_Vercel_Session_Secret_Key_2026';

/**
 * Set Admin Session & Signed Cookie
 */
function set_user_session($admin_id, $username, $email) {
    global $session_secret;
    $_SESSION['admin_id'] = $admin_id;
    $_SESSION['username'] = $username;
    $_SESSION['email']    = $email;

    $payload = json_encode([
        'type'     => 'admin',
        'admin_id' => $admin_id,
        'username' => $username,
        'email'    => $email,
        'exp'      => time() + (86400 * 7) // 7 days
    ]);

    $sig = hash_hmac('sha256', $payload, $session_secret);
    $cookie_val = base64_encode($payload) . '.' . $sig;

    setcookie('tl_admin_sess', $cookie_val, [
        'expires'  => time() + (86400 * 7),
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

/**
 * Restore Admin Session from Signed Cookie if missing from $_SESSION
 */
function restore_user_session() {
    global $session_secret;
    if (isset($_SESSION['admin_id'])) {
        return;
    }

    if (!empty($_COOKIE['tl_admin_sess'])) {
        $parts = explode('.', $_COOKIE['tl_admin_sess']);
        if (count($parts) === 2) {
            $payload_raw = base64_decode($parts[0]);
            $sig = $parts[1];

            $expected_sig = hash_hmac('sha256', $payload_raw, $session_secret);
            if (hash_equals($expected_sig, $sig)) {
                $data = json_decode($payload_raw, true);
                if ($data && isset($data['exp']) && $data['exp'] > time() && ($data['type'] ?? '') === 'admin') {
                    $_SESSION['admin_id'] = $data['admin_id'];
                    $_SESSION['username'] = $data['username'];
                    $_SESSION['email']    = $data['email'];
                }
            }
        }
    }
}

/**
 * Clear Admin Session
 */
function clear_user_session() {
    unset($_SESSION['admin_id'], $_SESSION['username'], $_SESSION['email']);
    if (isset($_COOKIE['tl_admin_sess'])) {
        setcookie('tl_admin_sess', '', time() - 3600, '/');
    }
}

/**
 * Set Driver Session & Signed Cookie
 */
function set_driver_session($driver_id, $driver_name, $driver_license) {
    global $session_secret;
    $_SESSION['driver_id']      = $driver_id;
    $_SESSION['driver_name']    = $driver_name;
    $_SESSION['driver_license'] = $driver_license;

    $payload = json_encode([
        'type'           => 'driver',
        'driver_id'      => $driver_id,
        'driver_name'    => $driver_name,
        'driver_license' => $driver_license,
        'exp'            => time() + (86400 * 2) // 2 days
    ]);

    $sig = hash_hmac('sha256', $payload, $session_secret);
    $cookie_val = base64_encode($payload) . '.' . $sig;

    setcookie('tl_driver_sess', $cookie_val, [
        'expires'  => time() + (86400 * 2),
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

/**
 * Restore Driver Session from Signed Cookie
 */
function restore_driver_session() {
    global $session_secret;
    if (isset($_SESSION['driver_id'])) {
        return;
    }

    if (!empty($_COOKIE['tl_driver_sess'])) {
        $parts = explode('.', $_COOKIE['tl_driver_sess']);
        if (count($parts) === 2) {
            $payload_raw = base64_decode($parts[0]);
            $sig = $parts[1];

            $expected_sig = hash_hmac('sha256', $payload_raw, $session_secret);
            if (hash_equals($expected_sig, $sig)) {
                $data = json_decode($payload_raw, true);
                if ($data && isset($data['exp']) && $data['exp'] > time() && ($data['type'] ?? '') === 'driver') {
                    $_SESSION['driver_id']      = $data['driver_id'];
                    $_SESSION['driver_name']    = $data['driver_name'];
                    $_SESSION['driver_license'] = $data['driver_license'];
                }
            }
        }
    }
}

/**
 * Clear Driver Session
 */
function clear_driver_session() {
    unset($_SESSION['driver_id'], $_SESSION['driver_name'], $_SESSION['driver_license']);
    if (isset($_COOKIE['tl_driver_sess'])) {
        setcookie('tl_driver_sess', '', time() - 3600, '/');
    }
}

// Automatically attempt session restoration on file import
restore_user_session();
restore_driver_session();
