<?php
require_once __DIR__ . '/../includes/init.php';
require_role('student');
require_once __DIR__ . '/../includes/ajax_lab_helpers.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $filters = lab_read_filters_from_get();
    $offset = lab_get_offset();
    $limit = lab_get_limit();

    $allTutors = lab_get_filtered_tutors($pdo, $filters);
    $total = count($allTutors);
    $pageTutors = array_slice($allTutors, $offset, $limit);

    echo json_encode([
        'success' => true,
        'total' => $total,
        'offset' => $offset,
        'limit' => $limit,
        'tutors' => lab_prepare_tutors_for_output($pageTutors)
    ]);
} catch (Exception $exception) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Nu s-au putut incarca tutorii.'
    ]);
}