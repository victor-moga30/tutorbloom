$(function () {
    const $form = $("#search-form");
    const $resultsBody = $("#results-body");
    const $verticalTable = $("#vertical-table");

    if (!$form.length || !$resultsBody.length || !$verticalTable.length) {
        return;
    }

    const $languageSelect = $form.find('select[name="limba"]');
    const $levelSelect = $form.find('select[name="nivel"]');
    const $keywordInput = $form.find('input[name="keyword"]');

    let filteredTutors = tutorData.slice();

    let horizontalSort = {
        key: "name",
        direction: "asc"
    };

    let verticalSort = {
        key: "name",
        direction: "asc"
    };

    appUtils.setupLiveValidationReset($form);

    updateLevelsByLanguage();

    renderResultsTable(sortTutors(filteredTutors, horizontalSort.key, horizontalSort.direction));
    renderVerticalTable(sortTutors(filteredTutors, verticalSort.key, verticalSort.direction));

    setupHeaderSorting();

    $languageSelect.on("change", function () {
        updateLevelsByLanguage();
        autoKeywordSuggestion();
        runFilters();
    });

    $levelSelect.on("change", runFilters);
    $keywordInput.on("input", runFilters);

    $form.on("input", function (event) {
        if ($(event.target).attr("name") !== "keyword") {
            runFilters();
        }
    });

    $form.on("change", runFilters);

    $form.on("submit", function (event) {
        event.preventDefault();
        validateSearchForm($form);
    });

    $form.on("reset", function () {
        setTimeout(function () {
            updateLevelsByLanguage();
            filteredTutors = tutorData.slice();

            horizontalSort = {
                key: "name",
                direction: "asc"
            };

            verticalSort = {
                key: "name",
                direction: "asc"
            };

            updateHorizontalHeaderClasses();
            renderResultsTable(sortTutors(filteredTutors, horizontalSort.key, horizontalSort.direction));
            renderVerticalTable(sortTutors(filteredTutors, verticalSort.key, verticalSort.direction));
        }, 0);
    });

    function updateLevelsByLanguage() {
        const selectedLanguageText = $languageSelect.find("option:selected").text();
        const levels = languageLevels[selectedLanguageText] || ["A1", "A2", "B1"];

        $levelSelect.empty();

        $.each(levels, function (_, level) {
            $("<option>", {
                value: level,
                text: level
            }).appendTo($levelSelect);
        });
    }

    function autoKeywordSuggestion() {
        const selectedLanguageText = $languageSelect.find("option:selected").text();

        const suggestions = {
            Engleza: "conversatie",
            Spaniola: "vocabular",
            Franceza: "gramatica",
            Germana: "incepatori"
        };

        $keywordInput.val(suggestions[selectedLanguageText] || "");
    }

    function runFilters() {
        const selectedLanguageText = $languageSelect.find("option:selected").text();
        const selectedLevel = $levelSelect.val();
        const priceMax = Number($form.find('input[name="pretMax"]').val());
        const keyword = $.trim($keywordInput.val()).toLowerCase();
        const lessonType = $form.find('input[name="tipLectie"]:checked').val();
        const wantsOnline = $form.find('input[name="online"]').is(":checked");
        const wantsPhysical = $form.find('input[name="fizic"]').is(":checked");
        const verifiedOnly = $form.find('input[name="verificat"]').is(":checked");

        filteredTutors = $.grep(tutorData, function (tutor) {
            const matchesLanguage = $.inArray(selectedLanguageText, tutor.languages) !== -1;
            const matchesLevel = $.inArray(selectedLevel, tutor.levels) !== -1;
            const matchesPrice = tutor.price <= priceMax;

            const matchesKeyword =
                keyword === "" ||
                tutor.description.toLowerCase().includes(keyword) ||
                tutor.name.toLowerCase().includes(keyword) ||
                tutor.experience.toLowerCase().includes(keyword);

            const matchesLessonType = $.inArray(
                lessonType === "individual" ? "Individuala" : "Grup",
                tutor.lessonTypes
            ) !== -1;

            const matchesTeaching =
                (!wantsOnline && !wantsPhysical) ||
                (wantsOnline && $.inArray("Online", tutor.teaching) !== -1) ||
                (wantsPhysical && $.inArray("Fizic", tutor.teaching) !== -1);

            const matchesVerified = !verifiedOnly || tutor.rating >= 4.7;

            return matchesLanguage &&
                matchesLevel &&
                matchesPrice &&
                matchesKeyword &&
                matchesLessonType &&
                matchesTeaching &&
                matchesVerified;
        });

        renderResultsTable(sortTutors(filteredTutors, horizontalSort.key, horizontalSort.direction));
        renderVerticalTable(sortTutors(filteredTutors, verticalSort.key, verticalSort.direction));
    }

    function setupHeaderSorting() {
        $(".sortable").on("click", function () {
            const $header = $(this);
            const key = $header.data("sort");

            if (horizontalSort.key === key) {
                horizontalSort.direction = horizontalSort.direction === "asc" ? "desc" : "asc";
            } else {
                horizontalSort.key = key;
                horizontalSort.direction = "asc";
            }

            updateHorizontalHeaderClasses();

            const sortedData = sortTutors(filteredTutors, horizontalSort.key, horizontalSort.direction);
            renderResultsTable(sortedData);
        });
    }

    function updateHorizontalHeaderClasses() {
        $(".sortable").each(function () {
            const $header = $(this);

            $header.removeClass("sorted-asc sorted-desc");

            if ($header.data("sort") === horizontalSort.key) {
                $header.addClass(horizontalSort.direction === "asc" ? "sorted-asc" : "sorted-desc");
            }
        });
    }

    function renderResultsTable(data) {
        $resultsBody.empty();

        if (data.length === 0) {
            $resultsBody.html(`
                <tr>
                    <td colspan="7">Nu exista tutori care sa corespunda filtrelor selectate.</td>
                </tr>
            `);
            return;
        }

        $.each(data, function (_, tutor) {
            const $row = $("<tr>").html(`
                <td data-label="Poza">
                    <div class="fake-avatar">${tutor.name.charAt(0)}</div>
                </td>
                <td data-label="Nume">
                    <strong>${tutor.name}</strong><br>
                    <span>${tutor.experience}</span>
                </td>
                <td data-label="Limba">${tutor.languages.join(", ")}</td>
                <td data-label="Nivel">${tutor.levels.join(", ")}</td>
                <td data-label="Pret">${tutor.price} lei</td>
                <td data-label="Sloturi disponibile">${formatSlots(tutor.slots)}</td>
                <td data-label="Actiuni">
                    <a href="profile.html">Profil</a><br>
                    <a href="book.html">Rezerva</a>
                </td>
            `);

            $resultsBody.append($row);
        });
    }

    function renderVerticalTable(data) {
        const fields = ["Nume", "Limbi", "Niveluri", "Pret", "Oras", "Rating"];

        const $tbody = $("<tbody>");

        $.each(fields, function (_, field) {
            const $row = $("<tr>");
            let sortClass = "";
            const fieldKey = mapVerticalFieldToKey(field);

            if (verticalSort.key === fieldKey) {
                sortClass = verticalSort.direction === "asc" ? "sorted-asc" : "sorted-desc";
            }

            $("<th>")
                .addClass("vertical-sort-header " + sortClass)
                .text(field)
                .appendTo($row);

            $.each(data, function (_, tutor) {
                let value = "";

                if (field === "Nume") value = tutor.name;
                if (field === "Limbi") value = tutor.languages.join(", ");
                if (field === "Niveluri") value = tutor.levels.join(", ");
                if (field === "Pret") value = tutor.price + " lei";
                if (field === "Oras") value = tutor.city;
                if (field === "Rating") value = tutor.rating;

                $("<td>").html(value).appendTo($row);
            });

            $tbody.append($row);
        });

        $verticalTable.empty().append($tbody);

        $verticalTable.find(".vertical-sort-header").on("click", function () {
            const key = mapVerticalFieldToKey($(this).text());

            if (verticalSort.key === key) {
                verticalSort.direction = verticalSort.direction === "asc" ? "desc" : "asc";
            } else {
                verticalSort.key = key;
                verticalSort.direction = "asc";
            }

            const sortedData = sortTutors(filteredTutors, verticalSort.key, verticalSort.direction);
            renderVerticalTable(sortedData);
        });
    }

    function mapVerticalFieldToKey(field) {
        if (field === "Nume") return "name";
        if (field === "Limbi") return "languages";
        if (field === "Niveluri") return "levels";
        if (field === "Pret") return "price";
        if (field === "Oras") return "city";
        if (field === "Rating") return "rating";
        return "name";
    }
});

$(function () {
    const params = new URLSearchParams(window.location.search);
    const smartMatch = params.get("smart_match");

    const savedResults = localStorage.getItem("perfectTutorMatchResults");

    if (smartMatch === "1" && savedResults) {
        const data = JSON.parse(savedResults);
        renderSmartMatchResults(data.matches);
    }
});

function renderSmartMatchResults(matches) {
    const $section = $("#smart-match-results");
    const $list = $("#smart-match-list");

    if (!$section.length || !$list.length || !matches || matches.length === 0) {
        return;
    }

    $list.empty();

    $.each(matches, function (index, tutor) {
        const $card = $("<div>").addClass("smart-match-card");

        if (index === 0) {
            $card.addClass("best-match");
        }

        const reasonsHtml = tutor.reasons && tutor.reasons.length > 0
            ? `<ul class="match-reasons">${$.map(tutor.reasons, function (reason) {
                return `<li>${reason}</li>`;
            }).join("")}</ul>`
            : "";

        $card.html(`
            ${index === 0 ? `<div class="match-badge">Best Match</div>` : ""}
            <h3>${tutor.name}</h3>
            <p><strong>Preț:</strong> ${tutor.price} lei</p>
            <p><strong>Format:</strong> ${tutor.format}</p>
            <p><strong>Rating:</strong> ${tutor.averageRating} (${tutor.reviewCount} review-uri)</p>
            <p><strong>Experiență:</strong> ${tutor.yearsExperience} ani</p>

            <div class="match-score">
                <p><strong>Compatibilitate:</strong> ${tutor.matchScore}%</p>
                <div class="match-score-bar">
                    <div class="match-score-fill" style="width: ${tutor.matchScore}%;"></div>
                </div>
            </div>

            ${reasonsHtml}
        `);

        $list.append($card);
    });

    $section.prop("hidden", false);

    setTimeout(function () {
        $section[0].scrollIntoView({
            behavior: "smooth",
            block: "start"
        });
    }, 150);
}

function sortTutors(data, key, direction) {
    const sorted = data.slice().sort(function (a, b) {
        let valueA = a[key];
        let valueB = b[key];

        if ($.isArray(valueA)) valueA = valueA.join(", ");
        if ($.isArray(valueB)) valueB = valueB.join(", ");

        if (typeof valueA === "string") valueA = valueA.toLowerCase();
        if (typeof valueB === "string") valueB = valueB.toLowerCase();

        if (valueA < valueB) return direction === "asc" ? -1 : 1;
        if (valueA > valueB) return direction === "asc" ? 1 : -1;
        return 0;
    });

    return sorted;
}

function formatSlots(slots) {
    return $.map(slots, function (slot) {
        return `${slot.day} ${slot.time} (${slot.place})`;
    }).join("<br>");
}

function validateSearchForm(form) {
    const $form = $(form);
    const $priceField = $form.find('input[name="pretMax"]');
    const $keywordField = $form.find('input[name="keyword"]');
    const $dateField = $form.find('input[name="dataDorita"]');
    const $timeField = $form.find('input[name="oraDorita"]');

    let isValid = true;

    const price = Number($priceField.val());

    if (Number.isNaN(price) || price < 50 || price > 300) {
        appUtils.markFieldError($priceField, "Pretul trebuie sa fie intre 50 si 300.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($priceField);
    }

    if ($.trim($keywordField.val()).length > 0 && $.trim($keywordField.val()).length < 3) {
        appUtils.markFieldError($keywordField, "Cuvantul-cheie trebuie sa aiba cel putin 3 caractere.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($keywordField);
    }

    if (!$dateField.val()) {
        appUtils.markFieldError($dateField, "Alege o data.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($dateField);
    }

    if (!$timeField.val()) {
        appUtils.markFieldError($timeField, "Alege o ora.");
        isValid = false;
    } else {
        appUtils.markFieldSuccess($timeField);
    }

    if (!isValid) {
        appUtils.showFormMessage($form, "Formularul de cautare contine erori.", false);
        return;
    }

    appUtils.showFormMessage($form, "Filtrele au fost validate cu succes.", true);
    appUtils.showToast("Cautarea a fost validata.");
}