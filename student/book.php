<?php
require_once __DIR__ . '/../includes/init.php';
require_role('student');

$tutorId = (int)($_GET['tutor_id'] ?? $_POST['tutor_id'] ?? 0);
$availabilityId = (int)($_GET['availability_id'] ?? $_POST['availability_id'] ?? 0);
$error = '';
$success = '';

$stmt = $pdo->prepare("
    SELECT tp.*, u.full_name, a.available_date, a.start_time, a.end_time
    FROM tutor_profiles tp
    JOIN users u ON u.id = tp.user_id
    JOIN availability a ON a.tutor_profile_id = tp.id
    WHERE tp.id = :tutor_id
      AND a.id = :availability_id
      AND tp.status = 'approved'
      AND a.is_booked = 0
");
$stmt->execute([
        ':tutor_id' => $tutorId,
        ':availability_id' => $availabilityId
]);
$slot = $stmt->fetch();

if (!$slot) {
    die('Slot invalid sau deja rezervat.');
}

$stmt = $pdo->prepare("
    SELECT s.*
    FROM subjects s
    JOIN tutor_subjects ts ON ts.subject_id = s.id
    WHERE ts.tutor_profile_id = :tutor_id
    GROUP BY s.id
");
$stmt->execute([':tutor_id' => $tutorId]);
$subjects = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    $message = clean_text($message, 500);

    if ($subjectId <= 0) {
        $error = 'Alege o materie.';
    } else {
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO bookings
                (student_id, tutor_profile_id, availability_id, subject_id, message, status)
                VALUES
                (:student_id, :tutor_profile_id, :availability_id, :subject_id, :message, 'pending')
            ");
            $stmt->execute([
                    ':student_id' => current_user_id(),
                    ':tutor_profile_id' => $tutorId,
                    ':availability_id' => $availabilityId,
                    ':subject_id' => $subjectId,
                    ':message' => $message
            ]);

            $stmt = $pdo->prepare("
                UPDATE availability
                SET is_booked = 1
                WHERE id = :id
            ");
            $stmt->execute([':id' => $availabilityId]);

            $pdo->commit();

            log_action(current_user_id(), 'booking_created', 'Student created booking.');

            $success = 'Rezervarea a fost trimisa catre tutor.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Eroare la rezervare.';
        }
    }
}

$pageTitle = 'Rezervare';
$bodyId = 'book-page';
require_once __DIR__ . '/../includes/header.php';
?>

    <section class="content-section">
        <h3>Rezerva sedinta cu <?= e($slot['full_name']) ?></h3>

        <p>
            <strong>Data:</strong> <?= e($slot['available_date']) ?><br>
            <strong>Interval:</strong> <?= e($slot['start_time']) ?> - <?= e($slot['end_time']) ?>
        </p>

        <?php if ($error): ?>
            <p style="color:red;"><?= e($error) ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p style="color:green;"><?= e($success) ?></p>
        <?php else: ?>
            <form method="post" class="styled-form">
                <?= csrf_field() ?>

                <input type="hidden" name="tutor_id" value="<?= (int)$tutorId ?>">
                <input type="hidden" name="availability_id" value="<?= (int)$availabilityId ?>">

                <p>
                    <label>Materie</label><br>
                    <select name="subject_id" required>
                        <option value="">Alege materia</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= (int)$subject['id'] ?>"><?= e($subject['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <label>Mesaj pentru tutor</label><br>
                    <textarea name="message" rows="5" cols="50"></textarea>
                </p>

                <button type="submit">Trimite rezervarea</button>
            </form>
        <?php endif; ?>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>