<?php
require_once __DIR__ . '/../includes/init.php';
require_role('student');
require_once __DIR__ . '/../includes/ajax_lab_helpers.php';

$subjects = lab_get_subjects($pdo);
$filters = lab_read_filters_from_get();

$pageTitle = 'TutorBloom - AJAX jQuery';
$pageHeading = 'Cautare tutori - AJAX jQuery';
$pageSubtitle = 'Cerinta 3: paginare cu jQuery si JSON';
$bodyId = 'ajax-jquery-page';
$extraScripts = ['assets/js/tutors-pagination-jquery.js'];

require_once __DIR__ . '/../includes/header.php';
?>

    <section class="content-section">
        <h3>Cerinta 3 - Paginare cu jQuery</h3>

        <p>
            Aceasta pagina foloseste jQuery pentru apelul AJAX si pentru popularea tabelului.
        </p>

        <form method="get" class="styled-form" id="search-form">
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
                                <a href="<?= e(app_url('student/search_tutors_jquery.php')) ?>">Reseteaza</a>
                            </td>
                        </tr>
                    </table>
                </div>
            </fieldset>
        </form>
    </section>

    <section class="content-section">
        <h3>Rezultate</h3>

        <p id="ajax-message" class="error-box" hidden></p>
        <p id="ajax-info"></p>

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
                <tbody id="tutors-body"></tbody>
            </table>
        </div>

        <p>
            <button type="button" id="previous-button">Previous 3</button>
            <button type="button" id="next-button">Next 3</button>
        </p>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>