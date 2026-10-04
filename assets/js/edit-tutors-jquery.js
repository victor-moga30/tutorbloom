(function ($) {
    "use strict";

    const $form = $("#edit-tutor-form");

    if (!$form.length) {
        return;
    }

    const $tutorSelect = $("#tutor-select");
    const $saveButton = $("#save-button");
    const $errorBox = $("#ajax-message");
    const $successBox = $("#success-message");

    let isDirty = false;
    let currentTutorId = $tutorSelect.val();

    function showError(message) {
        $errorBox.text(message).prop("hidden", false);
        $successBox.prop("hidden", true);
    }

    function showSuccess(message) {
        $successBox.text(message).prop("hidden", false);
        $errorBox.prop("hidden", true);
    }

    function clearMessages() {
        $errorBox.text("").prop("hidden", true);
        $successBox.text("").prop("hidden", true);
    }

    function setDirty(value) {
        isDirty = value;
        $saveButton.prop("disabled", !isDirty);
    }

    function setFormDisabled(value) {
        $form.find("input, textarea, select, button").each(function () {
            if (this.type !== "hidden") {
                $(this).prop("disabled", value);
            }
        });
    }

    function updateSelectOption(tutor) {
        $tutorSelect.find("option[value='" + tutor.id + "']")
            .text("ID " + tutor.id + " - " + tutor.full_name + " - " + tutor.status);
    }

    function fillForm(tutor) {
        $("#full-name").val(tutor.full_name);
        $("#email").val(tutor.email);
        $("#description").val(tutor.description || "");
        $("#experience-years").val(tutor.experience_years);
        $("#price-per-hour").val(tutor.price_per_hour);
        $("#lesson-type").val(tutor.lesson_type);
        $("#location-type").val(tutor.location_type);
        $("#status").val(tutor.status);

        updateSelectOption(tutor);
        setDirty(false);
    }

    function loadTutor(id) {
        clearMessages();
        setFormDisabled(true);

        $.ajax({
            url: "../admin/ajax_get_tutor.php",
            method: "GET",
            dataType: "json",
            data: {
                id: id
            },
            success: function (data) {
                setFormDisabled(false);

                if (!data.success) {
                    showError(data.message || "Nu s-au putut incarca datele.");
                    return;
                }

                fillForm(data.tutor);
                currentTutorId = String(id);
                $tutorSelect.val(currentTutorId);
            },
            error: function () {
                setFormDisabled(false);
                showError("Nu se poate comunica cu serverul. Verifica conexiunea la Internet.");
            }
        });
    }

    function saveTutor(callback) {
        clearMessages();
        $saveButton.prop("disabled", true);

        $.ajax({
            url: "../admin/ajax_update_tutor.php",
            method: "POST",
            dataType: "json",
            data: $form.serialize(),
            success: function (data) {
                if (!data.success) {
                    showError(data.message || "Modificarile nu au putut fi salvate.");
                    $saveButton.prop("disabled", false);
                    return;
                }

                if (data.tutor) {
                    updateSelectOption(data.tutor);
                }

                setDirty(false);
                showSuccess(data.message || "Modificarile au fost salvate.");

                if (typeof callback === "function") {
                    callback();
                }
            },
            error: function (xhr) {
                let message = "Nu se poate comunica cu serverul. Verifica conexiunea la Internet.";

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                showError(message);
                $saveButton.prop("disabled", false);
            }
        });
    }

    $form.find("input, textarea, select").not("#tutor-select").not("[type='hidden']").on("input change", function () {
        setDirty(true);
    });

    $saveButton.on("click", function () {
        saveTutor();
    });

    $tutorSelect.on("change", function () {
        const newTutorId = $tutorSelect.val();

        if (!isDirty) {
            loadTutor(newTutorId);
            return;
        }

        const wantsSave = window.confirm(
            "Exista modificari nesalvate. Vrei sa le salvezi inainte sa continui?"
        );

        if (wantsSave) {
            $tutorSelect.val(currentTutorId);

            saveTutor(function () {
                loadTutor(newTutorId);
            });
        } else {
            setDirty(false);
            loadTutor(newTutorId);
        }
    });

    loadTutor(currentTutorId);
})(jQuery);