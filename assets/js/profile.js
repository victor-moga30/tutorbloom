$(function () {
    const $spotlight = $("#profile-spotlight");
    const $tags = $("#profile-tags");
    const $availability = $("#profile-availability-list");

    if (typeof tutorData === "undefined" || !tutorData.length) {
        console.log("tutorData nu exista sau este gol.");
        return;
    }

    const tutor = tutorData[0];

    if ($spotlight.length) {
        $spotlight.html(`
            <h3>${tutor.name}</h3>
            <p>${tutor.description}</p>
            <p><strong>Oras:</strong> ${tutor.city}</p>
            <p><strong>Rating:</strong> ${tutor.rating}</p>
        `);
    }

    if ($tags.length) {
        $tags.empty();

        $.each(tutor.languages, function (_, language) {
            $("<span>")
                .addClass("profile-tag")
                .text(language)
                .appendTo($tags);
        });
    }

    if ($availability.length) {
        $availability.empty();

        $.each(tutor.slots, function (_, slot) {
            $("<li>")
                .text(slot.day + " - " + slot.time + " - " + slot.place)
                .appendTo($availability);
        });
    }

    setupProfileCollapse();
});

function setupProfileCollapse() {
    $(".profile-collapsible").each(function () {
        const $section = $(this);
        const $btn = $section.find(".profile-toggle").first();

        if ($btn.length) {
            $btn.on("click", function () {
                $section.toggleClass("open");
            });
        }
    });
}