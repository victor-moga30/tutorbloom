<?php
require_once __DIR__ . '/../includes/init.php';
require_role('admin');

$totalUsers = (int)$pdo->query("SELECT COUNT(*) AS total FROM users")->fetch()['total'];

$totalTutors = (int)$pdo->query("SELECT COUNT(*) AS total FROM tutor_profiles")->fetch()['total'];

$pendingTutors = (int)$pdo->query("
    SELECT COUNT(*) AS total 
    FROM tutor_profiles 
    WHERE status = 'pending'
")->fetch()['total'];

$approvedTutors = (int)$pdo->query("
    SELECT COUNT(*) AS total 
    FROM tutor_profiles 
    WHERE status = 'approved'
")->fetch()['total'];

$pageTitle = 'TutorBloom - Dashboard admin';
$pageHeading = 'Dashboard admin';
$pageSubtitle = 'Administreaza tutorii, utilizatorii si aprobarile';
$bodyId = 'dashboard-page';

require_once __DIR__ . '/../includes/header.php';
?>

    <section class="intro-section">
        <p>
            In acest panou poti verifica rapid situatia tutorilor si poti aproba sau respinge
            profilurile care asteapta verificare.
        </p>
    </section>

    <section class="content-section">
        <h3>Date rapide</h3>

        <div class="stats-grid">
            <article class="stat-card">
                <h4 class="stat-number"><?= e($totalUsers) ?></h4>
                <p>Utilizatori</p>
            </article>

            <article class="stat-card">
                <h4 class="stat-number"><?= e($totalTutors) ?></h4>
                <p>Tutori total</p>
            </article>

            <article class="stat-card">
                <h4 class="stat-number"><?= e($pendingTutors) ?></h4>
                <p>Tutori pending</p>
            </article>
        </div>
    </section>

    <section class="content-section">
        <h3>Aprobari</h3>

        <div class="stats-grid">
            <article class="stat-card">
                <h4 class="stat-number"><?= e($approvedTutors) ?></h4>
                <p>Tutori aprobati</p>
            </article>

            <article class="stat-card">
                <h4>Review</h4>
                <p>
                    <a href="<?= e(app_url('admin/review_tutors.php')) ?>">Mergi la review tutori</a>
                </p>
            </article>

            <article class="stat-card">
                <h4>Rol admin</h4>
                <p>Acces complet la aprobarea tutorilor</p>
            </article>
        </div>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>