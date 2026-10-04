$(function () {
    initStaticCarousel();
    initFaq();
});

function initStaticCarousel() {
    const $slides = $(".carousel-slide");
    const $dots = $(".carousel-dot");
    const $prev = $("#carousel-prev");
    const $next = $("#carousel-next");

    if (!$slides.length) {
        return;
    }

    let currentIndex = 0;
    let intervalId = null;

    function showSlide(index) {
        currentIndex = index;

        $slides.removeClass("active");
        $slides.eq(currentIndex).addClass("active");

        $dots.removeClass("active");
        $dots.eq(currentIndex).addClass("active");
    }

    function nextSlide() {
        showSlide((currentIndex + 1) % $slides.length);
    }

    function prevSlide() {
        showSlide((currentIndex - 1 + $slides.length) % $slides.length);
    }

    function startAuto() {
        clearInterval(intervalId);
        intervalId = setInterval(nextSlide, 4000);
    }

    $next.on("click", function () {
        nextSlide();
        startAuto();
    });

    $prev.on("click", function () {
        prevSlide();
        startAuto();
    });

    $dots.on("click", function () {
        const index = Number($(this).data("slide"));
        showSlide(index);
        startAuto();
    });

    $(".carousel-container").on("mouseenter", function () {
        clearInterval(intervalId);
    });

    $(".carousel-container").on("mouseleave", function () {
        startAuto();
    });

    showSlide(0);
    startAuto();
}

function initFaq() {
    $(".collapsible-trigger").on("click", function () {
        $(this).closest(".collapsible-item").toggleClass("open");
    });
}