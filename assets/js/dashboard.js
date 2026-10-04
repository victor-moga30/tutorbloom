$(function () {
    const $form = $("#dashboard-form");

    if (!$form.length) {
        return;
    }

    appUtils.setupLiveValidationReset($form);
    setupCollapsibleLists();
    updatePreview();

    $form.on("input change", updatePreview);

    $form.on("submit", function (event) {
        event.preventDefault();
        validateDashboardForm($form);
    });
});

function validateDashboardForm(form) {
    const $form = $(form);
    const $nameField = $form.find('input[name="nume"]');
    const $emailField = $form.find('input[name="email"]');
    const $phoneField = $form.find('input[name="telefon"]');
    const $cityField = $form.find('input[name="oras"]');
    const $descriptionField = $form.find('textarea[name="descriere"]');
    const $dayField = $form.find('input[name="zi"]');
    const $dateField = $form.find('input[name="dataSlot"]');
    const $startField = $form.find('input[name="oraStart"]');
    const $endField = $form.find('input[name="oraFinal"]');
    const $priceField = $form.find('input[name="pret"]');
    const $languagesField = $form.find('select[name="limbi"]');

    let isValid = true;

    const nameRegex = /^[A-Za-zĂÂÎȘȚăâîșț\s\-]{3,40}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^07\d{8}$/;
    const cityRegex = /^[A-Za-zĂÂÎȘȚăâîșț\s\-]{2,30}$/;
    const validDays = ["Luni", "Marti", "Miercuri", "Joi", "Vineri", "Sambata", "Duminica"];

    if (!nameRegex.test($.trim($nameField.val()))) {
        appUtils.markFieldError($nameField, "Nume invalid.");
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
        appUtils.markFieldError($phoneField, "Telefon invalid.");
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

    if ($.trim($descriptionField.val()).length < 20) {
        appUtils.markFieldError($descriptionField, "Descrierea trebuie sa aiba cel putin 20 de caractere.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($descriptionField);
    }

    if ($.inArray($.trim($dayField.val()), validDays) === -1) {
        appUtils.markFieldError($dayField, "Ziua trebuie sa fie una reala din saptamana.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($dayField);
    }

    if (!$dateField.val()) {
        appUtils.markFieldError($dateField, "Alege data slotului.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($dateField);
    }

    if (!$startField.val()) {
        appUtils.markFieldError($startField, "Alege ora de inceput.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($startField);
    }

    if (!$endField.val()) {
        appUtils.markFieldError($endField, "Alege ora de final.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($endField);
    }

    if ($startField.val() && $endField.val() && $startField.val() >= $endField.val()) {
        appUtils.markFieldError($endField, "Ora de final trebuie sa fie dupa ora de inceput.");
        isValid = false;
    }

    const price = Number($priceField.val());

    if (Number.isNaN(price) || price < 50 || price > 300) {
        appUtils.markFieldError($priceField, "Pretul trebuie sa fie intre 50 si 300.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($priceField);
    }

    if ($languagesField.find("option:selected").length === 0) {
        appUtils.markFieldError($languagesField, "Selecteaza cel putin o limba.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($languagesField);
    }

    if (!isValid) {
        appUtils.showFormMessage($form, "Formularul tutorului contine erori.", false);
        return;
    }

    appUtils.showFormMessage($form, "Datele tutorului au fost validate cu succes.", true);
    appUtils.showToast("Panoul a fost validat.");
}

function setupCollapsibleLists() {
    $(".info-lists li").each(function () {
        const $item = $(this);
        const $childList = $item.children("ul, ol").first();

        if (!$childList.length) {
            return;
        }

        const title = $.trim($item.contents().filter(function () {
            return this.nodeType === 3;
        }).first().text());

        $item.addClass("collapsible-parent");

        $item.contents().filter(function () {
            return this.nodeType === 3;
        }).first().replaceWith("");

        $("<button>", {
            type: "button",
            class: "list-toggle",
            text: title
        }).insertBefore($childList).on("click", function () {
            $item.toggleClass("open");
        });
    });
}

function updatePreview() {
    const $preview = $("#slot-preview");
    const $form = $("#dashboard-form");

    if (!$preview.length || !$form.length) {
        return;
    }

    const day = $form.find('input[name="zi"]').val();
    const date = $form.find('input[name="dataSlot"]').val();
    const start = $form.find('input[name="oraStart"]').val();
    const end = $form.find('input[name="oraFinal"]').val();
    const price = $form.find('input[name="pret"]').val();
    const type = $form.find('input[name="tipSlot"]:checked').val();

    $preview.html(`
        <h4>Preview slot nou</h4>
        <p><strong>Zi:</strong> ${day || "-"}</p>
        <p><strong>Data:</strong> ${date || "-"}</p>
        <p><strong>Interval:</strong> ${start || "-"} - ${end || "-"}</p>
        <p><strong>Pret:</strong> ${price || "-"} lei</p>
        <p><strong>Tip:</strong> ${type}</p>
    `);
}