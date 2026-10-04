<?php

function lab_read_filters_from_get(): array
{
    return [
        'subject' => trim($_GET['subject'] ?? ''),
        'level' => trim($_GET['level'] ?? ''),
        'max_price' => trim($_GET['max_price'] ?? '150'),
        'lesson_type' => trim($_GET['lesson_type'] ?? ''),
        'location_type' => trim($_GET['location_type'] ?? ''),
        'keyword' => trim($_GET['keyword'] ?? '')
    ];
}

function lab_get_offset(): int
{
    $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT);

    if ($offset === false || $offset === null || $offset < 0) {
        return 0;
    }

    return $offset;
}

function lab_get_limit(): int
{
    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);

    if ($limit === false || $limit === null || $limit < 1) {
        return 3;
    }

    if ($limit > 20) {
        return 20;
    }

    return $limit;
}

function lab_tutor_match_score(array $tutor, array $filters): array
{
    $score = 0;
    $reasons = [];

    $selectedSubject = $filters['subject'];
    $selectedLevel = $filters['level'];
    $maxPrice = (float)$filters['max_price'];
    $lessonType = $filters['lesson_type'];
    $locationType = $filters['location_type'];
    $keyword = $filters['keyword'];

    $subjectNames = $tutor['subject_names'] ?? '';
    $tutorLevels = $tutor['tutor_levels'] ?? '';

    if ($selectedSubject !== '' && stripos($subjectNames, $selectedSubject) !== false) {
        $score += 30;
        $reasons[] = 'Preda materia selectata.';
    }

    if ($selectedLevel !== '' && stripos($tutorLevels, $selectedLevel) !== false) {
        $score += 20;
        $reasons[] = 'Se potriveste cu nivelul estimat.';
    } elseif ($selectedLevel !== '') {
        $score += 6;
        $reasons[] = 'Preda materia aleasa, dar nivelul nu este potrivirea principala.';
    }

    $price = (float)$tutor['price_per_hour'];

    if ($maxPrice > 0 && $price <= $maxPrice) {
        $score += 15;
        $reasons[] = 'Se incadreaza in buget.';
    } elseif ($maxPrice > 0 && $price <= $maxPrice * 1.30) {
        $score += 8;
        $reasons[] = 'Depaseste putin bugetul, dar ramane o optiune apropiata.';
    } elseif ($maxPrice > 0) {
        $score += 2;
        $reasons[] = 'Depaseste bugetul ales.';
    }

    if ($lessonType !== '' && ($tutor['lesson_type'] === $lessonType || $tutor['lesson_type'] === 'ambele')) {
        $score += 10;
        $reasons[] = 'Are tipul de lectie dorit.';
    } elseif ($lessonType !== '') {
        $score += 3;
    }

    if ($locationType !== '' && ($tutor['location_type'] === $locationType || $tutor['location_type'] === 'ambele')) {
        $score += 10;
        $reasons[] = 'Se potriveste cu formatul dorit.';
    } elseif ($locationType !== '') {
        $score += 3;
    }

    if ($keyword !== '' && stripos($tutor['description'] ?? '', $keyword) !== false) {
        $score += 5;
        $reasons[] = 'Descrierea contine cuvantul cautat.';
    }

    if ((int)$tutor['experience_years'] >= 5) {
        $score += 10;
        $reasons[] = 'Are experienta foarte buna.';
    } elseif ((int)$tutor['experience_years'] >= 3) {
        $score += 7;
        $reasons[] = 'Are experienta relevanta.';
    } else {
        $score += 3;
    }

    if (!$reasons) {
        $reasons[] = 'Tutor aprobat disponibil in platforma.';
    }

    return [
        'score' => min($score, 100),
        'reasons' => $reasons
    ];
}

function lab_get_filtered_tutors(PDO $pdo, array $filters): array
{
    $sql = "
        SELECT
            tp.*,
            u.full_name,
            u.email,
            GROUP_CONCAT(DISTINCT CONCAT(s.name, ' - ', ts.level) SEPARATOR ', ') AS subjects,
            GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS subject_names,
            GROUP_CONCAT(DISTINCT ts.level SEPARATOR ', ') AS tutor_levels
        FROM tutor_profiles tp
        JOIN users u ON u.id = tp.user_id
        LEFT JOIN tutor_subjects ts ON ts.tutor_profile_id = tp.id
        LEFT JOIN subjects s ON s.id = ts.subject_id
        WHERE tp.status = 'approved'
    ";

    $params = [];

    if ($filters['subject'] !== '') {
        $sql .= "
            AND EXISTS (
                SELECT 1
                FROM tutor_subjects ts2
                JOIN subjects s2 ON s2.id = ts2.subject_id
                WHERE ts2.tutor_profile_id = tp.id
                  AND s2.name = :subject
            )
        ";
        $params[':subject'] = $filters['subject'];
    }

    if ($filters['keyword'] !== '') {
        $sql .= " AND (u.full_name LIKE :keyword OR tp.description LIKE :keyword) ";
        $params[':keyword'] = '%' . $filters['keyword'] . '%';
    }

    $sql .= "
        GROUP BY tp.id
        ORDER BY tp.experience_years DESC, tp.price_per_hour ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tutors = $stmt->fetchAll();

    foreach ($tutors as &$tutor) {
        $match = lab_tutor_match_score($tutor, $filters);

        $tutor['match_score'] = $match['score'];
        $tutor['match_reasons'] = $match['reasons'];
        $tutor['profile_photo'] = $tutor['profile_photo'] ?: 'assets/images/tutor1.jpg';
    }

    unset($tutor);

    usort($tutors, function ($a, $b) {
        return $b['match_score'] <=> $a['match_score'];
    });

    return $tutors;
}

function lab_prepare_tutors_for_output(array $tutors): array
{
    $result = [];

    foreach ($tutors as $tutor) {
        $result[] = [
            'id' => (int)$tutor['id'],
            'full_name' => $tutor['full_name'],
            'email' => $tutor['email'],
            'subjects' => $tutor['subjects'] ?? '',
            'description' => $tutor['description'] ?? '',
            'experience_years' => (int)$tutor['experience_years'],
            'price_per_hour' => (float)$tutor['price_per_hour'],
            'lesson_type' => $tutor['lesson_type'],
            'location_type' => $tutor['location_type'],
            'profile_photo' => $tutor['profile_photo'] ?: 'assets/images/tutor1.jpg',
            'match_score' => (int)$tutor['match_score'],
            'match_reasons' => $tutor['match_reasons']
        ];
    }

    return $result;
}

function lab_get_subjects(PDO $pdo): array
{
    return $pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
}

function lab_get_all_tutors_for_edit(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT tp.id, u.full_name, u.email, tp.status
        FROM tutor_profiles tp
        JOIN users u ON u.id = tp.user_id
        ORDER BY tp.id
    ");

    return $stmt->fetchAll();
}