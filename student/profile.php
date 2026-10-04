<?php
require_once __DIR__ . '/../includes/init.php';
require_role('student');

$message = '';

$userId = (int)current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $fullName = clean_text($_POST['full_name'] ?? '', 100);

    if ($fullName === '') {
        $message = 'Numele nu poate fi gol.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE users
            SET full_name = :full_name
            WHERE id = :user_id
              AND role = 'student'
        ");

        $stmt->execute([
            ':full_name' => $fullName,
            ':user_id' => $userId
        ]);

        $stmt = $pdo->prepare("
            SELECT id, username, full_name, email, role
            FROM users
            WHERE id = :user_id
            LIMIT 1
        ");

        $stmt->execute([
            ':user_id' => $userId
        ]);

        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
        }

        log_action($userId, 'student_profile_update', 'Student updated profile safely.');

        $message = 'Profilul a fost salvat in siguranta.';
    }
}

$stmt = $pdo->prepare("
    SELECT id, username, full_name, email, role
    FROM users
    WHERE id = :user_id
    LIMIT 1
");

$stmt->execute([
    ':user_id' => $userId
]);

$user = $stmt->fetch();

$pageTitle = 'Profil student';
$pageHeading = 'Profil student';
$pageSubtitle = 'High security: editarea numelui foloseste prepared statements si CSRF';
$bodyId = 'profile-page';

require_once __DIR__ . '/../includes/header.php';
?>

    <section class="content-section">
        <h3>Editare profil student</h3>

        <p>
            User curent:
            <strong><?= e($user['username']) ?></strong>,
            ID:
            <strong><?= (int)$user['id'] ?></strong>,
            rol:
            <strong><?= e($user['role']) ?></strong>
        </p>

        <?php if ($message): ?>
            <p class="success-message"><?= e($message) ?></p>
        <?php endif; ?>

        <form method="post" class="styled-form">
            <?= csrf_field() ?>

            <fieldset>
                <legend><b>Date student</b></legend>

                <p>
                    <label for="full_name">Nume complet</label><br>
                    <input
                        id="full_name"
                        type="text"
                        name="full_name"
                        value="<?= e($user['full_name']) ?>"
                        maxlength="100"
                        style="width:100%;"
                        required
                    >
                </p>

                <p>
                    <button type="submit">Salveaza profil</button>
                </p>
            </fieldset>
        </form>

        <hr>

        <p><strong>Test SQL Injection pentru high_security:</strong></p>

        <pre>Victor', role='admin' WHERE id = <?= (int)$user['id'] ?> -- </pre>

        <p>
            In high_security, acest payload trebuie salvat ca text normal in campul nume.
            Rolul trebuie sa ramana student.
        </p>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>