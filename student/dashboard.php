<?php
require_once __DIR__ . '/../includes/init.php';
require_role('student');

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM tutor_profiles
    WHERE status = 'approved'
");
$approvedTutors = (int)$stmt->fetch()['total'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE student_id = :student_id
");
$stmt->execute([':student_id' => current_user_id()]);
$myBookings = (int)$stmt->fetch()['total'];

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM subjects
");
$totalSubjects = (int)$stmt->fetch()['total'];

$stmt = $pdo->query("
    SELECT tp.*, u.full_name,
           GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS subjects
    FROM tutor_profiles tp
    JOIN users u ON u.id = tp.user_id
    LEFT JOIN tutor_subjects ts ON ts.tutor_profile_id = tp.id
    LEFT JOIN subjects s ON s.id = ts.subject_id
    WHERE tp.status = 'approved'
    GROUP BY tp.id
    ORDER BY tp.experience_years DESC, tp.price_per_hour ASC
    LIMIT 3
");
$recommendedTutors = $stmt->fetchAll();

$pageTitle = 'TutorBloom - Acasa';
$pageHeading = 'Bine ai venit!';
$pageSubtitle = 'Gaseste rapid profesorul potrivit pentru tine';
$bodyId = 'home-page';

require_once __DIR__ . '/../includes/header.php';
?>

    <section class="content-section">
        <div id="home-carousel" class="carousel-container">

            <div class="carousel-slide active">
                <img class="carousel-photo" src="<?= e(app_url('assets/images/slide1.png')) ?>" alt="Tutoring">
                <div class="carousel-overlay"></div>
                <div class="carousel-content">
                    <h3>Gaseste tutorul potrivit</h3>
                    <p>Compara rapid profesorii disponibili si alege varianta ideala pentru nivelul si obiectivele tale.</p>
                    <a href="<?= e(app_url('student/search_tutors.php')) ?>" class="carousel-link">Vezi tutorii</a>
                </div>
            </div>

            <div class="carousel-slide">
                <img class="carousel-photo" src="<?= e(app_url('assets/images/slide2.png')) ?>" alt="Test nivel">
                <div class="carousel-overlay"></div>
                <div class="carousel-content">
                    <h3>Testeaza-ti nivelul</h3>
                    <p>Completeaza testul de potrivire si primeste recomandari potrivite pentru tine.</p>
                    <a href="<?= e(app_url('student/perfect_match.php')) ?>" class="carousel-link">Incepe testul</a>
                </div>
            </div>

            <div class="carousel-slide">
                <img class="carousel-photo" src="<?= e(app_url('assets/images/slide3.png')) ?>" alt="Rezervare">
                <div class="carousel-overlay"></div>
                <div class="carousel-content">
                    <h3>Rezerva rapid</h3>
                    <p>Alege un interval disponibil si trimite o cerere de rezervare catre tutor.</p>
                    <a href="<?= e(app_url('student/search_tutors.php')) ?>" class="carousel-link">Rezerva acum</a>
                </div>
            </div>

            <div class="carousel-slide">
                <img class="carousel-photo" src="<?= e(app_url('assets/images/slide4.png')) ?>" alt="Profil tutor">
                <div class="carousel-overlay"></div>
                <div class="carousel-content">
                    <h3>Vezi profiluri publice</h3>
                    <p>Consulta experienta, materiile, pretul si disponibilitatea inainte sa alegi.</p>
                    <a href="<?= e(app_url('student/search_tutors.php')) ?>" class="carousel-link">Vezi profiluri</a>
                </div>
            </div>

            <div class="carousel-controls">
                <button type="button" class="carousel-btn" id="carousel-prev">&lt;</button>
                <button type="button" class="carousel-btn" id="carousel-next">&gt;</button>
            </div>

            <div class="carousel-indicators">
                <button type="button" class="carousel-dot active" data-slide="0"></button>
                <button type="button" class="carousel-dot" data-slide="1"></button>
                <button type="button" class="carousel-dot" data-slide="2"></button>
                <button type="button" class="carousel-dot" data-slide="3"></button>
            </div>

        </div>
    </section>

    <section class="intro-section">
        <h3>Invata fara griji</h3>
        <p>
            TutorBloom este o platforma in care poti cauta tutori, consulta profiluri publice,
            verifica disponibilitatea acestora si trimite o cerere de rezervare intr-un mod simplu si clar.
        </p>
        <p>
            Alege materia dorita, compara preturile si descopera varianta care se potriveste cel mai bine
            nevoilor tale, ritmului tau de invatare si programului pe care il ai.
        </p>
    </section>

    <section class="content-section">
        <h3>Date rapide</h3>

        <div class="stats-grid">
            <article class="stat-card">
                <h4 class="stat-number"><?= e($approvedTutors) ?></h4>
                <p>Tutori aprobati</p>
            </article>

            <article class="stat-card">
                <h4 class="stat-number"><?= e($myBookings) ?></h4>
                <p>Rezervarile mele</p>
            </article>

            <article class="stat-card">
                <h4 class="stat-number"><?= e($totalSubjects) ?></h4>
                <p>Materii disponibile</p>
            </article>
        </div>
    </section>

    <section class="content-section">
        <h3>Gaseste-ti tutorele perfect</h3>
        <p>
            Completeaza testul de potrivire pentru a primi recomandari in functie de nivel, buget,
            formatul lectiilor si materia dorita.
        </p>
        <p>
            <a href="<?= e(app_url('student/perfect_match.php')) ?>">Incepe testul de potrivire</a>
        </p>
    </section>

    <section class="content-section">
        <h3>Tutori recomandati</h3>

        <?php if ($recommendedTutors): ?>
            <div class="table-wrapper results-wrapper">
                <table class="results-table">
                    <thead>
                    <tr>
                        <th>Poza</th>
                        <th>Nume</th>
                        <th>Materii</th>
                        <th>Experienta</th>
                        <th>Pret</th>
                        <th>Profil</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recommendedTutors as $tutor): ?>
                        <?php
                        $photoPath = $tutor['profile_photo'] ?: 'assets/images/tutor1.jpg';
                        ?>
                        <tr>
                            <td data-label="Poza">
                                <img src="<?= e(app_url($photoPath)) ?>" alt="Tutor" width="70">
                            </td>
                            <td data-label="Nume"><?= e($tutor['full_name']) ?></td>
                            <td data-label="Materii"><?= e($tutor['subjects']) ?></td>
                            <td data-label="Experienta"><?= e($tutor['experience_years']) ?> ani</td>
                            <td data-label="Pret"><?= e($tutor['price_per_hour']) ?> lei / ora</td>
                            <td data-label="Profil">
                                <a href="<?= e(app_url('student/tutor_profile.php?id=' . (int)$tutor['id'])) ?>">Vezi profil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Momentan nu exista tutori aprobati.</p>
        <?php endif; ?>
    </section>

    <section class="content-section">
        <h3>Intrebari frecvente</h3>

        <div id="home-faq">
            <div class="collapsible-item">
                <button type="button" class="collapsible-trigger">Cum aleg tutorul potrivit?</button>
                <ul class="collapsible-body">
                    <li>Verifica materia, nivelul si stilul de predare.</li>
                    <li>Compara pretul si disponibilitatea.</li>
                    <li>Citeste descrierea inainte de rezervare.</li>
                </ul>
            </div>

            <div class="collapsible-item">
                <button type="button" class="collapsible-trigger">Cum functioneaza rezervarea?</button>
                <ul class="collapsible-body">
                    <li>Alegi tutorul.</li>
                    <li>Selectezi un interval disponibil.</li>
                    <li>Trimiti cererea catre tutor.</li>
                </ul>
            </div>

            <div class="collapsible-item">
                <button type="button" class="collapsible-trigger">Ce poate face un tutor?</button>
                <ul class="collapsible-body">
                    <li>Isi editeaza profilul.</li>
                    <li>Adauga disponibilitati.</li>
                    <li>Accepta sau respinge rezervari.</li>
                </ul>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>