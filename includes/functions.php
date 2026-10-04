<?php

function app_url(string $path = ''): string
{
    return APP_BASE_URL . '/' . ltrim($path, '/');
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        header('Location: ' . $path);
        exit;
    }

    header('Location: ' . $path);
    exit;
}

function redirect_to(string $path): void
{
    redirect(app_url($path));
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(): void
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $postedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($sessionToken) ||
        !is_string($postedToken) ||
        $sessionToken === '' ||
        $postedToken === '' ||
        !hash_equals($sessionToken, $postedToken)
    ) {
        http_response_code(403);
        exit('Cerere respinsa: token CSRF invalid.');
    }
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Metoda HTTP nu este permisa.');
    }
}

function get_int_from_post(string $key, int $default = 0): int
{
    $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);

    if ($value === false || $value === null) {
        return $default;
    }

    return $value;
}

function get_int_from_get(string $key, int $default = 0): int
{
    $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);

    if ($value === false || $value === null) {
        return $default;
    }

    return $value;
}

function clean_text(string $value, int $maxLength): string
{
    $value = trim($value);
    $value = preg_replace('/[[:cntrl:]]/', '', $value);

    if (mb_strlen($value, 'UTF-8') > $maxLength) {
        $value = mb_substr($value, 0, $maxLength, 'UTF-8');
    }

    return $value;
}

function safe_uploaded_profile_path(string $relativePath): ?string
{
    if ($relativePath === '' || !str_starts_with($relativePath, 'uploads/profiles/')) {
        return null;
    }

    $baseDir = realpath(__DIR__ . '/../uploads/profiles');
    if ($baseDir === false) {
        return null;
    }

    $fileName = basename($relativePath);
    $targetPath = realpath($baseDir . DIRECTORY_SEPARATOR . $fileName);

    if ($targetPath === false) {
        return null;
    }

    $baseDirWithSeparator = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    if (!str_starts_with($targetPath, $baseDirWithSeparator)) {
        return null;
    }

    if (!is_file($targetPath)) {
        return null;
    }

    return $targetPath;
}

function log_action($userId, string $action, string $details = ''): void
{
    global $sqlite;

    try {
        if (!isset($sqlite)) {
            return;
        }

        $stmt = $sqlite->prepare("
            INSERT INTO app_logs (user_id, action, details)
            VALUES (:user_id, :action, :details)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':action' => clean_text($action, 100),
            ':details' => clean_text($details, 500)
        ]);
    } catch (Exception $exception) {
        // Nu oprim aplicatia daca logarea SQLite nu functioneaza.
    }
}

function current_user_id()
{
    return $_SESSION['user']['id'] ?? null;
}

function current_user_role()
{
    return $_SESSION['user']['role'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}