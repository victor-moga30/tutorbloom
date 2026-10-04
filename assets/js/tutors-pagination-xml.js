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

    function getText(parent, tagName) {
        const elements = parent.getElementsByTagName(tagName);

        if (elements.length === 0) {
            return "";
        }

        return elements[0].textContent;
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

        return "../student/ajax_tutors_xml.php?" + params.toString();
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

    function renderTutors(tutorNodes) {
        tableBody.innerHTML = "";

        if (tutorNodes.length === 0) {
            renderEmptyRow("Nu exista tutori pentru filtrele selectate.");
            return;
        }

        Array.prototype.forEach.call(tutorNodes, function (tutorNode) {
            const id = getText(tutorNode, "id");
            const fullName = getText(tutorNode, "full_name");
            const subjects = getText(tutorNode, "subjects");
            const experienceYears = getText(tutorNode, "experience_years");
            const pricePerHour = getText(tutorNode, "price_per_hour");
            const matchScore = getText(tutorNode, "match_score");
            const profilePhoto = getText(tutorNode, "profile_photo");

            const row = document.createElement("tr");

            row.innerHTML =
                "<td data-label=\"Poza\">" +
                "<img src=\"../" + escapeHtml(profilePhoto) + "\" width=\"70\" alt=\"Tutor\">" +
                "</td>" +
                "<td data-label=\"Nume\">" + escapeHtml(fullName) + "</td>" +
                "<td data-label=\"Materii\">" + escapeHtml(subjects) + "</td>" +
                "<td data-label=\"Experienta\">" + escapeHtml(experienceYears) + " ani</td>" +
                "<td data-label=\"Pret\">" + escapeHtml(pricePerHour) + " lei</td>" +
                "<td data-label=\"Match\">" + escapeHtml(matchScore) + "%</td>" +
                "<td data-label=\"Actiuni\">" +
                "<a href=\"../student/tutor_profile.php?id=" + encodeURIComponent(id) + "\">Profil</a>" +
                "</td>";

            tableBody.appendChild(row);
        });
    }

    function loadTutors() {
        clearMessage();
        infoBox.textContent = "Se incarca datele...";

        const xhr = new XMLHttpRequest();

        xhr.open("GET", buildUrl(), true);
        xhr.setRequestHeader("Accept", "application/xml");

        xhr.onload = function () {
            if (xhr.status < 200 || xhr.status >= 300) {
                showMessage("A aparut o eroare la server. Incearca din nou.");
                infoBox.textContent = "";
                renderEmptyRow("Datele nu au putut fi incarcate.");
                return;
            }

            const xml = xhr.responseXML;

            if (!xml || !xml.documentElement) {
                showMessage("Raspunsul primit de la server nu este XML valid.");
                infoBox.textContent = "";
                renderEmptyRow("Datele nu au putut fi interpretate.");
                return;
            }

            const root = xml.documentElement;

            if (root.getAttribute("success") !== "true") {
                const message = getText(root, "message") || "Nu s-au putut incarca datele.";
                showMessage(message);
                infoBox.textContent = "";
                renderEmptyRow("Datele nu au putut fi incarcate.");
                return;
            }

            total = Number(root.getAttribute("total"));
            const tutorNodes = root.getElementsByTagName("tutor");

            renderTutors(tutorNodes);

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