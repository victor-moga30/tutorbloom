<?php
require_once __DIR__ . '/../includes/init.php';
require_role('tutor');

require_post();
verify_csrf_token();

$stmt = $pdo->prepare("
    SELECT id, profile_photo
    FROM tutor_profiles
    WHERE user_id = :user_id
    LIMIT 1
");
$stmt->execute([':user_id' => current_user_id()]);
$profile = $stmt->fetch();

if (!$profile) {
    redirect_to('tutor/profile.php');
}

$currentPhoto = $profile['profile_photo'] ?? '';

if ($currentPhoto !== '') {
    $absolutePath = safe_uploaded_profile_path($currentPhoto);

    if ($absolutePath !== null) {
        unlink($absolutePath);
    }

    $stmt = $pdo->prepare("
        UPDATE tutor_profiles
        SET profile_photo = NULL
        WHERE user_id = :user_id
    ");
    $stmt->execute([':user_id' => current_user_id()]);

    $stmt = $pdo->prepare("
        DELETE FROM uploaded_files
        WHERE user_id = :user_id
          AND file_path = :file_path
    ");
    $stmt->execute([
        ':user_id' => current_user_id(),
        ':file_path' => $currentPhoto
    ]);

    log_action(current_user_id(), 'photo_deleted', 'Tutor deleted profile photo.');
}

redirect_to('tutor/profile.php');