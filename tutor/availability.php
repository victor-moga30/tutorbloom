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
    exit('Profil tutor inexistent.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'add') {
        $date = $_POST['available_date'] ?? '';
        $start = $_POST['start_time'] ?? '';
        $end = $_POST['end_time'] ?? '';

        $validDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
        $validStart = preg_match('/^\d{2}:\d{2}$/', $start);
        $validEnd = preg_match('/^\d{2}:\d{2}$/', $end);

        if ($validDate && $validStart && $validEnd && $start < $end) {
            $stmt = $pdo->prepare("
                INSERT INTO availability
                (tutor_profile_id, available_date, start_time, end_time)
                VALUES
                (:profile_id, :date, :start, :end)
            ");
            $stmt->execute([
                    ':profile_id' => $profile['id'],
                    ':date' => $date,
                    ':start' => $start,
                    ':end' => $end
            ]);

            log_action(current_user_id(), 'availability_added', 'Tutor added availability.');
        }
    }

    if ($formAction === 'delete') {
        $deleteId = get_int_from_post('availability_id');

        if ($deleteId > 0) {
            $stmt = $pdo->prepare("
                DELETE FROM availability
                WHERE id = :id
                  AND tutor_profile_id = :profile_id
                  AND is_booked = 0
            ");
            $stmt->execute([
                    ':id' => $deleteId,
                    ':profile_id' => $profile['id']
            ]);

            log_action(current_user_id(), 'availability_deleted', 'Tutor deleted availability.');
        }
    }

    redirect_to('tutor/availability.php');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM availability
    WHERE tutor_profile_id = :profile_id
    ORDER BY available_date, start_time
");
$stmt->execute([':profile_id' => $profile['id']]);
$slots = $stmt->fetchAll();

$pageTitle = 'Disponibilitati';
$bodyId = 'dashboard-page';
require_once __DIR__ . '/../includes/header.php';
?>

    <section class="content-section">
        <h3>Adauga disponibilitate</h3>

        <form method="post" class="styled-form">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="add">

            <p>
                <label>Data</label><br>
                <input type="date" name="available_date" required>
            </p>

            <p>
                <label>Ora inceput</label><br>
                <input type="time" name="start_time" required>
            </p>

            <p>
                <label>Ora final</label><br>
                <input type="time" name="end_time" required>
            </p>

            <button type="submit">Adauga</button>
        </form>
    </section>

    <section class="content-section">
        <h3>Disponibilitatile mele</h3>

        <div class="table-wrapper">
            <table class="results-table">
                <thead>
                <tr>
                    <th>Data</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Actiune</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($slots as $slot): ?>
                    <tr>
                        <td><?= e($slot['available_date']) ?></td>
                        <td><?= e($slot['start_time']) ?></td>
                        <td><?= e($slot['end_time']) ?></td>
                        <td><?= $slot['is_booked'] ? 'Rezervat' : 'Liber' ?></td>
                        <td>
                            <?php if (!$slot['is_booked']): ?>
                                <form method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="form_action" value="delete">
                                    <input type="hidden" name="availability_id" value="<?= (int)$slot['id'] ?>">
                                    <button type="submit">Sterge</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$slots): ?>
                    <tr>
                        <td colspan="5">Nu exista disponibilitati.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>