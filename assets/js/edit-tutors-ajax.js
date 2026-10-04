(function () {
    "use strict";

    const form = document.getElementById("edit-tutor-form");

    if (!form) {
        return;
    }

    const tutorSelect = document.getElementById("tutor-select");
    const saveButton = document.getElementById("save-button");
    const errorBox = document.getElementById("ajax-message");
    const successBox = document.getElementById("success-message");

    let isDirty = false;
    let currentTutorId = tutorSelect.value;

    function showError(message) {
        errorBox.textContent = message;
        errorBox.hidden = false;
        successBox.hidden = true;
    }

    function showSuccess(message) {
        successBox.textContent = message;
        successBox.hidden = false;
        errorBox.hidden = true;
    }

    function clearMessages() {
        errorBox.textContent = "";
        successBox.textContent = "";
        errorBox.hidden = true;
        successBox.hidden = true;
    }

    function setDirty(value) {
        isDirty = value;
        saveButton.disabled = !isDirty;
    }

    function setFormDisabled(value) {
        const fields = form.querySelectorAll("input, textarea, select, button");

        fields.forEach(function (field) {
            if (field.type !== "hidden") {
                field.disabled = value;
            }
        });
    }

    function updateSelectOption(tutor) {
        const option = tutorSelect.querySelector("option[value='" + CSS.escape(String(tutor.id)) + "']");

        if (option) {
            option.textContent = "ID " + tutor.id + " - " + tutor.full_name + " - " + tutor.status;
        }
    }

    function fillForm(tutor) {
        document.getElementById("full-name").value = tutor.full_name;
        document.getElementById("email").value = tutor.email;
        document.getElementById("description").value = tutor.description || "";
        document.getElementById("experience-years").value = tutor.experience_years;
        document.getElementById("price-per-hour").value = tutor.price_per_hour;
        document.getElementById("lesson-type").value = tutor.lesson_type;
        document.getElementById("location-type").value = tutor.location_type;
        document.getElementById("status").value = tutor.status;

        updateSelectOption(tutor);
        setDirty(false);
    }

    function loadTutor(id) {
        clearMessages();
        setFormDisabled(true);

        const xhr = new XMLHttpRequest();
        xhr.open("GET", "../admin/ajax_get_tutor.php?id=" + encodeURIComponent(id), true);
        xhr.setRequestHeader("Accept", "application/json");

        xhr.onload = function () {
            setFormDisabled(false);

            if (xhr.status < 200 || xhr.status >= 300) {
                showError("Nu s-au putut incarca datele tutorului.");
                return;
            }

            let data;

            try {
                data = JSON.parse(xhr.responseText);
            } catch (exception) {
                showError("Raspuns invalid de la server.");
                return;
            }

            if (!data.success) {
                showError(data.message || "Nu s-au putut incarca datele.");
                return;
            }

            fillForm(data.tutor);
            currentTutorId = String(id);
            tutorSelect.value = currentTutorId;
        };

        xhr.onerror = function () {
            setFormDisabled(false);
            showError("Nu se poate comunica cu serverul. Verifica conexiunea la Internet.");
        };

        xhr.send();
    }

    function buildPostData() {
        const formData = new FormData(form);
        const params = new URLSearchParams();

        formData.forEach(function (value, key) {
            params.append(key, value);
        });

        return params.toString();
    }

    function saveTutor(callback) {
        clearMessages();
        saveButton.disabled = true;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../admin/ajax_update_tutor.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.setRequestHeader("Accept", "application/json");

        xhr.onload = function () {
            let data;

            try {
                data = JSON.parse(xhr.responseText);
            } catch (exception) {
                showError("Raspuns invalid de la server.");
                saveButton.disabled = false;
                return;
            }

            if (xhr.status < 200 || xhr.status >= 300 || !data.success) {
                showError(data.message || "Modificarile nu au putut fi salvate.");
                saveButton.disabled = false;
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
        };

        xhr.onerror = function () {
            showError("Nu se poate comunica cu serverul. Verifica conexiunea la Internet.");
            saveButton.disabled = false;
        };

        xhr.send(buildPostData());
    }

    form.querySelectorAll("input, textarea, select").forEach(function (field) {
        if (field.id !== "tutor-select" && field.type !== "hidden") {
            field.addEventListener("input", function () {
                setDirty(true);
            });

            field.addEventListener("change", function () {
                setDirty(true);
            });
        }
    });

    saveButton.addEventListener("click", function () {
        saveTutor();
    });

    tutorSelect.addEventListener("change", function () {
        const newTutorId = tutorSelect.value;

        if (!isDirty) {
            loadTutor(newTutorId);
            return;
        }

        const wantsSave = window.confirm(
            "Exista modificari nesalvate. Vrei sa le salvezi inainte sa continui?"
        );

        if (wantsSave) {
            tutorSelect.value = currentTutorId;

            saveTutor(function () {
                loadTutor(newTutorId);
            });
        } else {
            setDirty(false);
            loadTutor(newTutorId);
        }
    });

    loadTutor(currentTutorId);
})();