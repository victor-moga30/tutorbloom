<?php
if (!isset($pageTitle)) {
    $pageTitle = 'TutorBloom';
}

if (!isset($pageHeading)) {
    $pageHeading = $pageTitle;
}

if (!isset($pageSubtitle)) {
    $pageSubtitle = 'Găsește rapid profesorul potrivit pentru tine';
}

if (!isset($bodyId)) {
    $bodyId = 'home-page';
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title><?= e($pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="<?= e(app_url('assets/css/style-responsive.css')) ?>">
</head>
<body id="<?= e($bodyId) ?>">

<header class="site-header">
    <h1>TutorBloom</h1>
    <h2><?= e($pageHeading) ?></h2>
    <p class="subtitle"><?= e($pageSubtitle) ?></p>
</header>

<?php if (is_logged_in()): ?>
    <nav class="main-nav">
        <ul class="menu">

            <?php if (current_user_role() === 'student'): ?>
                <li class="menu-home">
                    <a href="<?= e(app_url('student/dashboard.php')) ?>">Acasa</a>
                </li>

                <li class="menu-search has-submenu" tabindex="0">
                    <a href="<?= e(app_url('student/search_tutors.php')) ?>">Cautare tutori</a>
                    <ul class="submenu">
                        <li>
                            <a href="<?= e(app_url('student/search_tutors.php')) ?>">Cautare normala - backend pagination</a>
                        </li>
                        <li>
                            <a href="<?= e(app_url('student/search_tutors_ajax_json.php')) ?>">Laborator AJAX JSON</a>
                        </li>
                        <li>
                            <a href="<?= e(app_url('student/search_tutors_ajax_xml.php')) ?>">Laborator AJAX XML</a>
                        </li>
                        <li>
                            <a href="<?= e(app_url('student/search_tutors_jquery.php')) ?>">Laborator AJAX jQuery</a>
                        </li>
                    </ul>
                </li>

                <li class="menu-dashboard">
                    <a href="<?= e(app_url('student/perfect_match.php')) ?>">Quiz potrivire</a>
                </li>

                <li class="menu-profile has-submenu" tabindex="0">
                    <a href="<?= e(app_url('student/profile.php')) ?>">Profil</a>
                    <ul class="submenu">
                        <li>
                            <a href="<?= e(app_url('student/profile.php')) ?>">Profilul meu</a>
                        </li>
                        <li>
                            <a href="<?= e(app_url('student/dashboard.php')) ?>">Dashboard student</a>
                        </li>
                        <li>
                            <a href="<?= e(app_url('student/search_tutors.php')) ?>">Gaseste tutor</a>
                        </li>
                        <li>
                            <a href="<?= e(app_url('student/perfect_match.php')) ?>">Quiz potrivire</a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (current_user_role() === 'tutor'): ?>
                <li class="menu-home">
                    <a href="<?= e(app_url('tutor/dashboard.php')) ?>">Acasa</a>
                </li>

                <li class="menu-profile has-submenu" tabindex="0">
                    <a href="<?= e(app_url('tutor/profile.php')) ?>">Profil</a>
                    <ul class="submenu">
                        <li>
                            <a href="<?= e(app_url('tutor/profile.php')) ?>">Editare profil</a>
                        </li>
                        <li>
                            <a href="<?= e(app_url('tutor/availability.php')) ?>">Disponibilitati</a>
                        </li>
                    </ul>
                </li>

                <li class="menu-book">
                    <a href="<?= e(app_url('tutor/bookings.php')) ?>">Rezervari</a>
                </li>

                <li class="menu-dashboard has-submenu" tabindex="0">
                    <a href="<?= e(app_url('tutor/availability.php')) ?>">Panou tutor</a>
                    <ul class="submenu">
                        <li>
                            <a href="<?= e(app_url('tutor/availability.php')) ?>">Disponibilitate</a>
                        </li>
                        <li>
                            <a href="<?= e(app_url('tutor/bookings.php')) ?>">Rezervari primite</a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (current_user_role() === 'admin'): ?>
                <li class="menu-home">
                    <a href="<?= e(app_url('admin/dashboard.php')) ?>">Acasa</a>
                </li>

                <li class="menu-dashboard has-submenu" tabindex="0">
                    <a href="<?= e(app_url('admin/review_tutors.php')) ?>">Review tutori</a>
                    <ul class="submenu">
                        <li>
                            <a href="<?= e(app_url('admin/review_tutors.php')) ?>">Lista / aprobare tutori</a>
                        </li>
                        <li>
                            <a href="<?= e(app_url('admin/edit_tutors_ajax.php')) ?>">Editare vanilla AJAX</a>
                        </li>
                        <li>
                            <a href="<?= e(app_url('admin/edit_tutors_jquery.php')) ?>">Editare jQuery</a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <li class="menu-profile has-submenu" tabindex="0">
                <a href="#">Cont</a>
                <ul class="submenu">
                    <li>
                        <a href="#">Utilizator: <?= e($_SESSION['user']['full_name']) ?></a>
                    </li>
                    <li>
                        <a href="#">Rol: <?= e($_SESSION['user']['role']) ?></a>
                    </li>
                    <li>
                        <a href="<?= e(app_url('logout.php')) ?>">Logout</a>
                    </li>
                </ul>
            </li>

        </ul>
    </nav>
<?php endif; ?>

<main class="page-content">