<?php
require_once __DIR__ . '/../includes/init.php';
require_role('admin');
require_once __DIR__ . '/../includes/ajax_lab_helpers.php';

$tutors = lab_get_all_tutors_for_edit($pdo);
$selectedId = get_int_from_get('id');

if ($selectedId <= 0 && $tutors) {
    $selectedId = (int)$tutors[0]['id'];
}

$pageTitle = 'TutorBloom - Editare jQuery';
$pageHeading = 'Editare tutori - jQuery';
$pageSubtitle = 'Cerinta 6: editare cu jQuery';
$bodyId = 'edit-tutors-jquery-page';
$extraScripts = ['assets/js/edit-tutors-jquery.js'];

require_once __DIR__ . '/../includes/header.php';
?>

    <section class="content-section">
        <h3>Cerinta 6 - Editare cu jQuery</h3>

        <p>
            Aceeasi functionalitate ca la Cerinta 5, dar apelurile AJAX si popularea formularului
            sunt facute cu jQuery.
        </p>

        <p>
            <a href="<?= e(app_url('admin/review_tutors.php')) ?>">Inapoi la review tutori</a> |
            <a href="<?= e(app_url('admin/edit_tutors_ajax.php?id=' . (int)$selectedId)) ?>">Vezi varianta vanilla AJAX</a>
        </p>

        <p id="ajax-message" class="error-box" hidden></p>
        <p id="success-message" class="success-message" hidden></p>

        <?php if (!$tutors): ?>
            <p>Nu exista tutori de editat.</p>
        <?php else: ?>
            <form id="edit-tutor-form" class="styled-form">
                <?= csrf_field() ?>

                <p>
                    <label for="tutor-select"><b>Alege ID tutor</b></label><br>
                    <select id="tutor-select" name="id">
                        <?php foreach ($tutors as $tutor): ?>
                            <option value="<?= (int)$tutor['id'] ?>" <?= (int)$tutor['id'] === $selectedId ? 'selected' : '' ?>>
                                ID <?= (int)$tutor['id'] ?> - <?= e($tutor['full_name']) ?> - <?= e($tutor['status']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <label for="full-name">Nume complet</label><br>
                    <input type="text" id="full-name" name="full_name" maxlength="100">
                </p>

                <p>
                    <label for="email">Email</label><br>
                    <input type="email" id="email" name="email" maxlength="120">
                </p>

                <p>
                    <label for="description">Descriere</label><br>
                    <textarea id="description" name="description" rows="5" maxlength="1000"></textarea>
                </p>

                <p>
                    <label for="experience-years">Experienta ani</label><br>
                    <input type="number" id="experience-years" name="experience_years" min="0" max="60">
                </p>

                <p>
                    <label for="price-per-hour">Pret pe ora</label><br>
                    <input type="number" id="price-per-hour" name="price_per_hour" min="0" max="1000" step="0.01">
                </p>

                <p>
                    <label for="lesson-type">Tip lectie</label><br>
                    <select id="lesson-type" name="lesson_type">
                        <option value="individual">Individuala</option>
                        <option value="grup">Grup</option>
                        <option value="ambele">Ambele</option>
                    </select>
                </p>

                <p>
                    <label for="location-type">Predare</label><br>
                    <select id="location-type" name="location_type">
                        <option value="online">Online</option>
                        <option value="fizic">Fizic</option>
                        <option value="ambele">Ambele</option>
                    </select>
                </p>

                <p>
                    <label for="status">Status</label><br>
                    <select id="status" name="status">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </p>

                <p>
                    <button type="button" id="save-button" disabled>Save</button>
                </p>
            </form>
        <?php endif; ?>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>