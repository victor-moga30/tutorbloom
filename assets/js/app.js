(function ($) {
    "use strict";

    function clearFieldState(field) {
        const $field = $(field);
        const $row = $field.closest(".form-row, td, p, div");

        $field.removeClass("input-error input-success error-field");
        $row.find(".error-message").first().remove();
    }

    function markFieldError(field, message) {
        const $field = $(field);
        const $row = $field.closest(".form-row, td, p, div");

        clearFieldState($field);
        $field.addClass("input-error");

        if ($row.length) {
            $("<div>", {
                class: "error-message",
                text: message
            }).appendTo($row);
        }
    }

    function markFieldSuccess(field) {
        $(field).removeClass("input-error").addClass("input-success");
    }

    function showFormMessage(form, message, isSuccess) {
        const $form = $(form);
        let $box = $form.find(".form-global-message").first();

        if (!$box.length) {
            $box = $("<div>", { class: "form-global-message" }).prependTo($form);
        }

        $box
            .text(message)
            .removeClass("success-message error-box")
            .addClass(isSuccess ? "success-message" : "error-box");
    }

    function clearFormMessage(form) {
        $(form).find(".form-global-message").remove();
    }

    function showToast(message) {
        let $toast = $("#app-toast");

        if (!$toast.length) {
            $toast = $("<div>", {
                id: "app-toast",
                class: "app-toast"
            }).appendTo("body");
        }

        $toast.text(message).addClass("show");

        setTimeout(function () {
            $toast.removeClass("show");
        }, 2500);
    }

    function setupSubmenuToggle() {
        const $submenuItems = $(".has-submenu");

        $submenuItems.each(function () {
            const $item = $(this);
            const $link = $item.children("a").first();

            $link.on("click", function (event) {
                if ($(window).width() > 700) {
                    return;
                }

                const isOpen = $item.hasClass("submenu-open");

                $submenuItems.not($item).removeClass("submenu-open");

                if (!isOpen) {
                    event.preventDefault();
                    $item.addClass("submenu-open");
                }
            });
        });

        $(document).on("click", function (event) {
            $submenuItems.each(function () {
                const $item = $(this);

                if (!$item.is(event.target) && $item.has(event.target).length === 0) {
                    $item.removeClass("submenu-open");
                }
            });
        });
    }

    function setupLiveValidationReset(form) {
        const $form = $(form);

        if (!$form.length) {
            return;
        }

        $form.on("input change", "input, select, textarea", function () {
            clearFieldState(this);
            clearFormMessage($form);
        });
    }

    window.appUtils = {
        clearFieldState: clearFieldState,
        markFieldError: markFieldError,
        markFieldSuccess: markFieldSuccess,
        showFormMessage: showFormMessage,
        clearFormMessage: clearFormMessage,
        showToast: showToast,
        setupSubmenuToggle: setupSubmenuToggle,
        setupLiveValidationReset: setupLiveValidationReset
    };

    $(function () {
        setupSubmenuToggle();
    });
})(jQuery);