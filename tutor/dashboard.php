<?php
require_once __DIR__ . '/../includes/init.php';
require_role('tutor');

$stmt = $pdo->prepare("
    SELECT *
    FROM tutor_profiles
    WHERE user_id = :user_id
");
$stmt->execute([':user_id' => current_user_id()]);
$profile = $stmt->fetch();

if (!$profile) {
    $stmt = $pdo->prepare("
        INSERT INTO tutor_profiles (user_id, description, status)
        VALUES (:user_id, '', 'pending')
    ");
    $stmt->execute([':user_id' => current_user_id()]);

    $stmt = $pdo->prepare("
        SELECT *
        FROM tutor_profiles
        WHERE user_id = :user_id
    ");
    $stmt->execute([':user_id' => current_user_id()]);
    $profile = $stmt->fetch();
}

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE tutor_profile_id = :profile_id
");
$stmt->execute([':profile_id' => $profile['id']]);
$totalBookings = (int)$stmt->fetch()['total'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE tutor_profile_id = :profile_id
      AND status = 'pending'
");
$stmt->execute([':profile_id' => $profile['id']]);
$pendingBookings = (int)$stmt->fetch()['total'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM availability
    WHERE tutor_profile_id = :profile_id
      AND is_booked = 0
");
$stmt->execute([':profile_id' => $profile['id']]);
$freeSlots = (int)$stmt->fetch()['total'];

$pageTitle = 'TutorBloom - Dashboard tutor';
$pageHeading = 'Dashboard tutor';
$pageSubtitle = 'Administreaza profilul, disponibilitatile si rezervarile primite';
$bodyId = 'dashboard-page';

require_once __DIR__ . '/../includes/header.php';
?>

    <section class="intro-section">
        <p>
            In aceasta pagina poti administra informatiile importante din contul de tutor,
            de la datele de profil si intervalele disponibile, pana la rezervarile primite din partea studentilor.
        </p>
    </section>

    <section class="content-section">
        <h3>Bine ai venit, <?= e($_SESSION['user']['full_name']) ?>!</h3>

        <div class="stats-grid">
            <article class="stat-card">
                <h4>Status profil</h4>
                <p><?= e($profile['status']) ?></p>
            </article>

            <article class="stat-card">
                <h4>Total rezervari</h4>
                <p><?= e($totalBookings) ?></p>
            </article>

            <article class="stat-card">
                <h4>Rezervari pending</h4>
                <p><?= e($pendingBookings) ?></p>
            </article>
        </div>
    </section>

    <section class="content-section">
        <h3>Disponibilitate</h3>

        <div class="stats-grid">
            <article class="stat-card">
                <h4>Sloturi libere</h4>
                <p><?= e($freeSlots) ?></p>
            </article>

            <article class="stat-card">
                <h4>Profil</h4>
                <p>
                    <a href="<?= e(app_url('tutor/profile.php')) ?>">Editeaza profil</a>
                </p>
            </article>

            <article class="stat-card">
                <h4>Program</h4>
                <p>
                    <a href="<?= e(app_url('tutor/availability.php')) ?>">Editeaza disponibilitati</a>
                </p>
            </article>
        </div>
    </section>

    <section class="content-section">
        <h3>Actiuni rapide</h3>
        <ul>
            <li>
                <a href="<?= e(app_url('tutor/profile.php')) ?>">Editeaza profil</a>
            </li>
            <li>
                <a href="<?= e(app_url('tutor/availability.php')) ?>">Editeaza disponibilitati</a>
            </li>
            <li>
                <a href="<?= e(app_url('tutor/bookings.php')) ?>">Vezi rezervari</a>
            </li>
        </ul>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>