<?php
require_once __DIR__ . '/../includes/init.php';
require_role('student');

$tutorId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT tp.*, u.full_name, u.email,
           GROUP_CONCAT(DISTINCT CONCAT(s.name, ' - ', ts.level) SEPARATOR ', ') AS subjects
    FROM tutor_profiles tp
    JOIN users u ON u.id = tp.user_id
    LEFT JOIN tutor_subjects ts ON ts.tutor_profile_id = tp.id
    LEFT JOIN subjects s ON s.id = ts.subject_id
    WHERE tp.id = :id
      AND tp.status = 'approved'
    GROUP BY tp.id
");
$stmt->execute([':id' => $tutorId]);
$tutor = $stmt->fetch();

if (!$tutor) {
    die('Tutorul nu exista sau nu este aprobat.');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM availability
    WHERE tutor_profile_id = :id
      AND is_booked = 0
      AND available_date >= CURDATE()
    ORDER BY available_date, start_time
");
$stmt->execute([':id' => $tutorId]);
$availability = $stmt->fetchAll();

$pageTitle = 'TutorBloom - Profil tutor';
$pageHeading = 'Profil tutor';
$pageSubtitle = 'Gaseste rapid tutorul potrivit pentru tine';
$bodyId = 'profile-page';

require_once __DIR__ . '/../includes/header.php';
?>

    <section class="content-section">
        <h3><?= e($tutor['full_name']) ?></h3>

        <div class="profile-collapsible open">
            <button type="button" class="profile-toggle">Informatii tutor</button>

            <div class="profile-body">
                <?php
                $photoPath = $tutor['profile_photo'] ?: 'assets/images/tutor1.jpg';
                ?>

                <p>
                    <img
                            src="<?= e(app_url($photoPath)) ?>"
                            width="160"
                            alt="Tutor"
                    >
                </p>

                <p><?= e($tutor['description']) ?></p>

                <p>
                    <span class="profile-tag">Email: <?= e($tutor['email']) ?></span>
                    <span class="profile-tag">Experienta: <?= e($tutor['experience_years']) ?> ani</span>
                    <span class="profile-tag">Pret: <?= e($tutor['price_per_hour']) ?> lei / ora</span>
                </p>

                <p>
                    <strong>Materii:</strong> <?= e($tutor['subjects']) ?><br>
                    <strong>Tip lectie:</strong> <?= e($tutor['lesson_type']) ?><br>
                    <strong>Locatie:</strong> <?= e($tutor['location_type']) ?>
                </p>
            </div>
        </div>
    </section>

    <section class="content-section">
        <h3>Disponibilitati</h3>

        <?php if ($availability): ?>
            <div class="table-wrapper results-wrapper">
                <table class="results-table">
                    <thead>
                    <tr>
                        <th>Data</th>
                        <th>Ora inceput</th>
                        <th>Ora final</th>
                        <th>Rezervare</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($availability as $slot): ?>
                        <tr>
                            <td data-label="Data"><?= e($slot['available_date']) ?></td>
                            <td data-label="Ora inceput"><?= e($slot['start_time']) ?></td>
                            <td data-label="Ora final"><?= e($slot['end_time']) ?></td>
                            <td data-label="Rezervare">
                                <a href="<?= e(app_url('student/book.php?tutor_id=' . (int)$tutor['id'] . '&availability_id=' . (int)$slot['id'])) ?>">
                                    Rezerva
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Acest tutor nu are disponibilitati libere momentan.</p>
        <?php endif; ?>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>