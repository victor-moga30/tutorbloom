(function ($) {
    "use strict";

    const limit = 3;
    let offset = 0;
    let total = 0;

    function showMessage(message) {
        $("#ajax-message").text(message).prop("hidden", false);
    }

    function clearMessage() {
        $("#ajax-message").text("").prop("hidden", true);
    }

    function buildUrl() {
        const params = new URLSearchParams(window.location.search);

        params.set("offset", String(offset));
        params.set("limit", String(limit));

        return "../student/ajax_tutors_json.php?" + params.toString();
    }

    function updateButtons() {
        $("#previous-button").prop("disabled", offset <= 0);
        $("#next-button").prop("disabled", offset + limit >= total);
    }

    function renderEmptyRow(message) {
        const $row = $("<tr>");
        const $cell = $("<td>", {
            colspan: 7,
            text: message
        });

        $row.append($cell);
        $("#tutors-body").empty().append($row);
    }

    function renderTutors(tutors) {
        const $body = $("#tutors-body");
        $body.empty();

        if (tutors.length === 0) {
            renderEmptyRow("Nu exista tutori pentru filtrele selectate.");
            return;
        }

        $.each(tutors, function (index, tutor) {
            const $row = $("<tr>");

            $("<td>", { "data-label": "Poza" })
                .append($("<img>", {
                    src: "../" + tutor.profile_photo,
                    width: 70,
                    alt: "Tutor"
                }))
                .appendTo($row);

            $("<td>", { "data-label": "Nume", text: tutor.full_name }).appendTo($row);
            $("<td>", { "data-label": "Materii", text: tutor.subjects }).appendTo($row);
            $("<td>", { "data-label": "Experienta", text: tutor.experience_years + " ani" }).appendTo($row);
            $("<td>", { "data-label": "Pret", text: tutor.price_per_hour + " lei" }).appendTo($row);
            $("<td>", { "data-label": "Match", text: tutor.match_score + "%" }).appendTo($row);

            $("<td>", { "data-label": "Actiuni" })
                .append($("<a>", {
                    href: "../student/tutor_profile.php?id=" + encodeURIComponent(tutor.id),
                    text: "Profil"
                }))
                .appendTo($row);

            $body.append($row);
        });
    }

    function loadTutors() {
        clearMessage();
        $("#ajax-info").text("Se incarca datele...");

        $.ajax({
            url: buildUrl(),
            method: "GET",
            dataType: "json",
            success: function (data) {
                if (!data.success) {
                    showMessage(data.message || "Nu s-au putut incarca datele.");
                    $("#ajax-info").text("");
                    renderEmptyRow("Datele nu au putut fi incarcate.");
                    return;
                }

                total = Number(data.total);
                renderTutors(data.tutors);

                if (total === 0) {
                    $("#ajax-info").text("0 rezultate.");
                } else {
                    const first = offset + 1;
                    const last = Math.min(offset + limit, total);
                    $("#ajax-info").text("Se afiseaza " + first + "-" + last + " din " + total + " rezultate.");
                }

                updateButtons();
            },
            error: function () {
                showMessage("Nu se poate comunica cu serverul. Verifica conexiunea la Internet.");
                $("#ajax-info").text("");
                renderEmptyRow("Eroare de conexiune.");
                total = 0;
                updateButtons();
            }
        });
    }

    $("#previous-button").on("click", function () {
        if (offset >= limit) {
            offset -= limit;
            loadTutors();
        }
    });

    $("#next-button").on("click", function () {
        if (offset + limit < total) {
            offset += limit;
            loadTutors();
        }
    });

    loadTutors();
})(jQuery);