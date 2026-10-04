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
    die('Profil tutor inexistent.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if (in_array($action, ['accepted', 'rejected'], true)) {
        $stmt = $pdo->prepare("
            UPDATE bookings
            SET status = :status
            WHERE id = :id
              AND tutor_profile_id = :profile_id
        ");
        $stmt->execute([
                ':status' => $action,
                ':id' => $bookingId,
                ':profile_id' => $profile['id']
        ]);

        if ($action === 'rejected') {
            $stmt = $pdo->prepare("
                SELECT availability_id
                FROM bookings
                WHERE id = :id
                  AND tutor_profile_id = :profile_id
            ");
            $stmt->execute([
                    ':id' => $bookingId,
                    ':profile_id' => $profile['id']
            ]);
            $booking = $stmt->fetch();

            if ($booking) {
                $stmt = $pdo->prepare("
                    UPDATE availability
                    SET is_booked = 0
                    WHERE id = :availability_id
                ");
                $stmt->execute([':availability_id' => $booking['availability_id']]);
            }
        }

        log_action(current_user_id(), 'booking_' . $action, 'Tutor changed booking status.');
    }
}

$stmt = $pdo->prepare("
    SELECT b.*, 
           u.full_name AS student_name,
           s.name AS subject_name,
           a.available_date,
           a.start_time,
           a.end_time
    FROM bookings b
    JOIN users u ON u.id = b.student_id
    JOIN subjects s ON s.id = b.subject_id
    JOIN availability a ON a.id = b.availability_id
    WHERE b.tutor_profile_id = :profile_id
    ORDER BY b.created_at DESC
");
$stmt->execute([':profile_id' => $profile['id']]);
$bookings = $stmt->fetchAll();

$pageTitle = 'Rezervări primite';
$bodyId = 'dashboard-page';
require_once __DIR__ . '/../includes/header.php';
?>

    <section class="content-section">
        <h3>Rezervări primite</h3>

        <div class="table-wrapper">
            <table class="results-table">
                <thead>
                <tr>
                    <th>Student</th>
                    <th>Materie</th>
                    <th>Data</th>
                    <th>Interval</th>
                    <th>Mesaj</th>
                    <th>Status</th>
                    <th>Acțiuni</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= e($booking['student_name']) ?></td>
                        <td><?= e($booking['subject_name']) ?></td>
                        <td><?= e($booking['available_date']) ?></td>
                        <td><?= e($booking['start_time']) ?> - <?= e($booking['end_time']) ?></td>
                        <td><?= e($booking['message']) ?></td>
                        <td><?= e($booking['status']) ?></td>
                        <td>
                            <?php if ($booking['status'] === 'pending'): ?>
                                <form method="post" style="display:inline;">
                                    <?= csrf_field() ?>

                                    <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                                    <button type="submit" name="action" value="accepted">Acceptă</button>
                                    <button type="submit" name="action" value="rejected">Respinge</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$bookings): ?>
                    <tr>
                        <td colspan="7">Nu există rezervări.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>