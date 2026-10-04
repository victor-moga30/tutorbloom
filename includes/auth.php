<?php

function login_user(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role']
    ];

    log_action((int)$user['id'], 'login', 'User logged in.');
}

function logout_user(): void
{
    global $mysqli;

    if (isset($_COOKIE['remember_me'])) {
        $parts = explode(':', $_COOKIE['remember_me']);

        if (count($parts) === 2) {
            $selector = $parts[0];

            $stmt = $mysqli->prepare("DELETE FROM remember_tokens WHERE selector = ?");
            $stmt->bind_param('s', $selector);
            $stmt->execute();
        }

        setcookie('remember_me', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => is_https_request(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    if (isset($_SESSION['user'])) {
        log_action((int)$_SESSION['user']['id'], 'logout', 'User logged out.');
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => is_https_request(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    session_destroy();
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect_to('login.php');
    }
}

function require_role($roles): void
{
    require_login();

    if (!is_array($roles)) {
        $roles = [$roles];
    }

    if (!in_array(current_user_role(), $roles, true)) {
        http_response_code(403);
        exit('Acces interzis. Nu ai rolul necesar pentru aceasta pagina.');
    }
}

function redirect_by_role(string $role): void
{
    if ($role === 'student') {
        redirect_to('student/dashboard.php');
    }

    if ($role === 'tutor') {
        redirect_to('tutor/dashboard.php');
    }

    if ($role === 'admin') {
        redirect_to('admin/dashboard.php');
    }

    redirect_to('login.php');
}

function create_remember_token(int $userId): void
{
    global $mysqli;

    $selector = bin2hex(random_bytes(12));
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expires = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);

    $stmt = $mysqli->prepare("
        INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('isss', $userId, $selector, $tokenHash, $expires);
    $stmt->execute();

    setcookie('remember_me', $selector . ':' . $token, [
        'expires' => time() + 60 * 60 * 24 * 30,
        'path' => '/',
        'secure' => is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function check_remember_me(): void
{
    global $mysqli;

    if (is_logged_in()) {
        return;
    }

    if (empty($_COOKIE['remember_me'])) {
        return;
    }

    $parts = explode(':', $_COOKIE['remember_me']);

    if (count($parts) !== 2) {
        return;
    }

    [$selector, $token] = $parts;

    if (!ctype_xdigit($selector) || !ctype_xdigit($token)) {
        return;
    }

    $tokenHash = hash('sha256', $token);

    $stmt = $mysqli->prepare("
        SELECT rt.token_hash, rt.expires_at, u.*
        FROM remember_tokens rt
        JOIN users u ON u.id = rt.user_id
        WHERE rt.selector = ?
        LIMIT 1
    ");
    $stmt->bind_param('s', $selector);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return;
    }

    if (strtotime($row['expires_at']) < time()) {
        $delete = $mysqli->prepare("DELETE FROM remember_tokens WHERE selector = ?");
        $delete->bind_param('s', $selector);
        $delete->execute();
        return;
    }

    if (!hash_equals($row['token_hash'], $tokenHash)) {
        return;
    }

    login_user($row);
}