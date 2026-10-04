window.APP_BASE = "/tutorbloom";

window.faqData = [
    {
        title: "Cum aleg tutorul potrivit?",
        items: [
            "Verifică materia, nivelul și stilul de predare.",
            "Compară prețul și disponibilitatea.",
            "Citește descrierea înainte de rezervare."
        ]
    },
    {
        title: "Cum funcționează rezervarea?",
        items: [
            "Alegi tutorul.",
            "Selectezi un interval disponibil.",
            "Trimiți cererea către tutor.",
            "Tutorul poate accepta sau respinge rezervarea."
        ]
    },
    {
        title: "Ce este smart matching?",
        items: [
            "Smart matching-ul calculează potrivirea în PHP.",
            "Sunt luate în calcul materia, nivelul, bugetul, tipul lecției și experiența tutorului.",
            "Rezultatele sunt afișate din baza de date, nu din localStorage."
        ]
    }
];

window.carouselSlides = [
    {
        title: "Găsește tutorul potrivit",
        text: "Compară rapid tutorii disponibili și alege varianta ideală pentru nivelul și obiectivele tale.",
        link: window.APP_BASE + "/student/search_tutors.php",
        buttonText: "Vezi tutorii",
        image: window.APP_BASE + "/assets/images/slide1.png"
    },
    {
        title: "Potrivire inteligentă",
        text: "Completează formularul de potrivire și primește recomandări calculate în PHP.",
        link: window.APP_BASE + "/student/perfect_match.php",
        buttonText: "Fă quiz-ul",
        image: window.APP_BASE + "/assets/images/slide2.png"
    },
    {
        title: "Rezervă rapid",
        text: "Alege un interval disponibil și trimite o cerere de rezervare către tutor.",
        link: window.APP_BASE + "/student/search_tutors.php",
        buttonText: "Rezervă",
        image: window.APP_BASE + "/assets/images/slide3.png"
    },
    {
        title: "Vezi profiluri publice",
        text: "Consultă experiența, materiile, prețul și disponibilitatea înainte să alegi.",
        link: window.APP_BASE + "/student/search_tutors.php",
        buttonText: "Vezi profiluri",
        image: window.APP_BASE + "/assets/images/slide4.png"
    }
];