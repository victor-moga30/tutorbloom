<?php

function detect_app_base_url(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $projectFolder = '/tutorbloom';

    $position = strpos($scriptName, $projectFolder);

    if ($position !== false) {
        return substr($scriptName, 0, $position + strlen($projectFolder));
    }

    return $projectFolder;
}

define('APP_BASE_URL', detect_app_base_url());
function is_https_request(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
    }

    return false;
}

function is_local_request(): bool
{
    $host = $_SERVER['HTTP_HOST'] ?? '';

    return str_starts_with($host, 'localhost')
        || str_starts_with($host, '127.0.0.1')
        || str_starts_with($host, '[::1]');
}

function force_https_outside_localhost(): void
{
    if (is_local_request()) {
        return;
    }

    if (!is_https_request()) {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        header('Location: https://' . $host . $uri, true, 301);
        exit;
    }
}

function send_security_headers(): void
{
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");
}

force_https_outside_localhost();

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

send_security_headers();

require_once __DIR__ . '/../config/mysqli.php';
require_once __DIR__ . '/../config/pdo.php';
require_once __DIR__ . '/../config/sqlite.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

check_remember_me();