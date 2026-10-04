const correctAnswers = {
    a1_q1: "am",
    a1_q2: "is",
    a1_q3: "drink",

    a2_q1: "went",
    a2_q2: "are",
    a2_q3: "any",

    b1_q1: "rains",
    b1_q2: "since",
    b1_q3: "more interesting",

    b2_q1: "had started",
    b2_q2: "had",
    b2_q3: "had I left",

    c1_q1: "had",
    c1_q2: "interpretation",
    c1_q3: "to be"
};

$(function () {
    const $progress = $(".progress");
    const $stepsContainer = $(".step-container");
    const $stepIndicators = $(".progress-container li");
    const $steps = $(".step");
    const $prevButton = $(".prev-btn");
    const $nextButton = $(".next-btn");
    const $submitButton = $(".submit-btn");
    const $errorBox = $("#error-message");
    const $reviewButton = $("#review-btn");
    const $findMatchButton = $("#find-match-btn");

    document.documentElement.style.setProperty("--steps", $stepIndicators.length);

    let currentStep = 0;
    let reviewMode = false;
    let firstRender = true;

    function updateProgress() {
        const width = currentStep / ($stepIndicators.length - 1);

        $progress.css("transform", `scaleX(${width})`);
        $stepsContainer.css("height", $steps.eq(currentStep).outerHeight() + "px");

        $stepIndicators.each(function (index) {
            $(this)
                .toggleClass("current", currentStep === index)
                .toggleClass("done", currentStep > index);
        });

        $steps.each(function (index) {
            $(this)
                .css("transform", `translateX(-${currentStep * 100}%)`)
                .toggleClass("current", currentStep === index);
        });

        updateButtons();
        scrollToTopOfForm();
    }

    function updateButtons() {
        if (reviewMode) {
            $prevButton.prop("hidden", currentStep === 0);
            $nextButton.prop("hidden", currentStep === $stepIndicators.length - 1);
            $submitButton.prop("hidden", true);
            return;
        }

        $prevButton.prop("hidden", currentStep === 0);
        $nextButton.prop("hidden", currentStep === $stepIndicators.length - 1);
        $submitButton.prop("hidden", currentStep !== $stepIndicators.length - 1);
    }

    function isValidStep() {
        if (currentStep === 0) {
            return validateStep1();
        }

        if (currentStep === 1) {
            return validateStep2();
        }

        if (currentStep >= 2 && currentStep <= 6) {
            return validateTestStep();
        }

        return true;
    }

    function validateStep1() {
        clearFieldErrors();
        clearError();

        let valid = true;

        const $fields = $("#limba, #clasa, #nivel, #obiectiv");

        $fields.each(function () {
            if ($(this).val() === "") {
                $(this).addClass("error-field");
                valid = false;
            }
        });

        if (!valid) {
            showError("Te rog completează toate câmpurile din acest pas.");
        }

        return valid;
    }

    function validateStep2() {
        clearFieldErrors();
        clearError();

        let valid = true;

        const $buget = $("#buget");
        const $format = $('input[name="format"]');
        const $zile = $('input[name="zile"]');
        const $frecventa = $("#frecventa");
        const $durata = $("#durata_plan");

        if ($buget.val() === "" || Number($buget.val()) <= 0) {
            $buget.addClass("error-field");
            valid = false;
        }

        if ($format.filter(":checked").length === 0) {
            $format.parent().addClass("error-field");
            valid = false;
        }

        if ($zile.filter(":checked").length === 0) {
            $zile.parent().addClass("error-field");
            valid = false;
        }

        if ($frecventa.val() === "") {
            $frecventa.addClass("error-field");
            valid = false;
        }

        if ($durata.val() === "") {
            $durata.addClass("error-field");
            valid = false;
        }

        if (!valid) {
            showError("Te rog completează toate câmpurile obligatorii.");
        }

        return valid;
    }

    function validateTestStep() {
        clearFieldErrors();
        clearError();

        let valid = true;
        const $currentStepElement = $steps.eq(currentStep);
        const radioGroups = {};

        $currentStepElement.find('input[type="radio"]').each(function () {
            radioGroups[$(this).attr("name")] = true;
        });

        $.each(radioGroups, function (name) {
            const $checked = $currentStepElement.find(`input[name="${name}"]:checked`);

            if ($checked.length === 0) {
                $currentStepElement
                    .find(`input[name="${name}"]`)
                    .parent()
                    .addClass("error-field");

                valid = false;
            }
        });

        if (!valid) {
            showError("Te rog răspunde la toate întrebările grilă.");
        }

        return valid;
    }

    function showError(message) {
        $errorBox.text(message).show();
    }

    function clearError() {
        $errorBox.hide();
    }

    function clearFieldErrors() {
        $(".error-field").removeClass("error-field");
    }

    function calculateScore() {
        let score = 0;
        let total = 0;

        $.each(correctAnswers, function (key, correctValue) {
            total++;

            const selectedValue = $(`input[name="${key}"]:checked`).val();

            if (selectedValue === correctValue) {
                score++;
            }
        });

        return {
            score: score,
            total: total
        };
    }

    function determineLevel(score) {
        if (score <= 3) return "A1";
        if (score <= 6) return "A2";
        if (score <= 9) return "B1";
        if (score <= 12) return "B2";
        return "C1";
    }

    function evaluatePlan(level) {
        const durata = $("#durata_plan").val();

        if (!durata) return "";

        if (level === "A1" && durata === "1_luna") {
            return "Planul ales este destul de ambițios pentru nivelul tău.";
        }

        if (level === "B2" || level === "C1") {
            return "Ai deja un nivel bun. Poți progresa rapid!";
        }

        return "Planul tău pare realist.";
    }

    function showResult() {
        const $resultBox = $("#result-box");

        const result = calculateScore();
        const score = result.score;
        const total = result.total;
        const level = determineLevel(score);
        const message = evaluatePlan(level);

        $resultBox.prop("hidden", false);
        $reviewButton.prop("hidden", false);
        $findMatchButton.prop("hidden", false);

        $resultBox.html(`
            <h3>Rezultatul tău</h3>
            <p><strong>Scor:</strong> ${score} / ${total}</p>
            <p><strong>Nivel estimat:</strong> ${level}</p>
            <p><strong>Feedback:</strong> ${message}</p>
        `);

        $prevButton.prop("hidden", true);
        $nextButton.prop("hidden", true);
        $submitButton.prop("hidden", true);

        $stepsContainer.css("height", $steps.eq(currentStep).outerHeight() + "px");
    }

    function markAnswers() {
        $.each(correctAnswers, function (key, correctValue) {
            $(`input[name="${key}"]`).each(function () {
                const $radio = $(this);
                const $label = $radio.parent();

                $label.removeClass("correct-answer wrong-answer correct-label wrong-label");

                if ($radio.val() === correctValue) {
                    $label.addClass("correct-label");
                }

                if ($radio.is(":checked") && $radio.val() === correctValue) {
                    $label.addClass("correct-answer");
                }

                if ($radio.is(":checked") && $radio.val() !== correctValue) {
                    $label.addClass("wrong-answer");
                }
            });
        });
    }

    function lockAnswers() {
        $('input[type="radio"], textarea, select, input[type="number"], input[type="month"], input[type="checkbox"]')
            .prop("disabled", true);
    }

    function scrollToTopOfForm() {
        if (firstRender) {
            firstRender = false;
            return;
        }

        const form = $(".form-wizard")[0];

        if (form) {
            form.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        }
    }

    function getStudentProfile(levelFromTest) {
        const selectedDays = $('input[name="zile"]:checked').map(function () {
            return $(this).val();
        }).get();

        let studentType = $("#clasa").val();

        if (studentType === "clasa9" || studentType === "clasa10" || studentType === "clasa11") {
            studentType = "liceu";
        }

        if (studentType === "clasa12") {
            studentType = "bac";
        }

        return {
            language: $("#limba").val(),
            studentType: studentType,
            level: levelFromTest.toLowerCase(),
            objective: $("#obiectiv").val(),
            budget: Number($("#buget").val()),
            format: $('input[name="format"]:checked').val(),
            availableDays: selectedDays
        };
    }

    function scoreObjective(tutor, student) {
        return tutor.focusAreas.includes(student.objective) ? 20 : 0;
    }

    function scoreStudentType(tutor, student) {
        return tutor.studentTypes.includes(student.studentType) ? 20 : 0;
    }

    function scoreLevel(tutor, student) {
        if (student.level === "a1" || student.level === "a2") {
            if (tutor.focusAreas.includes("general") || tutor.focusAreas.includes("gramatica")) {
                return 15;
            }
        }

        if (student.level === "b1" || student.level === "b2") {
            if (tutor.focusAreas.includes("examen") || tutor.focusAreas.includes("general") || tutor.focusAreas.includes("conversatie")) {
                return 15;
            }
        }

        if (student.level === "c1") {
            if (tutor.focusAreas.includes("examen") || tutor.focusAreas.includes("conversatie")) {
                return 15;
            }
        }

        return 6;
    }

    function scoreCredentials(tutor) {
        let score = 0;

        if (tutor.verified) {
            score += 8;
        }

        score += Math.min(tutor.credentialsScore, 7);

        return Math.min(score, 15);
    }

    function scoreReviews(tutor, student) {
        let score = 0;

        score += Math.min(tutor.averageRating * 2, 10);
        score += Math.min(tutor.reviewCount / 5, 5);

        if (tutor.reviewTags.includes(student.studentType) || tutor.reviewTags.includes(student.objective)) {
            score += 2;
        }

        return Math.min(score, 15);
    }

    function scoreExperience(tutor) {
        if (tutor.yearsExperience >= 15) return 10;
        if (tutor.yearsExperience >= 10) return 8;
        if (tutor.yearsExperience >= 5) return 6;
        if (tutor.yearsExperience >= 2) return 4;
        return 2;
    }

    function scoreBudget(tutor, student) {
        if (tutor.price <= student.budget) {
            return 5;
        }

        if (tutor.price <= student.budget * 1.1) {
            return 3;
        }

        return 0;
    }

    function scoreAvailabilityAndFormat(tutor, student) {
        let score = 0;

        if (tutor.format === student.format || tutor.format === "ambele" || student.format === "ambele") {
            score += 3;
        }

        const commonDays = tutor.availableDays.filter(function (day) {
            return student.availableDays.includes(day);
        }).length;

        if (commonDays >= 2) {
            score += 2;
        } else if (commonDays === 1) {
            score += 1;
        }

        return score;
    }

    function getBestTutorMatches(levelFromTest) {
        const student = getStudentProfile(levelFromTest);

        return tutors
            .filter(function (tutor) {
                return tutor.language === student.language;
            })
            .map(function (tutor) {
                return {
                    ...tutor,
                    matchScore: calculateTutorMatchScore(tutor, student)
                };
            })
            .sort(function (a, b) {
                return b.matchScore - a.matchScore;
            });
    }

    function generateMatchReason(tutor, student) {
        const reasons = [];

        if (tutor.verified) {
            reasons.push("are credentiale verificate");
        }

        if (tutor.studentTypes.includes(student.studentType)) {
            reasons.push("lucrează cu elevi din categoria ta");
        }

        if (tutor.focusAreas.includes(student.objective)) {
            reasons.push("este potrivit pentru obiectivul tău");
        }

        if (tutor.price <= student.budget) {
            reasons.push("se încadrează în bugetul tău");
        } else if (tutor.price <= student.budget * 1.1) {
            reasons.push("depășește bugetul doar foarte puțin");
        }

        if (tutor.reviewCount >= 20 && tutor.averageRating >= 4.8) {
            reasons.push("are review-uri foarte bune");
        }

        return reasons;
    }

    function calculateTutorMatchScore(tutor, student) {
        let totalScore = 0;

        totalScore += scoreStudentType(tutor, student);
        totalScore += scoreObjective(tutor, student);
        totalScore += scoreLevel(tutor, student);
        totalScore += scoreCredentials(tutor);
        totalScore += scoreReviews(tutor, student);
        totalScore += scoreExperience(tutor);
        totalScore += scoreBudget(tutor, student);
        totalScore += scoreAvailabilityAndFormat(tutor, student);

        return Math.min(Math.round(totalScore), 100);
    }

    $prevButton.on("click", function (event) {
        event.preventDefault();

        if (currentStep > 0) {
            currentStep--;
            updateProgress();
        }
    });

    $nextButton.on("click", function (event) {
        event.preventDefault();

        if (!reviewMode && !isValidStep()) {
            return;
        }

        if (currentStep < $stepIndicators.length - 1) {
            currentStep++;
            updateProgress();
        }
    });

    $("input, select, textarea").on("input", function () {
        $(this).removeClass("error-field");
    });

    $(".form-wizard").on("submit", function (event) {
        event.preventDefault();
        showResult();
    });

    $reviewButton.on("click", function () {
        reviewMode = true;

        lockAnswers();
        markAnswers();

        currentStep = 2;
        updateProgress();

        $prevButton.prop("hidden", false);
        $nextButton.prop("hidden", false);
        $reviewButton.prop("hidden", true);
    });

    $findMatchButton.on("click", function () {
        const result = calculateScore();
        const level = determineLevel(result.score);

        const student = getStudentProfile(level);
        const matches = getBestTutorMatches(level).map(function (tutor) {
            return {
                ...tutor,
                reasons: generateMatchReason(tutor, student)
            };
        });

        localStorage.setItem("perfectTutorMatchResults", JSON.stringify({
            student: student,
            matches: matches
        }));

        window.location.href = "search_tutors.html?smart_match=1";
    });

    updateProgress();
});

const tutors = [
    {
        id: 1,
        name: "Maria Popescu",
        language: "engleza",
        verified: true,
        credentialsScore: 10,
        credentials: ["Cambridge C2", "Facultatea de Litere", "Modul psihopedagogic"],
        yearsExperience: 12,
        averageRating: 4.9,
        reviewCount: 34,
        reviewTags: ["liceu", "bac", "gramatica"],
        studentTypes: ["gimnaziu", "liceu", "bac"],
        focusAreas: ["bac", "gramatica", "examen"],
        format: "online",
        availableDays: ["luni", "marti", "joi", "vineri"],
        price: 100
    },
    {
        id: 2,
        name: "Andrei Ionescu",
        language: "engleza",
        verified: true,
        credentialsScore: 8,
        credentials: ["IELTS 8.5", "Facultatea de Limbi Straine"],
        yearsExperience: 5,
        averageRating: 5.0,
        reviewCount: 25,
        reviewTags: ["adulti", "conversatie", "business"],
        studentTypes: ["student", "adult"],
        focusAreas: ["conversatie", "general", "examen"],
        format: "ambele",
        availableDays: ["miercuri", "joi", "vineri", "sambata"],
        price: 110
    },
    {
        id: 3,
        name: "Elena Georgescu",
        language: "engleza",
        verified: true,
        credentialsScore: 9,
        credentials: ["Cambridge C1", "Facultatea de Litere"],
        yearsExperience: 7,
        averageRating: 4.8,
        reviewCount: 18,
        reviewTags: ["gimnaziu", "incepatori", "gramatica"],
        studentTypes: ["gimnaziu", "liceu"],
        focusAreas: ["general", "gramatica"],
        format: "online",
        availableDays: ["luni", "miercuri", "vineri"],
        price: 80
    },
    {
        id: 4,
        name: "David Marinescu",
        language: "engleza",
        verified: false,
        credentialsScore: 7,
        credentials: ["Cambridge C1"],
        yearsExperience: 15,
        averageRating: 4.6,
        reviewCount: 6,
        reviewTags: ["liceu", "examen"],
        studentTypes: ["liceu", "bac", "student"],
        focusAreas: ["examen", "general"],
        format: "fizic",
        availableDays: ["marti", "joi", "sambata"],
        price: 90
    },
    {
        id: 5,
        name: "Ioana Stan",
        language: "engleza",
        verified: true,
        credentialsScore: 9,
        credentials: ["CELTA", "Cambridge C2"],
        yearsExperience: 6,
        averageRating: 4.9,
        reviewCount: 29,
        reviewTags: ["conversatie", "studenti", "adulti"],
        studentTypes: ["student", "adult"],
        focusAreas: ["conversatie", "general"],
        format: "online",
        availableDays: ["luni", "marti", "miercuri", "duminica"],
        price: 105
    },
    {
        id: 6,
        name: "Raluca Dumitrescu",
        language: "engleza",
        verified: true,
        credentialsScore: 10,
        credentials: ["Profesor titular", "Facultatea de Litere", "Modul psihopedagogic"],
        yearsExperience: 18,
        averageRating: 4.7,
        reviewCount: 21,
        reviewTags: ["gimnaziu", "liceu", "bac"],
        studentTypes: ["gimnaziu", "liceu", "bac"],
        focusAreas: ["bac", "gramatica", "general"],
        format: "ambele",
        availableDays: ["luni", "marti", "joi"],
        price: 120
    },
    {
        id: 7,
        name: "Cristina Pavel",
        language: "engleza",
        verified: true,
        credentialsScore: 8,
        credentials: ["TEFL", "Cambridge C1"],
        yearsExperience: 4,
        averageRating: 4.9,
        reviewCount: 31,
        reviewTags: ["gimnaziu", "copii", "incepatori"],
        studentTypes: ["gimnaziu"],
        focusAreas: ["general", "gramatica"],
        format: "online",
        availableDays: ["luni", "miercuri", "vineri", "sambata"],
        price: 75
    },
    {
        id: 8,
        name: "Mihai Petrescu",
        language: "engleza",
        verified: false,
        credentialsScore: 6,
        credentials: ["IELTS 7.5"],
        yearsExperience: 3,
        averageRating: 4.4,
        reviewCount: 12,
        reviewTags: ["conversatie", "adulti"],
        studentTypes: ["student", "adult"],
        focusAreas: ["conversatie"],
        format: "online",
        availableDays: ["marti", "joi", "vineri"],
        price: 70
    },
    {
        id: 9,
        name: "Ana Voinea",
        language: "engleza",
        verified: true,
        credentialsScore: 9,
        credentials: ["Cambridge C2", "Facultatea de Psihologie", "Modul psihopedagogic"],
        yearsExperience: 9,
        averageRating: 4.8,
        reviewCount: 23,
        reviewTags: ["gimnaziu", "anxietate examen", "bac"],
        studentTypes: ["gimnaziu", "liceu", "bac"],
        focusAreas: ["bac", "general", "examen"],
        format: "ambele",
        availableDays: ["miercuri", "joi", "vineri", "sambata"],
        price: 95
    },
    {
        id: 10,
        name: "Vlad Enache",
        language: "engleza",
        verified: true,
        credentialsScore: 8,
        credentials: ["CAE", "Facultatea de Stiinte ale Educatiei"],
        yearsExperience: 11,
        averageRating: 4.5,
        reviewCount: 14,
        reviewTags: ["liceu", "studenti", "gramatica"],
        studentTypes: ["liceu", "student"],
        focusAreas: ["gramatica", "examen", "general"],
        format: "fizic",
        availableDays: ["luni", "marti", "miercuri"],
        price: 85
    },
    {
        id: 11,
        name: "Bianca Toma",
        language: "engleza",
        verified: true,
        credentialsScore: 10,
        credentials: ["Cambridge C2", "CELTA", "Master in Applied Linguistics"],
        yearsExperience: 10,
        averageRating: 5.0,
        reviewCount: 42,
        reviewTags: ["cambridge", "examene", "adulti", "studenti"],
        studentTypes: ["liceu", "student", "adult"],
        focusAreas: ["examen", "conversatie", "general"],
        format: "online",
        availableDays: ["luni", "joi", "vineri", "duminica"],
        price: 130
    },
    {
        id: 12,
        name: "Sorina Ilie",
        language: "engleza",
        verified: false,
        credentialsScore: 7,
        credentials: ["Cambridge C1", "Facultatea de Litere"],
        yearsExperience: 20,
        averageRating: 4.3,
        reviewCount: 4,
        reviewTags: ["gimnaziu", "liceu"],
        studentTypes: ["gimnaziu", "liceu"],
        focusAreas: ["general", "gramatica"],
        format: "ambele",
        availableDays: ["marti", "joi", "vineri"],
        price: 100
    }
];