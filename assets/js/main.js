/**
 * Основной JavaScript файл
 * Слайдер, валидация форм, общие функции
 */

// Слайдер изображений
let currentSlide = 0;
const slides = document.querySelectorAll('.slider-slide');
const totalSlides = slides.length;

// Создаём точки навигации для слайдера
function createSliderDots() {
    const dotsContainer = document.getElementById('sliderDots');
    if(!dotsContainer) return;
    
    dotsContainer.innerHTML = '';
    for(let i = 0; i < totalSlides; i++) {
        const dot = document.createElement('div');
        dot.className = 'slider-dot' + (i === 0 ? ' active' : '');
        dot.onclick = () => goToSlide(i);
        dotsContainer.appendChild(dot);
    }
}

// Переход к конкретному слайду
function goToSlide(index) {
    currentSlide = index;
    if(currentSlide < 0) {
        currentSlide = totalSlides - 1;
    } else if(currentSlide >= totalSlides) {
        currentSlide = 0;
    }
    
    const wrapper = document.getElementById('sliderWrapper');
    if(wrapper) {
        wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
    }
    
    // Обновляем активную точку
    const dots = document.querySelectorAll('.slider-dot');
    dots.forEach((dot, i) => {
        if(i === currentSlide) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

// Смена слайда (вперёд/назад)
function changeSlide(direction) {
    goToSlide(currentSlide + direction);
}

// Автоматическая смена слайдов каждые 3 секунды
let sliderInterval;
function startSlider() {
    sliderInterval = setInterval(() => {
        changeSlide(1);
    }, 3000);
}

function stopSlider() {
    if(sliderInterval) {
        clearInterval(sliderInterval);
    }
}

// Инициализация слайдера при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    if(document.getElementById('sliderWrapper')) {
        createSliderDots();
        startSlider();
        
        // Останавливаем автопрокрутку при наведении
        const sliderContainer = document.querySelector('.slider-container');
        if(sliderContainer) {
            sliderContainer.addEventListener('mouseenter', stopSlider);
            sliderContainer.addEventListener('mouseleave', startSlider);
        }
    }
});

// Валидация телефона в формате 8(XXX)XXX-XX-XX
function formatPhone(input) {
    let value = input.value.replace(/\D/g, '');
    
    if(value.length > 0) {
        if(value[0] !== '8') {
            value = '8' + value;
        }
        
        if(value.length > 1) {
            value = value.substring(0, 1) + '(' + value.substring(1);
        }
        if(value.length > 5) {
            value = value.substring(0, 5) + ')' + value.substring(5);
        }
        if(value.length > 9) {
            value = value.substring(0, 9) + '-' + value.substring(9);
        }
        if(value.length > 12) {
            value = value.substring(0, 12) + '-' + value.substring(12);
        }
        if(value.length > 15) {
            value = value.substring(0, 15);
        }
    }
    
    input.value = value;
}

// Валидация даты в формате ДД.ММ.ГГГГ
function formatDate(input) {
    let value = input.value.replace(/\D/g, '');
    
    if(value.length > 2) {
        value = value.substring(0, 2) + '.' + value.substring(2);
    }
    if(value.length > 5) {
        value = value.substring(0, 5) + '.' + value.substring(5);
    }
    if(value.length > 10) {
        value = value.substring(0, 10);
    }
    
    input.value = value;
}

// Показ/скрытие сообщений
function showMessage(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Автоматически скрываем через 5 секунд
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

