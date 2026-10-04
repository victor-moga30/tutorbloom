<?php
require_once __DIR__ . '/../includes/init.php';
require_role('admin');

header('Content-Type: application/json; charset=utf-8');

try {
    require_post();
    verify_csrf_token();

    $id = get_int_from_post('id');
    $fullName = clean_text($_POST['full_name'] ?? '', 100);
    $email = clean_text($_POST['email'] ?? '', 120);
    $description = clean_text($_POST['description'] ?? '', 1000);
    $experienceYears = get_int_from_post('experience_years');
    $pricePerHour = filter_input(INPUT_POST, 'price_per_hour', FILTER_VALIDATE_FLOAT);
    $lessonType = $_POST['lesson_type'] ?? '';
    $locationType = $_POST['location_type'] ?? '';
    $status = $_POST['status'] ?? '';

    $validLessonTypes = ['individual', 'grup', 'ambele'];
    $validLocationTypes = ['online', 'fizic', 'ambele'];
    $validStatuses = ['pending', 'approved', 'rejected'];

    if ($id <= 0) {
        throw new RuntimeException('ID invalid.');
    }

    if ($fullName === '') {
        throw new RuntimeException('Numele este obligatoriu.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Email invalid.');
    }

    if ($experienceYears < 0 || $experienceYears > 60) {
        throw new RuntimeException('Experienta trebuie sa fie intre 0 si 60 de ani.');
    }

    if ($pricePerHour === false || $pricePerHour === null || $pricePerHour < 0 || $pricePerHour > 1000) {
        throw new RuntimeException('Pretul trebuie sa fie intre 0 si 1000.');
    }

    if (!in_array($lessonType, $validLessonTypes, true)) {
        throw new RuntimeException('Tipul lectiei este invalid.');
    }

    if (!in_array($locationType, $validLocationTypes, true)) {
        throw new RuntimeException('Tipul de predare este invalid.');
    }

    if (!in_array($status, $validStatuses, true)) {
        throw new RuntimeException('Status invalid.');
    }

    $stmt = $pdo->prepare("
        SELECT user_id
        FROM tutor_profiles
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([':id' => $id]);
    $profile = $stmt->fetch();

    if (!$profile) {
        throw new RuntimeException('Tutorul nu exista.');
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        UPDATE users
        SET full_name = :full_name,
            email = :email
        WHERE id = :user_id
    ");

    $stmt->execute([
        ':full_name' => $fullName,
        ':email' => $email,
        ':user_id' => (int)$profile['user_id']
    ]);

    $stmt = $pdo->prepare("
        UPDATE tutor_profiles
        SET description = :description,
            experience_years = :experience_years,
            price_per_hour = :price_per_hour,
            lesson_type = :lesson_type,
            location_type = :location_type,
            status = :status
        WHERE id = :id
    ");

    $stmt->execute([
        ':description' => $description,
        ':experience_years' => $experienceYears,
        ':price_per_hour' => $pricePerHour,
        ':lesson_type' => $lessonType,
        ':location_type' => $locationType,
        ':status' => $status,
        ':id' => $id
    ]);

    $pdo->commit();

    log_action(current_user_id(), 'ajax_update_tutor', 'Admin updated tutor profile ID ' . $id . '.');

    echo json_encode([
        'success' => true,
        'message' => 'Modificarile au fost salvate.',
        'tutor' => [
            'id' => $id,
            'full_name' => $fullName,
            'email' => $email,
            'description' => $description,
            'experience_years' => $experienceYears,
            'price_per_hour' => $pricePerHour,
            'lesson_type' => $lessonType,
            'location_type' => $locationType,
            'status' => $status
        ]
    ]);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage()
    ]);
}