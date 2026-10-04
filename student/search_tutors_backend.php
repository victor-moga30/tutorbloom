<?php
require_once __DIR__ . '/../includes/init.php';
require_role('student');
require_once __DIR__ . '/../includes/ajax_lab_helpers.php';

$subjects = lab_get_subjects($pdo);
$filters = lab_read_filters_from_get();

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

function backend_page_url(int $page): string
{
    $params = $_GET;
    $params['page'] = $page;

    return app_url('student/search_tutors_backend.php?' . http_build_query($params));
}

$pageTitle = 'TutorBloom - Backend pagination';
$pageHeading = 'Cautare tutori - Backend';
$pageSubtitle = 'Cerinta 4: paginare exclusiv pe server';
$bodyId = 'backend-pagination-page';
$loadGlobalScripts = false;

require_once __DIR__ . '/../includes/header.php';
?>

    <section class="content-section">
        <h3>Cerinta 4 - Paginare fara JavaScript</h3>

        <p>
            Aceasta pagina nu foloseste JavaScript, jQuery sau AJAX. La fiecare Next/Previous,
            pagina se reincarca si backend-ul aduce urmatoarele rezultate.
        </p>

        <form method="get" class="styled-form" action="<?= e(app_url('student/search_tutors_backend.php')) ?>">
            <fieldset>
                <legend><b>Filtre</b></legend>

                <div class="table-wrapper filters-wrapper">
                    <table class="filters-table">
                        <tr>
                            <td><b>Materie</b></td>
                            <td>
                                <select name="subject">
                                    <option value="">Toate</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?= e($subject['name']) ?>" <?= $filters['subject'] === $subject['name'] ? 'selected' : '' ?>>
                                            <?= e(ucfirst($subject['name'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>

                            <td><b>Nivel</b></td>
                            <td>
                                <select name="level">
                                    <option value="">Toate</option>
                                    <option value="incepator" <?= $filters['level'] === 'incepator' ? 'selected' : '' ?>>Incepator</option>
                                    <option value="mediu" <?= $filters['level'] === 'mediu' ? 'selected' : '' ?>>Mediu</option>
                                    <option value="avansat" <?= $filters['level'] === 'avansat' ? 'selected' : '' ?>>Avansat</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td><b>Pret maxim</b></td>
                            <td>
                                <input type="number" name="max_price" value="<?= e($filters['max_price']) ?>" min="0" max="300" step="5">
                            </td>

                            <td><b>Cuvant-cheie</b></td>
                            <td>
                                <input type="text" name="keyword" maxlength="30" value="<?= e($filters['keyword']) ?>">
                            </td>
                        </tr>

                        <tr>
                            <td><b>Tip lectie</b></td>
                            <td>
                                <select name="lesson_type">
                                    <option value="" <?= $filters['lesson_type'] === '' ? 'selected' : '' ?>>Oricare</option>
                                    <option value="individual" <?= $filters['lesson_type'] === 'individual' ? 'selected' : '' ?>>Individuala</option>
                                    <option value="grup" <?= $filters['lesson_type'] === 'grup' ? 'selected' : '' ?>>Grup</option>
                                </select>
                            </td>

                            <td><b>Predare</b></td>
                            <td>
                                <select name="location_type">
                                    <option value="" <?= $filters['location_type'] === '' ? 'selected' : '' ?>>Oricare</option>
                                    <option value="online" <?= $filters['location_type'] === 'online' ? 'selected' : '' ?>>Online</option>
                                    <option value="fizic" <?= $filters['location_type'] === 'fizic' ? 'selected' : '' ?>>Fizic</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="4">
                                <button type="submit">Aplica filtrele</button>
                                <a href="<?= e(app_url('student/search_tutors_backend.php')) ?>">Reseteaza</a>
                            </td>
                        </tr>
                    </table>
                </div>
            </fieldset>
        </form>
    </section>

    <section class="content-section">
        <h3>Rezultate</h3>

        <p>
            Pagina <?= e($page) ?> din <?= e($totalPages) ?>.
            Total rezultate: <?= e($total) ?>.
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
                    <tr>
                        <td data-label="Poza">
                            <img src="<?= e(app_url($tutor['profile_photo'])) ?>" width="70" alt="Tutor">
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
                <a class="button-link" href="<?= e(backend_page_url($page - 1)) ?>">Previous 3</a>
            <?php endif; ?>

            <?php if ($page >= $totalPages): ?>
                <button type="button" disabled>Next 3</button>
            <?php else: ?>
                <a class="button-link" href="<?= e(backend_page_url($page + 1)) ?>">Next 3</a>
            <?php endif; ?>
        </p>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>