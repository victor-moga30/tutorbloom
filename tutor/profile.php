<?php
require_once __DIR__ . '/../includes/init.php';
require_role('tutor');

$message = '';

$stmt = $pdo->prepare("
    SELECT 
        tp.*,
        u.full_name,
        u.email
    FROM tutor_profiles tp
    JOIN users u ON u.id = tp.user_id
    WHERE tp.user_id = :user_id
    LIMIT 1
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
        SELECT 
            tp.*,
            u.full_name,
            u.email
        FROM tutor_profiles tp
        JOIN users u ON u.id = tp.user_id
        WHERE tp.user_id = :user_id
        LIMIT 1
    ");
    $stmt->execute([':user_id' => current_user_id()]);
    $profile = $stmt->fetch();
}

$allSubjects = $pdo->query("
    SELECT id, name
    FROM subjects
    ORDER BY name
")->fetchAll();

$stmt = $pdo->prepare("
    SELECT 
        s.name,
        ts.level
    FROM tutor_subjects ts
    JOIN subjects s ON s.id = ts.subject_id
    WHERE ts.tutor_profile_id = :profile_id
    ORDER BY s.name, ts.level
");
$stmt->execute([':profile_id' => $profile['id']]);
$currentSubjects = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $fullName = clean_text($_POST['full_name'] ?? '', 100);
    $email = clean_text($_POST['email'] ?? '', 120);
    $description = clean_text($_POST['description'] ?? '', 1000);
    $experience = max(0, min(80, (int)($_POST['experience_years'] ?? 0)));
    $price = max(0, min(1000, (float)($_POST['price_per_hour'] ?? 0)));

    $lessonType = $_POST['lesson_type'] ?? 'individual';
    $locationType = $_POST['location_type'] ?? 'online';
    $mainSubjectId = !empty($_POST['main_subject_id']) ? (int)$_POST['main_subject_id'] : null;

    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $level = $_POST['level'] ?? 'incepator';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email invalid.';
    } elseif (!in_array($lessonType, ['individual', 'grup', 'ambele'], true)) {
        $message = 'Tip de lectie invalid.';
    } elseif (!in_array($locationType, ['online', 'fizic', 'ambele'], true)) {
        $message = 'Mod de predare invalid.';
    } elseif (!in_array($level, ['incepator', 'mediu', 'avansat'], true)) {
        $message = 'Nivel invalid.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE users
            SET full_name = :full_name,
                email = :email
            WHERE id = :user_id
        ");
        $stmt->execute([
                ':full_name' => $fullName,
                ':email' => $email,
                ':user_id' => current_user_id()
        ]);

        $stmt = $pdo->prepare("
            UPDATE tutor_profiles
            SET description = :description,
                experience_years = :experience_years,
                price_per_hour = :price_per_hour,
                lesson_type = :lesson_type,
                location_type = :location_type,
                main_subject_id = :main_subject_id,
                status = 'pending'
            WHERE user_id = :user_id
        ");
        $stmt->execute([
                ':description' => $description,
                ':experience_years' => $experience,
                ':price_per_hour' => $price,
                ':lesson_type' => $lessonType,
                ':location_type' => $locationType,
                ':main_subject_id' => $mainSubjectId,
                ':user_id' => current_user_id()
        ]);

        if ($subjectId > 0) {
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO tutor_subjects 
                (tutor_profile_id, subject_id, level)
                VALUES 
                (:tutor_profile_id, :subject_id, :level)
            ");
            $stmt->execute([
                    ':tutor_profile_id' => $profile['id'],
                    ':subject_id' => $subjectId,
                    ':level' => $level
            ]);
        }

        log_action(current_user_id(), 'profile_updated', 'Tutor updated prefilled profile form.');

        $_SESSION['user']['full_name'] = $fullName;
        $_SESSION['user']['email'] = $email;

        $message = 'Profilul a fost salvat. Pentru că ai modificat profilul, statusul revine la pending pentru review admin.';

        $stmt = $pdo->prepare("
            SELECT 
                tp.*,
                u.full_name,
                u.email
            FROM tutor_profiles tp
            JOIN users u ON u.id = tp.user_id
            WHERE tp.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute([':user_id' => current_user_id()]);
        $profile = $stmt->fetch();

        $stmt = $pdo->prepare("
            SELECT 
                s.name,
                ts.level
            FROM tutor_subjects ts
            JOIN subjects s ON s.id = ts.subject_id
            WHERE ts.tutor_profile_id = :profile_id
            ORDER BY s.name, ts.level
        ");
        $stmt->execute([':profile_id' => $profile['id']]);
        $currentSubjects = $stmt->fetchAll();
    }
}

$pageTitle = 'TutorBloom - Profilul meu';
$pageHeading = 'Profilul meu';
$pageSubtitle = 'Editează datele profilului tău de tutor';
$bodyId = 'profile-page';

require_once __DIR__ . '/../includes/header.php';
?>

    <section class="content-section">
        <h3>Formular precompletat cu date din baza de date</h3>

        <?php if ($message): ?>
            <p class="success-message"><?= e($message) ?></p>
        <?php endif; ?>

        <p>
            <strong>Status profil:</strong>
            <span class="profile-tag"><?= e($profile['status']) ?></span>
        </p>

        <form method="post" class="styled-form">
            <?= csrf_field() ?>

            <fieldset>
                <legend><b>Date cont tutor</b></legend>

                <p>
                    <label for="full_name">Nume complet</label><br>
                    <input
                            id="full_name"
                            type="text"
                            name="full_name"
                            value="<?= e($profile['full_name']) ?>"
                            required
                    >
                </p>

                <p>
                    <label for="email">Email</label><br>
                    <input
                            id="email"
                            type="email"
                            name="email"
                            value="<?= e($profile['email']) ?>"
                            required
                    >
                </p>
            </fieldset>

            <fieldset>
                <legend><b>Date profil tutor</b></legend>

                <p>
                    <label for="description">Descriere</label><br>
                    <textarea
                            id="description"
                            name="description"
                            rows="6"
                            cols="60"
                    ><?= e($profile['description']) ?></textarea>
                </p>

                <p>
                    <label for="experience_years">Experiență în ani</label><br>
                    <input
                            id="experience_years"
                            type="number"
                            name="experience_years"
                            min="0"
                            max="80"
                            value="<?= e($profile['experience_years']) ?>"
                    >
                </p>

                <p>
                    <label for="price_per_hour">Preț pe oră</label><br>
                    <input
                            id="price_per_hour"
                            type="number"
                            step="0.01"
                            min="0"
                            max="1000"
                            name="price_per_hour"
                            value="<?= e($profile['price_per_hour']) ?>"
                    >
                </p>

                <p>
                    <label for="lesson_type">Tip lecție</label><br>
                    <select id="lesson_type" name="lesson_type">
                        <option value="individual" <?= $profile['lesson_type'] === 'individual' ? 'selected' : '' ?>>
                            Individual
                        </option>
                        <option value="grup" <?= $profile['lesson_type'] === 'grup' ? 'selected' : '' ?>>
                            Grup
                        </option>
                        <option value="ambele" <?= $profile['lesson_type'] === 'ambele' ? 'selected' : '' ?>>
                            Ambele
                        </option>
                    </select>
                </p>

                <p>
                    <label for="location_type">Mod de predare</label><br>
                    <select id="location_type" name="location_type">
                        <option value="online" <?= $profile['location_type'] === 'online' ? 'selected' : '' ?>>
                            Online
                        </option>
                        <option value="fizic" <?= $profile['location_type'] === 'fizic' ? 'selected' : '' ?>>
                            Fizic
                        </option>
                        <option value="ambele" <?= $profile['location_type'] === 'ambele' ? 'selected' : '' ?>>
                            Ambele
                        </option>
                    </select>
                </p>

                <p>
                    <label for="main_subject_id">Materia principală</label><br>
                    <select id="main_subject_id" name="main_subject_id">
                        <option value="">Alege materia principală</option>

                        <?php foreach ($allSubjects as $subject): ?>
                            <option
                                    value="<?= (int)$subject['id'] ?>"
                                    <?= (int)($profile['main_subject_id'] ?? 0) === (int)$subject['id'] ? 'selected' : '' ?>
                            >
                                <?= e(ucfirst($subject['name'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
            </fieldset>

            <fieldset>
                <legend><b>Adaugă materie predată</b></legend>

                <p>
                    <label for="subject_id">Materie</label><br>
                    <select id="subject_id" name="subject_id">
                        <option value="">Alege o materie</option>

                        <?php foreach ($allSubjects as $subject): ?>
                            <option value="<?= (int)$subject['id'] ?>">
                                <?= e(ucfirst($subject['name'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <label for="level">Nivel</label><br>
                    <select id="level" name="level">
                        <option value="incepator">Începător</option>
                        <option value="mediu">Mediu</option>
                        <option value="avansat">Avansat</option>
                    </select>
                </p>
            </fieldset>

            <p>
                <button type="submit">Salvează profilul</button>
            </p>
        </form>
    </section>

    <section class="content-section">
        <h3>Materii deja salvate în baza de date</h3>

        <?php if ($currentSubjects): ?>
            <div class="table-wrapper results-wrapper">
                <table class="results-table">
                    <thead>
                    <tr>
                        <th>Materie</th>
                        <th>Nivel</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($currentSubjects as $item): ?>
                        <tr>
                            <td data-label="Materie"><?= e($item['name']) ?></td>
                            <td data-label="Nivel"><?= e($item['level']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Nu ai încă materii salvate.</p>
        <?php endif; ?>
    </section>

    <section class="content-section">
        <h3>Poza de profil</h3>

        <?php if (!empty($profile['profile_photo'])): ?>
            <p>
                <img
                        src="<?= e(app_url($profile['profile_photo'])) ?>"
                        width="140"
                        alt="Poza profil"
                >
            </p>

            <form method="post" action="<?= e(app_url('tutor/delete_photo.php')) ?>">
                <?= csrf_field() ?>
                <button type="submit">Sterge poza</button>
            </form>
        <?php else: ?>
            <p>Nu ai poză de profil încărcată.</p>
        <?php endif; ?>

        <form
                method="post"
                action="<?= e(app_url('tutor/upload_photo.php')) ?>"
                enctype="multipart/form-data"
                class="styled-form"
        >
            <?= csrf_field() ?>

            <input
                    type="file"
                    name="profile_photo"
                    accept="image/jpeg,image/png,image/gif"
                    required
            >

            <button type="submit">Încarcă poza</button>
        </form>
    </section>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <p class="error-message"><?= e($_SESSION['flash_error']) ?></p>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <p class="success-message"><?= e($_SESSION['flash_success']) ?></p>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>