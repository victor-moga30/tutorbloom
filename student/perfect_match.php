<?php
require_once __DIR__ . '/../includes/init.php';
require_role('student');

$steps = [
        'Informatii generale',
        'Preferinte personale',
        'A1',
        'A2',
        'B1',
        'B2',
        'C1',
        'Finalizare'
];

$correctAnswers = [
        'a1_q1' => 'am',
        'a1_q2' => 'is',
        'a1_q3' => 'drink',
        'a2_q1' => 'went',
        'a2_q2' => 'are',
        'a2_q3' => 'any',
        'b1_q1' => 'rains',
        'b1_q2' => 'since',
        'b1_q3' => 'more interesting',
        'b2_q1' => 'had started',
        'b2_q2' => 'had',
        'b2_q3' => 'had I left',
        'c1_q1' => 'had',
        'c1_q2' => 'interpretation',
        'c1_q3' => 'to be'
];

function pm_value($key, $default = '')
{
    return $_SESSION['perfect_match_form'][$key] ?? $default;
}

function pm_checked($key, $value)
{
    return pm_value($key) === $value ? 'checked' : '';
}

function pm_selected($key, $value)
{
    return pm_value($key) === $value ? 'selected' : '';
}

function determine_cefr_level($score)
{
    if ($score <= 3) {
        return 'A1';
    }

    if ($score <= 6) {
        return 'A2';
    }

    if ($score <= 9) {
        return 'B1';
    }

    if ($score <= 12) {
        return 'B2';
    }

    return 'C1';
}

function cefr_to_db_level($cefr)
{
    if ($cefr === 'A1' || $cefr === 'A2') {
        return 'incepator';
    }

    if ($cefr === 'B1' || $cefr === 'B2') {
        return 'mediu';
    }

    return 'avansat';
}

function validate_step($step)
{
    $errors = [];

    if ($step === 0) {
        foreach (['limba', 'clasa', 'nivel', 'obiectiv'] as $field) {
            if (empty($_SESSION['perfect_match_form'][$field])) {
                $errors[] = 'Completeaza toate campurile din pasul "Informatii generale".';
                break;
            }
        }
    }

    if ($step === 1) {
        if (empty($_SESSION['perfect_match_form']['buget']) || (float)$_SESSION['perfect_match_form']['buget'] <= 0) {
            $errors[] = 'Introdu un buget valid.';
        }

        if (empty($_SESSION['perfect_match_form']['format'])) {
            $errors[] = 'Alege formatul sedintelor.';
        }

        if (empty($_SESSION['perfect_match_form']['zile']) || !is_array($_SESSION['perfect_match_form']['zile'])) {
            $errors[] = 'Alege cel putin o zi disponibila.';
        }

        if (empty($_SESSION['perfect_match_form']['frecventa'])) {
            $errors[] = 'Alege frecventa sedintelor.';
        }

        if (empty($_SESSION['perfect_match_form']['durata_plan'])) {
            $errors[] = 'Alege durata planului.';
        }
    }

    $stepQuestions = [
            2 => ['a1_q1', 'a1_q2', 'a1_q3'],
            3 => ['a2_q1', 'a2_q2', 'a2_q3'],
            4 => ['b1_q1', 'b1_q2', 'b1_q3'],
            5 => ['b2_q1', 'b2_q2', 'b2_q3'],
            6 => ['c1_q1', 'c1_q2', 'c1_q3']
    ];

    if (isset($stepQuestions[$step])) {
        foreach ($stepQuestions[$step] as $field) {
            if (empty($_SESSION['perfect_match_form'][$field])) {
                $errors[] = 'Raspunde la toate intrebarile grila din acest pas.';
                break;
            }
        }
    }

    if ($step === 7) {
        if (empty($_SESSION['perfect_match_form']['consimtamant'])) {
            $errors[] = 'Bifeaza acordul pentru finalizare.';
        }
    }

    return $errors;
}

if (!isset($_SESSION['perfect_match_form'])) {
    $_SESSION['perfect_match_form'] = [];
}

$currentStep = isset($_POST['current_step']) ? (int)$_POST['current_step'] : (int)($_GET['step'] ?? 0);
$currentStep = max(0, min(7, $currentStep));

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    foreach ($_POST as $key => $value) {
        if ($key === 'current_step' || $key === 'action' || $key === 'csrf_token') {
            continue;
        }

        $_SESSION['perfect_match_form'][$key] = $value;
    }

    if (!isset($_POST['zile'])) {
        if ($currentStep === 1) {
            $_SESSION['perfect_match_form']['zile'] = [];
        }
    }

    $action = $_POST['action'] ?? 'next';

    if ($action === 'reset') {
        unset($_SESSION['perfect_match_form'], $_SESSION['perfect_match']);
        redirect_to('student/perfect_match.php');
    }

    if ($action === 'prev') {
        redirect_to('student/perfect_match.php?step=' . max(0, $currentStep - 1));
    }

    $errors = validate_step($currentStep);

    if (!$errors && $action === 'next') {
        redirect_to('student/perfect_match.php?step=' . min(7, $currentStep + 1));
    }

    if (!$errors && $action === 'submit') {
        $score = 0;
        $total = count($correctAnswers);

        foreach ($correctAnswers as $field => $correctValue) {
            if (($_SESSION['perfect_match_form'][$field] ?? '') === $correctValue) {
                $score++;
            }
        }

        $cefrLevel = determine_cefr_level($score);
        $dbLevel = cefr_to_db_level($cefrLevel);

        $format = $_SESSION['perfect_match_form']['format'] ?? '';
        $locationType = '';

        if ($format === 'online' || $format === 'fizic') {
            $locationType = $format;
        }

        $_SESSION['perfect_match'] = [
                'subject' => $_SESSION['perfect_match_form']['limba'] ?? '',
                'budget' => $_SESSION['perfect_match_form']['buget'] ?? 150,
                'format' => $format,
                'location_type' => $locationType,
                'objective' => $_SESSION['perfect_match_form']['obiectiv'] ?? '',
                'score' => $score,
                'total' => $total,
                'cefr_level' => $cefrLevel,
                'db_level' => $dbLevel
        ];

        log_action(
                current_user_id(),
                'perfect_match_completed',
                'Level: ' . $cefrLevel . ', score: ' . $score . '/' . $total
        );

        redirect_to(
                'student/search_tutors.php'
                . '?subject=' . urlencode($_SESSION['perfect_match']['subject'])
                . '&level=' . urlencode($_SESSION['perfect_match']['db_level'])
                . '&max_price=' . urlencode((string)$_SESSION['perfect_match']['budget'])
                . '&location_type=' . urlencode($_SESSION['perfect_match']['location_type'])
                . '&from_quiz=1'
        );
    }
}

$pageTitle = 'TutorBloom - Perfect tutor matcher';
$pageHeading = 'Perfect tutor matcher';
$pageSubtitle = 'Completeaza testul si primeste recomandari potrivite';
$bodyId = 'search-page';

require_once __DIR__ . '/../includes/header.php';
?>

    <section class="content-section">
        <form method="post" class="form-wizard" action="<?= e(app_url('student/perfect_match.php?step=' . (int)$currentStep)) ?>">
            <?= csrf_field() ?>

            <h1>Perfect tutor matcher</h1>

            <input type="hidden" name="current_step" value="<?= (int)$currentStep ?>">

            <div class="progress-container">
                <ol>
                    <?php foreach ($steps as $index => $label): ?>
                        <li class="<?= $index === $currentStep ? 'current' : ($index < $currentStep ? 'done' : '') ?>">
                            <?= e($label) ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>

            <?php if ($errors): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="step-container">
                <div class="step current">

                    <?php if ($currentStep === 0): ?>
                        <h3>Informatii generale</h3>

                        <div class="form-group">
                            <label for="limba">Ce limba doresti sa inveti?</label>
                            <select id="limba" name="limba" required>
                                <option value="">Selecteaza</option>
                                <option value="engleza" <?= pm_selected('limba', 'engleza') ?>>Engleza</option>
                                <option value="spaniola" <?= pm_selected('limba', 'spaniola') ?>>Spaniola</option>
                                <option value="franceza" <?= pm_selected('limba', 'franceza') ?>>Franceza</option>
                                <option value="germana" <?= pm_selected('limba', 'germana') ?>>Germana</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="clasa">In ce clasa esti?</label>
                            <select id="clasa" name="clasa" required>
                                <option value="">Selecteaza</option>
                                <option value="gimnaziu" <?= pm_selected('clasa', 'gimnaziu') ?>>Gimnaziu</option>
                                <option value="clasa9" <?= pm_selected('clasa', 'clasa9') ?>>Clasa a IX-a</option>
                                <option value="clasa10" <?= pm_selected('clasa', 'clasa10') ?>>Clasa a X-a</option>
                                <option value="clasa11" <?= pm_selected('clasa', 'clasa11') ?>>Clasa a XI-a</option>
                                <option value="clasa12" <?= pm_selected('clasa', 'clasa12') ?>>Clasa a XII-a</option>
                                <option value="student" <?= pm_selected('clasa', 'student') ?>>Student</option>
                                <option value="adult" <?= pm_selected('clasa', 'adult') ?>>Adult</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="nivel">Care crezi ca este nivelul tau actual?</label>
                            <select id="nivel" name="nivel" required>
                                <option value="">Selecteaza</option>
                                <option value="a1" <?= pm_selected('nivel', 'a1') ?>>A1</option>
                                <option value="a2" <?= pm_selected('nivel', 'a2') ?>>A2</option>
                                <option value="b1" <?= pm_selected('nivel', 'b1') ?>>B1</option>
                                <option value="b2" <?= pm_selected('nivel', 'b2') ?>>B2</option>
                                <option value="c1" <?= pm_selected('nivel', 'c1') ?>>C1</option>
                                <option value="nu_stiu" <?= pm_selected('nivel', 'nu_stiu') ?>>Nu stiu</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="obiectiv">Care este obiectivul tau principal?</label>
                            <select id="obiectiv" name="obiectiv" required>
                                <option value="">Selecteaza</option>
                                <option value="bac" <?= pm_selected('obiectiv', 'bac') ?>>Pregatire pentru bacalaureat</option>
                                <option value="examen" <?= pm_selected('obiectiv', 'examen') ?>>Pregatire pentru examen</option>
                                <option value="conversatie" <?= pm_selected('obiectiv', 'conversatie') ?>>Conversatie</option>
                                <option value="gramatica" <?= pm_selected('obiectiv', 'gramatica') ?>>Gramatica si vocabular</option>
                                <option value="general" <?= pm_selected('obiectiv', 'general') ?>>Invatare generala</option>
                            </select>
                        </div>
                    <?php endif; ?>

                    <?php if ($currentStep === 1): ?>
                        <h3>Preferinte personale</h3>

                        <div class="form-group">
                            <label for="buget">Buget maxim per sedinta (lei)</label>
                            <input id="buget" name="buget" type="number" min="0" step="5" placeholder="Ex: 100" value="<?= e(pm_value('buget')) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Tipul sedintelor</label>
                            <label><input name="format" type="radio" value="online" <?= pm_checked('format', 'online') ?>> Online</label>
                            <label><input name="format" type="radio" value="fizic" <?= pm_checked('format', 'fizic') ?>> Fizic</label>
                            <label><input name="format" type="radio" value="ambele" <?= pm_checked('format', 'ambele') ?>> Ambele</label>
                        </div>

                        <div class="form-group">
                            <label>Zile disponibile</label>
                            <?php
                            $selectedDays = $_SESSION['perfect_match_form']['zile'] ?? [];
                            $days = [
                                    'luni' => 'Luni',
                                    'marti' => 'Marti',
                                    'miercuri' => 'Miercuri',
                                    'joi' => 'Joi',
                                    'vineri' => 'Vineri',
                                    'sambata' => 'Sambata',
                                    'duminica' => 'Duminica'
                            ];
                            ?>
                            <?php foreach ($days as $dayValue => $dayLabel): ?>
                                <label>
                                    <input name="zile[]" type="checkbox" value="<?= e($dayValue) ?>" <?= in_array($dayValue, $selectedDays, true) ? 'checked' : '' ?>>
                                    <?= e($dayLabel) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-group">
                            <label for="frecventa">Cate sedinte pe saptamana doresti?</label>
                            <select id="frecventa" name="frecventa" required>
                                <option value="">Selecteaza</option>
                                <option value="1" <?= pm_selected('frecventa', '1') ?>>1 sedinta</option>
                                <option value="2" <?= pm_selected('frecventa', '2') ?>>2 sedinte</option>
                                <option value="3" <?= pm_selected('frecventa', '3') ?>>3 sedinte</option>
                                <option value="4" <?= pm_selected('frecventa', '4') ?>>4 sedinte</option>
                                <option value="5" <?= pm_selected('frecventa', '5') ?>>5+ sedinte</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="durata_plan">Cat timp vrei sa faci meditatii?</label>
                            <select id="durata_plan" name="durata_plan" required>
                                <option value="">Selecteaza</option>
                                <option value="1_luna" <?= pm_selected('durata_plan', '1_luna') ?>>1 luna</option>
                                <option value="3_luni" <?= pm_selected('durata_plan', '3_luni') ?>>3 luni</option>
                                <option value="6_luni" <?= pm_selected('durata_plan', '6_luni') ?>>6 luni</option>
                                <option value="9_luni" <?= pm_selected('durata_plan', '9_luni') ?>>9 luni</option>
                                <option value="12_luni" <?= pm_selected('durata_plan', '12_luni') ?>>12 luni</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="deadline">Pana cand vrei sa ajungi la rezultatul dorit?</label>
                            <input id="deadline" name="deadline" type="month" value="<?= e(pm_value('deadline')) ?>">
                        </div>

                        <div class="form-group">
                            <label for="motivatie">De ce vrei sa studiezi aceasta limba?</label>
                            <textarea id="motivatie" name="motivatie" rows="5" placeholder="Ex: Vreau sa iau o nota mare la bac..."><?= e(pm_value('motivatie')) ?></textarea>
                        </div>
                    <?php endif; ?>

                    <?php if ($currentStep === 2): ?>
                        <h3>Placement test - A1</h3>

                        <div class="question-block">
                            <p>1. I ___ a student.</p>
                            <label><input type="radio" name="a1_q1" value="am" <?= pm_checked('a1_q1', 'am') ?>> am</label>
                            <label><input type="radio" name="a1_q1" value="is" <?= pm_checked('a1_q1', 'is') ?>> is</label>
                            <label><input type="radio" name="a1_q1" value="are" <?= pm_checked('a1_q1', 'are') ?>> are</label>
                        </div>

                        <div class="question-block">
                            <p>2. She ___ from Romania.</p>
                            <label><input type="radio" name="a1_q2" value="am" <?= pm_checked('a1_q2', 'am') ?>> am</label>
                            <label><input type="radio" name="a1_q2" value="is" <?= pm_checked('a1_q2', 'is') ?>> is</label>
                            <label><input type="radio" name="a1_q2" value="are" <?= pm_checked('a1_q2', 'are') ?>> are</label>
                        </div>

                        <div class="question-block">
                            <p>3. We ___ coffee in the morning.</p>
                            <label><input type="radio" name="a1_q3" value="drink" <?= pm_checked('a1_q3', 'drink') ?>> drink</label>
                            <label><input type="radio" name="a1_q3" value="drinks" <?= pm_checked('a1_q3', 'drinks') ?>> drinks</label>
                            <label><input type="radio" name="a1_q3" value="drinking" <?= pm_checked('a1_q3', 'drinking') ?>> drinking</label>
                        </div>

                        <div class="question-block">
                            <label for="a1_w1">4. Scrie 2-3 propozitii simple despre tine, in engleza.</label>
                            <textarea id="a1_w1" name="a1_w1" rows="4"><?= e(pm_value('a1_w1')) ?></textarea>
                        </div>

                        <div class="question-block">
                            <label for="a1_w2">5. Scrie 2-3 propozitii despre rutina ta zilnica.</label>
                            <textarea id="a1_w2" name="a1_w2" rows="4"><?= e(pm_value('a1_w2')) ?></textarea>
                        </div>
                    <?php endif; ?>

                    <?php if ($currentStep === 3): ?>
                        <h3>Placement test - A2</h3>

                        <div class="question-block">
                            <p>1. Yesterday, I ___ to the cinema with my friends.</p>
                            <label><input type="radio" name="a2_q1" value="go" <?= pm_checked('a2_q1', 'go') ?>> go</label>
                            <label><input type="radio" name="a2_q1" value="went" <?= pm_checked('a2_q1', 'went') ?>> went</label>
                            <label><input type="radio" name="a2_q1" value="gone" <?= pm_checked('a2_q1', 'gone') ?>> gone</label>
                        </div>

                        <div class="question-block">
                            <p>2. There ___ two books on the table.</p>
                            <label><input type="radio" name="a2_q2" value="is" <?= pm_checked('a2_q2', 'is') ?>> is</label>
                            <label><input type="radio" name="a2_q2" value="are" <?= pm_checked('a2_q2', 'are') ?>> are</label>
                            <label><input type="radio" name="a2_q2" value="was" <?= pm_checked('a2_q2', 'was') ?>> was</label>
                        </div>

                        <div class="question-block">
                            <p>3. I don't have ___ milk left.</p>
                            <label><input type="radio" name="a2_q3" value="some" <?= pm_checked('a2_q3', 'some') ?>> some</label>
                            <label><input type="radio" name="a2_q3" value="any" <?= pm_checked('a2_q3', 'any') ?>> any</label>
                            <label><input type="radio" name="a2_q3" value="many" <?= pm_checked('a2_q3', 'many') ?>> many</label>
                        </div>

                        <div class="question-block">
                            <label for="a2_w1">4. Scrie un scurt mesaj unui prieten in care il inviti in oras.</label>
                            <textarea id="a2_w1" name="a2_w1" rows="4"><?= e(pm_value('a2_w1')) ?></textarea>
                        </div>

                        <div class="question-block">
                            <label for="a2_w2">5. Scrie despre o vacanta recenta.</label>
                            <textarea id="a2_w2" name="a2_w2" rows="4"><?= e(pm_value('a2_w2')) ?></textarea>
                        </div>
                    <?php endif; ?>

                    <?php if ($currentStep === 4): ?>
                        <h3>Placement test - B1</h3>

                        <div class="question-block">
                            <p>1. If it ___ tomorrow, we will stay at home.</p>
                            <label><input type="radio" name="b1_q1" value="rain" <?= pm_checked('b1_q1', 'rain') ?>> rain</label>
                            <label><input type="radio" name="b1_q1" value="rains" <?= pm_checked('b1_q1', 'rains') ?>> rains</label>
                            <label><input type="radio" name="b1_q1" value="will rain" <?= pm_checked('b1_q1', 'will rain') ?>> will rain</label>
                        </div>

                        <div class="question-block">
                            <p>2. She has lived here ___ 2019.</p>
                            <label><input type="radio" name="b1_q2" value="for" <?= pm_checked('b1_q2', 'for') ?>> for</label>
                            <label><input type="radio" name="b1_q2" value="since" <?= pm_checked('b1_q2', 'since') ?>> since</label>
                            <label><input type="radio" name="b1_q2" value="from" <?= pm_checked('b1_q2', 'from') ?>> from</label>
                        </div>

                        <div class="question-block">
                            <p>3. This book is ___ than the other one.</p>
                            <label><input type="radio" name="b1_q3" value="interesting" <?= pm_checked('b1_q3', 'interesting') ?>> interesting</label>
                            <label><input type="radio" name="b1_q3" value="more interesting" <?= pm_checked('b1_q3', 'more interesting') ?>> more interesting</label>
                            <label><input type="radio" name="b1_q3" value="most interesting" <?= pm_checked('b1_q3', 'most interesting') ?>> most interesting</label>
                        </div>

                        <div class="question-block">
                            <label for="b1_w1">4. Scrie un e-mail scurt in care explici de ce ai lipsit de la curs.</label>
                            <textarea id="b1_w1" name="b1_w1" rows="4"><?= e(pm_value('b1_w1')) ?></textarea>
                        </div>

                        <div class="question-block">
                            <label for="b1_w2">5. Scrie despre un oras pe care ai vrea sa il vizitezi si de ce.</label>
                            <textarea id="b1_w2" name="b1_w2" rows="4"><?= e(pm_value('b1_w2')) ?></textarea>
                        </div>
                    <?php endif; ?>

                    <?php if ($currentStep === 5): ?>
                        <h3>Placement test - B2</h3>

                        <div class="question-block">
                            <p>1. By the time we arrived, the film ___ .</p>
                            <label><input type="radio" name="b2_q1" value="started" <?= pm_checked('b2_q1', 'started') ?>> started</label>
                            <label><input type="radio" name="b2_q1" value="had started" <?= pm_checked('b2_q1', 'had started') ?>> had started</label>
                            <label><input type="radio" name="b2_q1" value="has started" <?= pm_checked('b2_q1', 'has started') ?>> has started</label>
                        </div>

                        <div class="question-block">
                            <p>2. I wish I ___ more time to study.</p>
                            <label><input type="radio" name="b2_q2" value="have" <?= pm_checked('b2_q2', 'have') ?>> have</label>
                            <label><input type="radio" name="b2_q2" value="had" <?= pm_checked('b2_q2', 'had') ?>> had</label>
                            <label><input type="radio" name="b2_q2" value="would have" <?= pm_checked('b2_q2', 'would have') ?>> would have</label>
                        </div>

                        <div class="question-block">
                            <p>3. Hardly ___ the house when it started raining.</p>
                            <label><input type="radio" name="b2_q3" value="I had left" <?= pm_checked('b2_q3', 'I had left') ?>> I had left</label>
                            <label><input type="radio" name="b2_q3" value="had I left" <?= pm_checked('b2_q3', 'had I left') ?>> had I left</label>
                            <label><input type="radio" name="b2_q3" value="left I" <?= pm_checked('b2_q3', 'left I') ?>> left I</label>
                        </div>

                        <div class="question-block">
                            <label for="b2_w1">4. Scrie un paragraf in care prezinti avantajele si dezavantajele invatarii online.</label>
                            <textarea id="b2_w1" name="b2_w1" rows="5"><?= e(pm_value('b2_w1')) ?></textarea>
                        </div>

                        <div class="question-block">
                            <label for="b2_w2">5. Scrie o opinie argumentata despre importanta limbilor straine in cariera.</label>
                            <textarea id="b2_w2" name="b2_w2" rows="5"><?= e(pm_value('b2_w2')) ?></textarea>
                        </div>
                    <?php endif; ?>

                    <?php if ($currentStep === 6): ?>
                        <h3>Placement test - C1</h3>

                        <div class="question-block">
                            <p>1. No sooner ___ the meeting started than the fire alarm went off.</p>
                            <label><input type="radio" name="c1_q1" value="had" <?= pm_checked('c1_q1', 'had') ?>> had</label>
                            <label><input type="radio" name="c1_q1" value="has" <?= pm_checked('c1_q1', 'has') ?>> has</label>
                            <label><input type="radio" name="c1_q1" value="did" <?= pm_checked('c1_q1', 'did') ?>> did</label>
                        </div>

                        <div class="question-block">
                            <p>2. His remarks were so vague that they were open to ___ .</p>
                            <label><input type="radio" name="c1_q2" value="interpretation" <?= pm_checked('c1_q2', 'interpretation') ?>> interpretation</label>
                            <label><input type="radio" name="c1_q2" value="interpreting" <?= pm_checked('c1_q2', 'interpreting') ?>> interpreting</label>
                            <label><input type="radio" name="c1_q2" value="interpreter" <?= pm_checked('c1_q2', 'interpreter') ?>> interpreter</label>
                        </div>

                        <div class="question-block">
                            <p>3. Were the company ___ in a stronger position, it could expand abroad.</p>
                            <label><input type="radio" name="c1_q3" value="be" <?= pm_checked('c1_q3', 'be') ?>> be</label>
                            <label><input type="radio" name="c1_q3" value="to be" <?= pm_checked('c1_q3', 'to be') ?>> to be</label>
                            <label><input type="radio" name="c1_q3" value="being" <?= pm_checked('c1_q3', 'being') ?>> being</label>
                        </div>

                        <div class="question-block">
                            <label for="c1_w1">4. Scrie un paragraf argumentativ despre rolul tehnologiei in educatie.</label>
                            <textarea id="c1_w1" name="c1_w1" rows="5"><?= e(pm_value('c1_w1')) ?></textarea>
                        </div>

                        <div class="question-block">
                            <label for="c1_w2">5. Redacteaza un raspuns formal la un e-mail in care refuzi politicos o invitatie.</label>
                            <textarea id="c1_w2" name="c1_w2" rows="5"><?= e(pm_value('c1_w2')) ?></textarea>
                        </div>
                    <?php endif; ?>

                    <?php if ($currentStep === 7): ?>
                        <h3>Finalizare</h3>

                        <p>Apasa "Submit" pentru a vedea recomandarile personalizate de tutori.</p>

                        <div class="form-group">
                            <label for="consimtamant">
                                <input id="consimtamant" name="consimtamant" type="checkbox" value="1" <?= pm_checked('consimtamant', '1') ?>>
                                Sunt de acord ca tutorii sa poata vedea rezultatele testului meu si raspunsurile scrise.
                            </label>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="controls">
                <?php if ($currentStep > 0): ?>
                    <button class="prev-btn" type="submit" name="action" value="prev">Prev</button>
                <?php endif; ?>

                <button class="prev-btn" type="submit" name="action" value="reset">Reset</button>

                <?php if ($currentStep < 7): ?>
                    <button class="next-btn" type="submit" name="action" value="next">Next</button>
                <?php else: ?>
                    <button class="submit-btn" type="submit" name="action" value="submit">Submit</button>
                <?php endif; ?>
            </div>
        </form>
    </section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>