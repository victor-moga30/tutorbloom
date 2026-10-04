<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect_by_role(current_user_role());
}

$error = '';

if (!isset($_SESSION['captcha_a'], $_SESSION['captcha_b'])) {
    $_SESSION['captcha_a'] = random_int(1, 9);
    $_SESSION['captcha_b'] = random_int(1, 9);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $username = clean_text($_POST['username'] ?? '', 50);
    $password = $_POST['password'] ?? '';
    $captcha = (int)($_POST['captcha'] ?? -1);
    $remember = isset($_POST['remember']);

    $correctCaptcha = $_SESSION['captcha_a'] + $_SESSION['captcha_b'];

    if ($captcha !== $correctCaptcha) {
        $error = 'CAPTCHA incorect.';
    } elseif ($username === '' || $password === '') {
        $error = 'Completeaza username si parola.';
    } else {
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            login_user($user);

            if ($remember) {
                create_remember_token((int)$user['id']);
            }

            unset($_SESSION['captcha_a'], $_SESSION['captcha_b']);

            redirect_by_role($user['role']);
        } else {
            $error = 'Username sau parola gresita.';
        }
    }

    $_SESSION['captcha_a'] = random_int(1, 9);
    $_SESSION['captcha_b'] = random_int(1, 9);
}

$pageTitle = 'Login';
$bodyId = 'login-page';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>TutorBloom - Login</title>
    <link rel="stylesheet" href="<?= e(app_url('assets/css/style-responsive.css')) ?>">
</head>
<body id="login-page">

<header class="site-header">
    <h1>TutorBloom</h1>
    <h2>Autentificare</h2>
</header>

<main class="page-content">
    <section class="content-section">
        <h3>Intra in cont</h3>

        <?php if ($error): ?>
            <p class="error-message"><strong><?= e($error) ?></strong></p>
        <?php endif; ?>

        <form method="post" class="styled-form" autocomplete="off">
            <?= csrf_field() ?>

            <fieldset>
                <legend><b>Date autentificare</b></legend>

                <p>
                    <label for="username">Username</label><br>
                    <input id="username" type="text" name="username" maxlength="50" required>
                </p>

                <p>
                    <label for="password">Parola</label><br>
                    <input id="password" type="password" name="password" required>
                </p>

                <p>
                    <label for="captcha">
                        CAPTCHA:
                        Cat face <?= (int)$_SESSION['captcha_a'] ?> + <?= (int)$_SESSION['captcha_b'] ?>?
                    </label><br>
                    <input id="captcha" type="number" name="captcha" required>
                </p>

                <p>
                    <label>
                        <input type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                </p>

                <p>
                    <button type="submit">Login</button>
                </p>
            </fieldset>
        </form>
        <p>
            Nu ai cont?
            <a href="<?= e(app_url('register.php')) ?>">Creeaza cont</a>
        </p>
    </section>
</main>

<footer class="site-footer">
    <p>&copy; 2026 TutorBloom</p>
</footer>

</body>
</html>