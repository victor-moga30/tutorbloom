<?php
require_once __DIR__ . '/../includes/init.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $profileId = get_int_from_post('profile_id');
    $action = $_POST['action'] ?? '';

    if (in_array($action, ['approved', 'rejected'], true) && $profileId > 0) {
        $stmt = $pdo->prepare("
            UPDATE tutor_profiles
            SET status = :status
            WHERE id = :id
              AND status = 'pending'
        ");

        $stmt->execute([
                ':status' => $action,
                ':id' => $profileId
        ]);

        log_action(current_user_id(), 'tutor_' . $action, 'Admin reviewed pending tutor profile.');
    }

    redirect_to('admin/review_tutors.php');
}

$stmt = $pdo->query("
    SELECT 
        tp.*, 
        u.full_name, 
        u.email,
        GROUP_CONCAT(DISTINCT CONCAT(s.name, ' - ', ts.level) SEPARATOR ', ') AS subjects
    FROM tutor_profiles tp
    JOIN users u ON u.id = tp.user_id
    LEFT JOIN tutor_subjects ts ON ts.tutor_profile_id = tp.id
    LEFT JOIN subjects s ON s.id = ts.subject_id
    GROUP BY tp.id
    ORDER BY FIELD(tp.status, 'pending', 'approved', 'rejected'), tp.created_at DESC
");

$tutors = $stmt->fetchAll();

$pageTitle = 'TutorBloom - Review tutori';
$pageHeading = 'Review tutori';
$pageSubtitle = 'Administreaza aprobarea profilurilor de tutor';
$bodyId = 'dashboard-page';

require_once __DIR__ . '/../includes/header.php';
?>

    <section class="content-section">
        <h3>Administrare tutori</h3>

        <p>
            <a class="button-link" href="<?= e(app_url('admin/edit_tutors_ajax.php')) ?>">
                Editare tutori cu vanilla AJAX
            </a>

            <a class="button-link" href="<?= e(app_url('admin/edit_tutors_jquery.php')) ?>">
                Editare tutori cu jQuery
            </a>
        </p>
    </section>

    <section class="content-section">
        <h3>Lista tutorilor</h3>

        <div class="table-wrapper results-wrapper">
            <table class="results-table">
                <thead>
                <tr>
                    <th>Poza</th>
                    <th>Nume</th>
                    <th>Email</th>
                    <th>Descriere</th>
                    <th>Materii</th>
                    <th>Pret</th>
                    <th>Status</th>
                    <th>Actiuni</th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($tutors as $tutor): ?>
                    <?php
                    $photoPath = $tutor['profile_photo'] ?: 'assets/images/tutor1.jpg';
                    ?>

                    <tr>
                        <td data-label="Poza">
                            <img
                                    src="<?= e(app_url($photoPath)) ?>"
                                    width="70"
                                    alt="Tutor"
                            >
                        </td>

                        <td data-label="Nume"><?= e($tutor['full_name']) ?></td>
                        <td data-label="Email"><?= e($tutor['email']) ?></td>
                        <td data-label="Descriere"><?= e($tutor['description']) ?></td>
                        <td data-label="Materii"><?= e($tutor['subjects']) ?></td>
                        <td data-label="Pret"><?= e($tutor['price_per_hour']) ?> lei</td>

                        <td data-label="Status">
                            <strong><?= e($tutor['status']) ?></strong>
                        </td>

                        <td data-label="Actiuni">
                            <?php if ($tutor['status'] === 'pending'): ?>
                                <form method="post" action="<?= e(app_url('admin/review_tutors.php')) ?>">
                                    <?= csrf_field() ?>

                                    <input
                                            type="hidden"
                                            name="profile_id"
                                            value="<?= (int)$tutor['id'] ?>"
                                    >

                                    <button type="submit" name="action" value="approved">
                                        Aproba
                                    </button>

                                    <button type="submit" name="action" value="rejected">
                                        Respinge
                                    </button>
                                </form>
                            <?php elseif ($tutor['status'] === 'approved'): ?>
                                <span class="profile-tag">Aprobat</span>
                            <?php else: ?>
                                <span class="profile-tag">Respins</span>
                            <?php endif; ?>

                            <br>
                            <a href="<?= e(app_url('admin/edit_tutors_ajax.php?id=' . (int)$tutor['id'])) ?>">
                                Editeaza
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$tutors): ?>
                    <tr>
                        <td colspan="8">Nu exista tutori.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>