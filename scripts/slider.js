class Slider {
    constructor(containerSelector) {
        this.container = document.querySelector(containerSelector);
        if (!this.container) return;
        
        this.slides = this.container.querySelectorAll('.slide');
        this.currentIndex = 0;
        this.interval = null;
        this.autoplayDelay = 3000;

        this.init();
    }

    init() {
        if (this.slides.length === 0) return;
        
        this.showSlide(0);
        this.startAutoplay();

        const prevBtn = document.querySelector('.slider-prev');
        const nextBtn = document.querySelector('.slider-next');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                this.prevSlide();
                this.resetAutoplay();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                this.nextSlide();
                this.resetAutoplay();
            });
        }
    }

    showSlide(index) {
        this.slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === index);
        });
        this.currentIndex = index;
    }

    nextSlide() {
        const newIndex = (this.currentIndex + 1) % this.slides.length;
        this.showSlide(newIndex);
    }

    prevSlide() {
        const newIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
        this.showSlide(newIndex);
    }

    startAutoplay() {
        this.interval = setInterval(() => {
            this.nextSlide();
        }, this.autoplayDelay);
    }

    stopAutoplay() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
    }

    resetAutoplay() {
        this.stopAutoplay();
        this.startAutoplay();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new Slider('.slider-container');
});


