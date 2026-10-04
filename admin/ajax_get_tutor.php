<?php
require_once __DIR__ . '/../includes/init.php';
require_role('admin');

header('Content-Type: application/json; charset=utf-8');

try {
    $id = get_int_from_get('id');

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID invalid.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT
            tp.id,
            tp.user_id,
            tp.description,
            tp.experience_years,
            tp.price_per_hour,
            tp.lesson_type,
            tp.location_type,
            tp.status,
            u.full_name,
            u.email
        FROM tutor_profiles tp
        JOIN users u ON u.id = tp.user_id
        WHERE tp.id = :id
        LIMIT 1
    ");

    $stmt->execute([':id' => $id]);
    $tutor = $stmt->fetch();

    if (!$tutor) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Tutorul nu exista.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'tutor' => [
            'id' => (int)$tutor['id'],
            'full_name' => $tutor['full_name'],
            'email' => $tutor['email'],
            'description' => $tutor['description'],
            'experience_years' => (int)$tutor['experience_years'],
            'price_per_hour' => (float)$tutor['price_per_hour'],
            'lesson_type' => $tutor['lesson_type'],
            'location_type' => $tutor['location_type'],
            'status' => $tutor['status']
        ]
    ]);
} catch (Exception $exception) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Nu s-au putut incarca datele tutorului.'
    ]);
}