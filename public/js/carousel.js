document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll(".carousel-slide");
    const prevButton = document.querySelector(".carousel-arrow.prev");
    const nextButton = document.querySelector(".carousel-arrow.next");
    let currentIndex = 0;
    const totalSlides = slides.length;
    let interval;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.toggle("active", i === index);
        });
        const offset = -index * 100;
        document.querySelector(".carousel-slides").style.transform = `translateX(${offset}%)`;
    }

    function nextSlide() {
        currentIndex = (currentIndex + 1) % totalSlides;
        showSlide(currentIndex);
    }

    function prevSlide() {
        currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
        showSlide(currentIndex);
    }

    nextButton.addEventListener("click", () => {
        clearInterval(interval);
        nextSlide();
        startAutoSlide();
    });

    prevButton.addEventListener("click", () => {
        clearInterval(interval);
        prevSlide();
        startAutoSlide();
    });

    // Auto-slide
    function startAutoSlide() {
        interval = setInterval(nextSlide, 5000);
    }

    startAutoSlide();
    showSlide(currentIndex);
});
