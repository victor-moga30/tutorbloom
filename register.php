<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect_by_role(current_user_role());
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $username = clean_text($_POST['username'] ?? '', 50);
    $fullName = clean_text($_POST['full_name'] ?? '', 100);
    $email = clean_text($_POST['email'] ?? '', 120);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    $allowedRoles = ['student', 'tutor'];

    if (!in_array($role, $allowedRoles, true)) {
        $error = 'Rol invalid.';
    } elseif ($username === '' || $fullName === '' || $email === '' || $password === '') {
        $error = 'Completeaza toate campurile.';
    } elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
        $error = 'Username invalid. Foloseste 3-50 caractere: litere, cifre, punct, underscore sau minus.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalid.';
    } elseif (strlen($password) < 8) {
        $error = 'Parola trebuie sa aiba minim 8 caractere.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Parolele nu coincid.';
    } else {
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO users (username, password_hash, full_name, email, role)
                VALUES (:username, :password_hash, :full_name, :email, :role)
            ");
            $stmt->execute([
                ':username' => $username,
                ':password_hash' => $passwordHash,
                ':full_name' => $fullName,
                ':email' => $email,
                ':role' => $role
            ]);

            $newUserId = (int)$pdo->lastInsertId();

            if ($role === 'tutor') {
                $stmt = $pdo->prepare("
                    INSERT INTO tutor_profiles (user_id, description, status)
                    VALUES (:user_id, '', 'pending')
                ");
                $stmt->execute([':user_id' => $newUserId]);
            }

            $pdo->commit();

            log_action($newUserId, 'register', 'New user registered with role ' . $role);

            $success = 'Cont creat cu succes. Acum te poti autentifica.';
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'Username sau email deja folosit.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>TutorBloom - Register</title>
    <link rel="stylesheet" href="<?= e(app_url('assets/css/style-responsive.css')) ?>">
</head>
<body id="login-page">

<header class="site-header">
    <h1>TutorBloom</h1>
    <h2>Inregistrare</h2>
    <p class="subtitle">Creeaza un cont nou</p>
</header>

<main class="page-content">
    <section class="content-section">
        <h3>Register</h3>

        <?php if ($error): ?>
            <p class="error-message"><?= e($error) ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success-message"><?= e($success) ?></p>
            <p><a href="<?= e(app_url('login.php')) ?>">Mergi la login</a></p>
        <?php else: ?>
            <form method="post" class="styled-form">
                <?= csrf_field() ?>

                <p>
                    <label for="username">Username</label><br>
                    <input id="username" type="text" name="username" maxlength="50" required>
                </p>

                <p>
                    <label for="full_name">Nume complet</label><br>
                    <input id="full_name" type="text" name="full_name" maxlength="100" required>
                </p>

                <p>
                    <label for="email">Email</label><br>
                    <input id="email" type="email" name="email" maxlength="120" required>
                </p>

                <p>
                    <label for="password">Parola</label><br>
                    <input id="password" type="password" name="password" minlength="8" required>
                </p>

                <p>
                    <label for="confirm_password">Confirma parola</label><br>
                    <input id="confirm_password" type="password" name="confirm_password" minlength="8" required>
                </p>

                <p>
                    <label for="role">Rol</label><br>
                    <select id="role" name="role" required>
                        <option value="student">Student</option>
                        <option value="tutor">Tutor</option>
                    </select>
                </p>

                <button type="submit">Creeaza cont</button>
            </form>

            <p><a href="<?= e(app_url('login.php')) ?>">Ai deja cont? Login</a></p>
        <?php endif; ?>
    </section>
</main>

<footer class="site-footer">
    <p>&copy; 2026 TutorBloom</p>
</footer>

</body>
</html>