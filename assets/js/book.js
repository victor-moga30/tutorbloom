$(function () {
    const $form = $("#booking-form");

    if (!$form.length) {
        return;
    }

    const $tutorSelect = $form.find('select[name="tutor"]');
    const $languageSelect = $form.find('select[name="limba"]');
    const $dateInput = $form.find('input[name="dataSedinta"]');
    const $timeInput = $form.find('input[name="oraSedinta"]');
    const $summaryBox = $("#booking-summary");

    appUtils.setupLiveValidationReset($form);

    updateLanguages();
    updateTimeAccordingToTutor();
    generateReservationId();
    updateSummary();

    $tutorSelect.on("change", function () {
        updateLanguages();
        updateTimeAccordingToTutor();
        generateReservationId();
        updateSummary();
    });

    $languageSelect.add($dateInput).add($timeInput).on("change", updateSummary);
    $form.on("input change", updateSummary);

    $form.on("submit", function (event) {
        event.preventDefault();
        validateBookingForm($form);
    });

    $form.on("reset", function () {
        setTimeout(function () {
            generateReservationId();
            updateLanguages();
            updateTimeAccordingToTutor();
            updateSummary();
            appUtils.clearFormMessage($form);
        }, 0);
    });

    function updateLanguages() {
        const tutorKey = $tutorSelect.val();
        const languages = tutorLanguageMap[tutorKey] || ["Engleza"];

        $languageSelect.empty();

        $.each(languages, function (_, language) {
            $("<option>", {
                value: language.toLowerCase(),
                text: language
            }).appendTo($languageSelect);
        });
    }

    function updateTimeAccordingToTutor() {
        const tutorKey = $tutorSelect.val();
        const times = tutorTimeMap[tutorKey] || ["18:00"];

        if ($.inArray($timeInput.val(), times) === -1) {
            $timeInput.val(times[0]);
        }
    }

    function generateReservationId() {
        const $idField = $form.find('input[name="idRezervare"]');
        const tutorCode = String($tutorSelect.val()).toUpperCase().slice(0, 3);
        const today = new Date();
        const code = String(today.getDate()).padStart(2, "0") +
            String(today.getMonth() + 1).padStart(2, "0") +
            today.getFullYear();

        $idField.val("REZ-" + tutorCode + "-" + code);
    }

    function updateSummary() {
        if (!$summaryBox.length) {
            return;
        }

        const name = $.trim($form.find('input[name="numeStudent"]').val()) || "-";
        const email = $.trim($form.find('input[name="emailStudent"]').val()) || "-";
        const tutorText = $tutorSelect.find("option:selected").text();
        const languageText = $languageSelect.find("option:selected").text();
        const level = $form.find('select[name="nivel"]').val();
        const date = $dateInput.val() || "-";
        const time = $timeInput.val() || "-";
        const type = $form.find('input[name="tipSedinta"]:checked').val() || "-";

        $summaryBox.html(`
            <h4>Sumar rezervare</h4>
            <p><strong>Student:</strong> ${name}</p>
            <p><strong>Email:</strong> ${email}</p>
            <p><strong>Tutor:</strong> ${tutorText}</p>
            <p><strong>Limba:</strong> ${languageText}</p>
            <p><strong>Nivel:</strong> ${level}</p>
            <p><strong>Data:</strong> ${date}</p>
            <p><strong>Ora:</strong> ${time}</p>
            <p><strong>Tip:</strong> ${type}</p>
        `);
    }
});

function validateBookingForm(form) {
    const $form = $(form);
    const $nameField = $form.find('input[name="numeStudent"]');
    const $emailField = $form.find('input[name="emailStudent"]');
    const $phoneField = $form.find('input[name="telefonStudent"]');
    const $cityField = $form.find('input[name="orasStudent"]');
    const $dateField = $form.find('input[name="dataSedinta"]');
    const $timeField = $form.find('input[name="oraSedinta"]');
    const $messageField = $form.find('textarea[name="mesaj"]');
    const $termsField = $form.find('input[name="termeni"]');

    let isValid = true;

    const nameRegex = /^[A-Za-zĂÂÎȘȚăâîșț\s\-]{3,40}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^07\d{8}$/;
    const cityRegex = /^[A-Za-zĂÂÎȘȚăâîșț\s\-]{2,30}$/;

    if (!nameRegex.test($.trim($nameField.val()))) {
        appUtils.markFieldError($nameField, "Numele trebuie sa aiba intre 3 si 40 de caractere si sa contina doar litere.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($nameField);
    }

    if (!emailRegex.test($.trim($emailField.val()))) {
        appUtils.markFieldError($emailField, "Email invalid.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($emailField);
    }

    if (!phoneRegex.test($.trim($phoneField.val()))) {
        appUtils.markFieldError($phoneField, "Telefonul trebuie sa inceapa cu 07 si sa aiba 10 cifre.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($phoneField);
    }

    if (!cityRegex.test($.trim($cityField.val()))) {
        appUtils.markFieldError($cityField, "Oras invalid.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($cityField);
    }

    if (!$dateField.val()) {
        appUtils.markFieldError($dateField, "Alege o data.");
        isValid = false;
    } else {
        const selectedDate = new Date($dateField.val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (selectedDate < today) {
            appUtils.markFieldError($dateField, "Data nu poate fi in trecut.");
            isValid = false;
        } else {
            appUtils.markFieldSuccess($dateField);
        }
    }

    if (!$timeField.val()) {
        appUtils.markFieldError($timeField, "Alege o ora.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($timeField);
    }

    if ($.trim($messageField.val()).length < 10) {
        appUtils.markFieldError($messageField, "Mesajul trebuie sa aiba cel putin 10 caractere.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($messageField);
    }

    if (!$termsField.is(":checked")) {
        appUtils.markFieldError($termsField, "Trebuie sa confirmi datele introduse.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($termsField);
    }

    const $objectives = $form.find('input[name="obiectiv1"], input[name="obiectiv2"], input[name="obiectiv3"]');

    if ($objectives.filter(":checked").length === 0) {
        appUtils.markFieldError($objectives.first(), "Selecteaza cel putin un obiectiv.");
        isValid = false;
    }

    if (!isValid) {
        appUtils.showFormMessage($form, "Formularul contine erori. Verifica campurile marcate.", false);
        return;
    }

    appUtils.showFormMessage($form, "Rezervarea demonstrativa a fost validata cu succes.", true);
    appUtils.showToast("Rezervarea a fost validata.");
}