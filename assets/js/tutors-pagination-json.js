(function () {
    "use strict";

    const limit = 3;
    let offset = 0;
    let total = 0;

    const tableBody = document.getElementById("tutors-body");
    const previousButton = document.getElementById("previous-button");
    const nextButton = document.getElementById("next-button");
    const messageBox = document.getElementById("ajax-message");
    const infoBox = document.getElementById("ajax-info");

    function escapeHtml(value) {
        return String(value)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }

    function showMessage(message) {
        messageBox.textContent = message;
        messageBox.hidden = false;
    }

    function clearMessage() {
        messageBox.textContent = "";
        messageBox.hidden = true;
    }

    function buildUrl() {
        const params = new URLSearchParams(window.location.search);

        params.set("offset", String(offset));
        params.set("limit", String(limit));

        return "../student/ajax_tutors_json.php?" + params.toString();
    }

    function updateButtons() {
        previousButton.disabled = offset <= 0;
        nextButton.disabled = offset + limit >= total;
    }

    function renderEmptyRow(message) {
        tableBody.innerHTML = "";

        const row = document.createElement("tr");
        const cell = document.createElement("td");

        cell.colSpan = 7;
        cell.textContent = message;

        row.appendChild(cell);
        tableBody.appendChild(row);
    }

    function renderTutors(tutors) {
        tableBody.innerHTML = "";

        if (tutors.length === 0) {
            renderEmptyRow("Nu exista tutori pentru filtrele selectate.");
            return;
        }

        tutors.forEach(function (tutor) {
            const row = document.createElement("tr");

            row.innerHTML =
                "<td data-label=\"Poza\">" +
                "<img src=\"../" + escapeHtml(tutor.profile_photo) + "\" width=\"70\" alt=\"Tutor\">" +
                "</td>" +
                "<td data-label=\"Nume\">" + escapeHtml(tutor.full_name) + "</td>" +
                "<td data-label=\"Materii\">" + escapeHtml(tutor.subjects) + "</td>" +
                "<td data-label=\"Experienta\">" + escapeHtml(tutor.experience_years) + " ani</td>" +
                "<td data-label=\"Pret\">" + escapeHtml(tutor.price_per_hour) + " lei</td>" +
                "<td data-label=\"Match\">" + escapeHtml(tutor.match_score) + "%</td>" +
                "<td data-label=\"Actiuni\">" +
                "<a href=\"../student/tutor_profile.php?id=" + encodeURIComponent(tutor.id) + "\">Profil</a>" +
                "</td>";

            tableBody.appendChild(row);
        });
    }

    function loadTutors() {
        clearMessage();
        infoBox.textContent = "Se incarca datele...";

        const xhr = new XMLHttpRequest();

        xhr.open("GET", buildUrl(), true);
        xhr.setRequestHeader("Accept", "application/json");

        xhr.onload = function () {
            if (xhr.status < 200 || xhr.status >= 300) {
                showMessage("A aparut o eroare la server. Incearca din nou.");
                infoBox.textContent = "";
                renderEmptyRow("Datele nu au putut fi incarcate.");
                return;
            }

            let data;

            try {
                data = JSON.parse(xhr.responseText);
            } catch (exception) {
                showMessage("Raspunsul primit de la server nu este JSON valid.");
                infoBox.textContent = "";
                renderEmptyRow("Datele nu au putut fi interpretate.");
                return;
            }

            if (!data.success) {
                showMessage(data.message || "Nu s-au putut incarca datele.");
                infoBox.textContent = "";
                renderEmptyRow("Datele nu au putut fi incarcate.");
                return;
            }

            total = Number(data.total);
            renderTutors(data.tutors);

            if (total === 0) {
                infoBox.textContent = "0 rezultate.";
            } else {
                const first = offset + 1;
                const last = Math.min(offset + limit, total);
                infoBox.textContent = "Se afiseaza " + first + "-" + last + " din " + total + " rezultate.";
            }

            updateButtons();
        };

        xhr.onerror = function () {
            showMessage("Nu se poate comunica cu serverul. Verifica conexiunea la Internet.");
            infoBox.textContent = "";
            renderEmptyRow("Eroare de conexiune.");
            total = 0;
            updateButtons();
        };

        xhr.send();
    }

    previousButton.addEventListener("click", function () {
        if (offset >= limit) {
            offset -= limit;
            loadTutors();
        }
    });

    nextButton.addEventListener("click", function () {
        if (offset + limit < total) {
            offset += limit;
            loadTutors();
        }
    });

    loadTutors();
})();