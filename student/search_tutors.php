<?php
require_once __DIR__ . '/../includes/init.php';
require_role('student');
require_once __DIR__ . '/../includes/ajax_lab_helpers.php';

$subjects = lab_get_subjects($pdo);
$filters = lab_read_filters_from_get();

$selectedSubject = $filters['subject'];
$selectedLevel = $filters['level'];
$maxPrice = $filters['max_price'];
$lessonType = $filters['lesson_type'];
$locationType = $filters['location_type'];
$keyword = $filters['keyword'];
$fromQuiz = isset($_GET['from_quiz']);

$limit = 3;
$page = get_int_from_get('page', 1);

if ($page < 1) {
    $page = 1;
}

$allTutors = lab_get_filtered_tutors($pdo, $filters);
$total = count($allTutors);
$totalPages = max(1, (int)ceil($total / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $limit;
$tutors = array_slice($allTutors, $offset, $limit);

function search_tutors_page_url(int $page): string
{
    $params = $_GET;
    $params['page'] = $page;

    return app_url('student/search_tutors.php?' . http_build_query($params));
}

$pageTitle = 'TutorBloom - Cautare tutori';
$pageHeading = 'Cautare tutori';
$pageSubtitle = 'Gaseste rapid tutorul potrivit pentru tine';
$bodyId = 'search-page';
$loadGlobalScripts = false;

require_once __DIR__ . '/../includes/header.php';
?>

    <div class="search-layout">
        <section class="main-column">

            <section class="intro-section">
                <p>
                    <strong>Invata mai usor:</strong>
                    compara tutorii disponibili, verifica informatiile importante si alege varianta potrivita.
                </p>
                <p>
                    Aceasta este varianta principala cu paginare facuta exclusiv pe backend.
                    Nu foloseste JavaScript pentru paginare.
                </p>
            </section>

            <?php if ($fromQuiz && isset($_SESSION['perfect_match'])): ?>
                <section class="quiz-summary">
                    <h3>Rezultat test</h3>
                    <p>
                        <strong>Scor:</strong> <?= e($_SESSION['perfect_match']['score']) ?> / <?= e($_SESSION['perfect_match']['total']) ?><br>
                        <strong>Nivel estimat:</strong> <?= e($_SESSION['perfect_match']['cefr_level']) ?><br>
                        <strong>Materie:</strong> <?= e($_SESSION['perfect_match']['subject']) ?><br>
                        <strong>Buget:</strong> <?= e($_SESSION['perfect_match']['budget']) ?> lei
                    </p>
                </section>
            <?php endif; ?>

            <section class="content-section">
                <h3>Filtre de cautare</h3>

                <form method="get" name="formCautare" class="styled-form" id="search-form" action="<?= e(app_url('student/search_tutors.php')) ?>">
                    <fieldset>
                        <legend><b>Criterii de filtrare</b></legend>

                        <div class="table-wrapper filters-wrapper">
                            <table class="filters-table">
                                <tr>
                                    <th colspan="4">Criterii de filtrare</th>
                                </tr>

                                <tr>
                                    <td><b>Materie</b></td>
                                    <td>
                                        <select name="subject">
                                            <option value="">Toate</option>
                                            <?php foreach ($subjects as $subject): ?>
                                                <option value="<?= e($subject['name']) ?>" <?= $selectedSubject === $subject['name'] ? 'selected' : '' ?>>
                                                    <?= e(ucfirst($subject['name'])) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>

                                    <td><b>Nivel</b></td>
                                    <td>
                                        <select name="level">
                                            <option value="">Toate</option>
                                            <option value="incepator" <?= $selectedLevel === 'incepator' ? 'selected' : '' ?>>Incepator</option>
                                            <option value="mediu" <?= $selectedLevel === 'mediu' ? 'selected' : '' ?>>Mediu</option>
                                            <option value="avansat" <?= $selectedLevel === 'avansat' ? 'selected' : '' ?>>Avansat</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Pret maxim</b></td>
                                    <td>
                                        <input type="number" name="max_price" value="<?= e($maxPrice) ?>" min="0" max="300" step="5">
                                    </td>

                                    <td><b>Cuvant-cheie</b></td>
                                    <td>
                                        <input type="text" name="keyword" maxlength="30" value="<?= e($keyword) ?>">
                                    </td>
                                </tr>

                                <tr>
                                    <td><b>Tip lectie</b></td>
                                    <td>
                                        <label>
                                            <input type="radio" name="lesson_type" value="" <?= $lessonType === '' ? 'checked' : '' ?>>
                                            Oricare
                                        </label>
                                        <br>
                                        <label>
                                            <input type="radio" name="lesson_type" value="individual" <?= $lessonType === 'individual' ? 'checked' : '' ?>>
                                            Individuala
                                        </label>
                                        <br>
                                        <label>
                                            <input type="radio" name="lesson_type" value="grup" <?= $lessonType === 'grup' ? 'checked' : '' ?>>
                                            Grup
                                        </label>
                                    </td>

                                    <td><b>Predare</b></td>
                                    <td>
                                        <select name="location_type">
                                            <option value="">Oricare</option>
                                            <option value="online" <?= $locationType === 'online' ? 'selected' : '' ?>>Online</option>
                                            <option value="fizic" <?= $locationType === 'fizic' ? 'selected' : '' ?>>Fizic</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="4">
                                        <input type="submit" value="Valideaza filtrele">
                                        <br><br>
                                        <a href="<?= e(app_url('student/search_tutors.php')) ?>">Reseteaza</a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </fieldset>
                </form>
            </section>

            <section class="content-section">
                <h3>Rezultate cautare</h3>

                <p>
                    <?php if ($total === 0): ?>
                        0 rezultate.
                    <?php else: ?>
                        Se afiseaza <?= e($offset + 1) ?>-<?= e(min($offset + $limit, $total)) ?> din <?= e($total) ?> rezultate.
                        Pagina <?= e($page) ?> din <?= e($totalPages) ?>.
                    <?php endif; ?>
                </p>

                <div class="table-wrapper results-wrapper">
                    <table class="results-table">
                        <thead>
                        <tr>
                            <th>Poza</th>
                            <th>Nume</th>
                            <th>Materii</th>
                            <th>Experienta</th>
                            <th>Pret</th>
                            <th>Match</th>
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
                                    <img src="<?= e(app_url($photoPath)) ?>" width="70" alt="Tutor">
                                </td>
                                <td data-label="Nume"><?= e($tutor['full_name']) ?></td>
                                <td data-label="Materii"><?= e($tutor['subjects']) ?></td>
                                <td data-label="Experienta"><?= e($tutor['experience_years']) ?> ani</td>
                                <td data-label="Pret"><?= e($tutor['price_per_hour']) ?> lei</td>
                                <td data-label="Match"><?= e($tutor['match_score']) ?>%</td>
                                <td data-label="Actiuni">
                                    <a href="<?= e(app_url('student/tutor_profile.php?id=' . (int)$tutor['id'])) ?>">Profil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$tutors): ?>
                            <tr>
                                <td colspan="7">Nu exista tutori pentru filtrele selectate.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <p>
                    <?php if ($page <= 1): ?>
                        <button type="button" disabled>Previous 3</button>
                    <?php else: ?>
                        <a class="button-link" href="<?= e(search_tutors_page_url($page - 1)) ?>">Previous 3</a>
                    <?php endif; ?>

                    <?php if ($page >= $totalPages): ?>
                        <button type="button" disabled>Next 3</button>
                    <?php else: ?>
                        <a class="button-link" href="<?= e(search_tutors_page_url($page + 1)) ?>">Next 3</a>
                    <?php endif; ?>
                </p>
            </section>

            <section id="smart-match-results" class="smart-match-results">
                <h2>Rezultatele tale personalizate</h2>
                <p class="smart-match-subtitle">
                    Recomandarile sunt ordonate dupa potrivirea cu preferintele tale.
                    In aceasta varianta sunt afisate doar rezultatele de pe pagina curenta.
                </p>

                <div id="smart-match-list">
                    <?php foreach ($tutors as $index => $tutor): ?>
                        <article class="smart-match-card <?= $index === 0 && $page === 1 ? 'best-match' : '' ?>">
                            <?php if ($index === 0 && $page === 1): ?>
                                <span class="match-badge">Cea mai buna potrivire</span>
                            <?php endif; ?>

                            <h3><?= e($tutor['full_name']) ?></h3>

                            <p>
                                <strong>Materii:</strong> <?= e($tutor['subjects']) ?><br>
                                <strong>Experienta:</strong> <?= e($tutor['experience_years']) ?> ani<br>
                                <strong>Pret:</strong> <?= e($tutor['price_per_hour']) ?> lei / ora
                            </p>

                            <p><?= e($tutor['description']) ?></p>

                            <div class="match-score">
                                <strong>Scor potrivire: <?= e($tutor['match_score']) ?>%</strong>
                                <div class="match-score-bar">
                                    <div class="match-score-fill" style="width: <?= (int)$tutor['match_score'] ?>%;"></div>
                                </div>
                            </div>

                            <ul class="match-reasons">
                                <?php foreach ($tutor['match_reasons'] as $reason): ?>
                                    <li><?= e($reason) ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <p>
                                <a href="<?= e(app_url('student/tutor_profile.php?id=' . (int)$tutor['id'])) ?>">
                                    Vezi profilul tutorului
                                </a>
                            </p>
                        </article>
                    <?php endforeach; ?>

                    <?php if (!$tutors): ?>
                        <p>Nu exista rezultate pentru aceasta cautare.</p>
                    <?php endif; ?>
                </div>
            </section>

        </section>

        <div class="right-column">
            <aside class="sidebar-menu">
                <h3>Actiuni rapide</h3>
                <ul class="side-submenu">
                    <li>
                        <a href="<?= e(app_url('student/perfect_match.php')) ?>">Gaseste-ti tutorele perfect</a>
                    </li>
                    <li>
                        <a href="<?= e(app_url('student/search_tutors.php')) ?>">Cautare backend pagination</a>
                    </li>
                    <li>
                        <a href="<?= e(app_url('student/search_tutors_ajax_json.php')) ?>">Varianta AJAX JSON</a>
                    </li>
                    <li>
                        <a href="<?= e(app_url('student/search_tutors_ajax_xml.php')) ?>">Varianta AJAX XML</a>
                    </li>
                    <li>
                        <a href="<?= e(app_url('student/search_tutors_jquery.php')) ?>">Varianta jQuery</a>
                    </li>
                    <li>
                        <a href="<?= e(app_url('student/dashboard.php')) ?>">Dashboard student</a>
                    </li>
                </ul>
            </aside>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>