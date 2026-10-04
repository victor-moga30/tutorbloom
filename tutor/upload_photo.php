<?php
require_once __DIR__ . '/../includes/init.php';
require_role('tutor');

function fail_upload(string $message): void
{
    $_SESSION['flash_error'] = $message;
    redirect_to('tutor/profile.php');
}

require_post();
verify_csrf_token();

if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    fail_upload('Upload invalid. Alege un fisier valid.');
}

if ($_FILES['profile_photo']['size'] > 2 * 1024 * 1024) {
    fail_upload('Fisierul este prea mare. Maxim 2MB.');
}

$allowedMimeTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif'
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['profile_photo']['tmp_name']);
finfo_close($finfo);

if (!isset($allowedMimeTypes[$mimeType])) {
    fail_upload('Tip de fisier invalid. Sunt permise doar imagini JPG, PNG sau GIF.');
}

$imageInfo = getimagesize($_FILES['profile_photo']['tmp_name']);

if ($imageInfo === false) {
    fail_upload('Fisierul incarcat nu este o imagine valida.');
}

$uploadsDir = __DIR__ . '/../uploads/profiles';

if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$newName = bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mimeType];
$relativePath = 'uploads/profiles/' . $newName;
$absolutePath = $uploadsDir . DIRECTORY_SEPARATOR . $newName;

if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $absolutePath)) {
    fail_upload('Nu s-a putut salva fisierul.');
}

chmod($absolutePath, 0644);

$stmt = $pdo->prepare("
    SELECT profile_photo
    FROM tutor_profiles
    WHERE user_id = :user_id
    LIMIT 1
");
$stmt->execute([
    ':user_id' => current_user_id()
]);
$profile = $stmt->fetch();

if ($profile && !empty($profile['profile_photo'])) {
    $oldPath = safe_uploaded_profile_path($profile['profile_photo']);

    if ($oldPath !== null) {
        unlink($oldPath);
    }
}

$stmt = $pdo->prepare("
    UPDATE tutor_profiles
    SET profile_photo = :photo
    WHERE user_id = :user_id
");
$stmt->execute([
    ':photo' => $relativePath,
    ':user_id' => current_user_id()
]);

$stmt = $pdo->prepare("
    INSERT INTO uploaded_files (user_id, file_name, file_path, file_type)
    VALUES (:user_id, :file_name, :file_path, :file_type)
");
$stmt->execute([
    ':user_id' => current_user_id(),
    ':file_name' => $newName,
    ':file_path' => $relativePath,
    ':file_type' => $mimeType
]);

log_action(current_user_id(), 'photo_uploaded', $relativePath);

$_SESSION['flash_success'] = 'Poza a fost incarcata cu succes.';
redirect_to('tutor/profile.php');