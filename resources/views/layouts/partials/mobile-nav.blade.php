<!-- Нижняя мобильная навигация -->
<div class="mnav-container">
    <div class="mnav-wrapper">
        <div class="mnav-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="mnav-logo-img">
        </div>
        <div class="mnav-carousel" >
            <div class="mnav-items">
                <a href="{{ route('home') }}" class="mnav-item {{ request()->routeIs('home') ? 'mnav-active' : '' }}" data-index="0">
                    <div class="mnav-icon">
                        <i class="bi bi-house"></i>
                    </div>
                </a>
                @if (Auth::user()->role === 'client' || Auth::user()->role === 'admin')
                    <a href="{{ route('client.templates.categories') }}"
                        class="mnav-item {{ request()->routeIs('client.templates.*') ? 'mnav-active' : '' }}" data-index="1">
                        <div class="mnav-icon">
                            <i class="bi bi-plus"></i>
                        </div>
                    </a>
                    <a href="{{ route('user.templates') }}"
                        class="mnav-item {{ request()->routeIs('user.templates*') ? 'mnav-active' : '' }}" data-index="2">
                        <div class="mnav-icon">
                        <i class="bi bi-person"></i>
                        </div>
                    </a>
                    <!-- Добавляем кнопку сканирования QR кода (заменяем на обычную кнопку без data-bs-toggle) -->
                    <a href="#" class="mnav-item" data-index="3" id="qrScanButton">
                        <div class="mnav-icon">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                    </a>
                @endif
                @if (Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                        class="mnav-item {{ request()->routeIs('admin.dashboard') ? 'mnav-active' : '' }}" data-index="4">
                        <div class="mnav-icon">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div id="qrScanModal" class="custom-modal">
    <div class="custom-modal-content">
        <div class="custom-modal-body">
            <div id="scanner-container">
                <video id="qr-video" class="w-100"></video>
            </div>
        </div>
    </div>
</div>

<style>
/* Стили для сканера QR-кода */
#scanner-container {
    position: relative;
    min-height: 600px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}

#qr-video {
    width: 100%;
    height: auto;
    max-height: 100%;
}

/* Стили для самописного модального окна */
.custom-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 2000;
    overflow: auto;
    transition: all 0.3s ease;
    will-change: opacity;
    transform: translateZ(0);
}

.custom-modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.custom-modal-content {
    position: relative;
    margin: 10px;
    width: 90%;
    max-width: 500px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    animation: slideIn 0.3s;
    transform: translateZ(0);
    will-change: transform;
    backface-visibility: hidden;
}

@keyframes slideIn {
    from { transform: translateY(20px) translateZ(0); opacity: 0; }
    to { transform: translateY(0) translateZ(0); opacity: 1; }
}

.custom-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
}

.custom-modal-close {
    font-size: 24px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
}

.custom-modal-close:hover {
    color: #000;
}

.custom-modal-body {
    padding: 15px;
}

.custom-modal-footer {
    padding: 15px;
    border-top: 1px solid #dee2e6;
    text-align: right;
}

/* Улучшенные стили для alert внутри модального окна */
.alert {
    position: relative;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.25rem;
}

.alert-info {
    color: #0c5460;
    background-color: #d1ecf1;
    border-color: #bee5eb;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.alert-warning {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeeba;
}

/* Оптимизированные стили для мобильной навигации */
.mnav-container {
    position: fixed;
    bottom: 20px;
    left: 0;
    right: 0;
    height: 75px;
    z-index: 1030;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-top-left-radius: 25px;
    border-top-right-radius: 25px;
    display: none;
    transition: all 0.35s cubic-bezier(0.25, 0.1, 0.25, 1);
    transform: translateZ(0);
    will-change: transform;
    backface-visibility: hidden;
}

/* Состояние с градиентом (активное) */
.mnav-container.active-state {
    transform: translateZ(0);
}

/* Состояние без градиента (неактивное) */
.mnav-container.inactive-state {
    background-image: none;
    transform: translateZ(0);
}

@media (max-width: 767.98px) {
    .mnav-container {
        display: block;
    }
}

.mnav-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: visible;
    transform: translateZ(0);
    backface-visibility: hidden;
}

.mnav-logo {
    position: absolute;
    top: -4px;
    left: 50%;
    transform: translateX(-50%) translateZ(0);
    z-index: 1040;
    pointer-events: none;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    backface-visibility: hidden;
    will-change: transform;
}

.mnav-logo-img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    padding: 8px;
    transform: translateY(0) translateZ(0);
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    backface-visibility: hidden;
    will-change: transform;
}

.mnav-logo-img:hover {
    transform: translateY(-5px) scale(1.05) translateZ(0);
}

.mnav-carousel {
    width: 100%;
    height: 100%;
    overflow-x: auto;
    top: 10px;
    scrollbar-width: none;
    -ms-overflow-style: none;
    position: relative;
    -webkit-overflow-scrolling: touch;
    transform: translateZ(0);
}

.mnav-carousel::-webkit-scrollbar {
    display: none;
}

.mnav-items {
    display: flex;
    padding: 0 47%;
    min-width: max-content;
    justify-content: center;
    align-items: center;
    position: relative;
    height: 79px;
    transform: translateZ(0);
    backface-visibility: hidden;
    will-change: transform;
}

.mnav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 10px;
    padding: 12px 12px;
    text-decoration: none;
    color: #a0a0a0;
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    will-change: transform;
    transform: translateZ(0);
    backface-visibility: hidden;
}

.mnav-item.mnav-active::after {
    width: 25px;
    opacity: 1;
}

.mnav-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 16px;
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    background: transparent;
    transform: translateZ(0);
    will-change: transform;
    backface-visibility: hidden;
}

.mnav-icon i {
    font-size: 35px;
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    transform: translateZ(0);
    will-change: transform, color;
    backface-visibility: hidden;
}

/* Стили для центрального элемента */
.mnav-item.mnav-center .mnav-icon {
    transform: scale(1.3) translateZ(0);
}

.mnav-item.mnav-center .mnav-icon i {
    color: #0d6efd;
    transform: translateY(-2px) translateZ(0);
}

/* Стили для соседних с центром элементов */
.mnav-item.mnav-near .mnav-icon {
    transform: scale(1.1) translateZ(0);
}

.mnav-item.mnav-near .mnav-icon i {
    color: #5a6268;
}

/* Стили для дальних элементов */
.mnav-item.mnav-far .mnav-icon {
    transform: scale(0.85) translateZ(0);
}

/* Стили для очень дальних элементов */
.mnav-item.mnav-very-far .mnav-icon {
    transform: scale(0.7) translateZ(0);
    opacity: 0.7;
}

/* Активный элемент */
.mnav-item.mnav-active .mnav-icon {
    transform: translateZ(0);
}

.mnav-item.mnav-active .mnav-icon i {
    color: #0d6efd;
}

/* Эффекты при наведении */
.mnav-item:hover .mnav-icon {
    transform: translateY(-5px) translateZ(0);
}

.mnav-item.mnav-center:hover .mnav-icon {
    transform: scale(1.35) translateY(-5px) translateZ(0);
}

.mnav-item.mnav-near:hover .mnav-icon {
    transform: scale(1.15) translateY(-5px) translateZ(0);
}

.mnav-item.mnav-far:hover .mnav-icon {
    transform: scale(0.9) translateY(-5px) translateZ(0);
}

/* Новые стили для эффекта неактивности */
.inactive-state .mnav-item:not(.mnav-center) .mnav-icon {
    opacity: 0.5;
    transition: opacity 0.3s ease;
}

.active-state .mnav-item .mnav-icon {
    opacity: 1;
}

/* Адаптивность под разные устройства */
@media (max-width: 380px) {
    .mnav-items {
        padding: 0 35%;
    }
    
    .mnav-item {
        padding: 12px 18px;
    }
    
    .mnav-icon {
        width: 42px;
        height: 42px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.mnav-carousel');
    const navItems = document.querySelectorAll('.mnav-item');
    const container = document.querySelector('.mnav-items');
    const mnavContainer = document.querySelector('.mnav-container');
    let isScrolling = false;
    let inactivityTimer;
    let navigationTimer = null; // Таймер для автоматического перехода
    
    // Функция для обновления размеров иконок в зависимости от расстояния от центра
    function updateIconSizes() {
        if (!carousel) return;
        
        const carouselMiddle = carousel.offsetWidth / 2;
        let centerItem = null; // Сохраняем ссылку на центральный элемент
        
        // Проходим по всем элементам и обновляем их размер
        navItems.forEach(item => {
            // Удаляем все ранее установленные классы размеров
            item.classList.remove('mnav-center', 'mnav-near', 'mnav-far', 'mnav-very-far');
            
            // Вычисляем центр элемента
            const itemMiddle = item.offsetLeft + item.offsetWidth / 2 - carousel.scrollLeft;
            
            // Вычисляем расстояние от центра карусели
            const distanceFromCenter = Math.abs(carouselMiddle - itemMiddle);
            
            // Устанавливаем классы в зависимости от расстояния от центра
            if (distanceFromCenter < 30) {
                // Если элемент в центре
                item.classList.add('mnav-center');
                centerItem = item; // Запоминаем центральный элемент
            } else if (distanceFromCenter < 90) {
                // Если элемент рядом с центром
                item.classList.add('mnav-near');
            } else if (distanceFromCenter < 150) {
                // Если элемент далеко от центра
                item.classList.add('mnav-far');
            } else {
                // Если элемент очень далеко от центра
                item.classList.add('mnav-very-far');
            }
        });
        
        // Если есть центральный элемент и это не мобильный режим, не устанавливаем таймер
        // Проверяем ширину экрана - автопереход только на мобильных устройствах
        if (centerItem && !centerItem.classList.contains('mnav-active') && window.innerWidth < 768) {
            // Сначала очистим предыдущий таймер, если он был
            if (navigationTimer) {
                clearTimeout(navigationTimer);
            }
            
            // Проверяем, является ли центральный элемент кнопкой QR-сканера
            const isQrScanButton = centerItem.id === 'qrScanButton';
            
            // Устанавливаем новый таймер для перехода через 400 мс
            navigationTimer = setTimeout(function() {
                if (isQrScanButton) {
                    // Если это кнопка QR-сканера, вызываем функцию открытия модального окна
                    openQrModal();
                } else {
                    // Для обычных ссылок - переходим по адресу href
                    window.location.href = centerItem.getAttribute('href');
                }
            }, 400);
        } else if (navigationTimer) {
            // Если нет центрального элемента или он уже активен или экран > 767px, очищаем таймер
            clearTimeout(navigationTimer);
            navigationTimer = null;
        }
    }
    
    // Функция установки активного состояния
    function setActiveState() {
        clearTimeout(inactivityTimer);
        mnavContainer.classList.add('active-state');
        mnavContainer.classList.remove('inactive-state');
        
        // Устанавливаем таймер неактивности
        inactivityTimer = setTimeout(setInactiveState, 1500);
    }
    
    // Функция установки неактивного состояния
    function setInactiveState() {
        mnavContainer.classList.remove('active-state');
        mnavContainer.classList.add('inactive-state');
    }
    
    // Устанавливаем начальное активное состояние
    setActiveState();
    
    // Добавляем обработчики событий для отслеживания взаимодействия
    mnavContainer.addEventListener('mousemove', setActiveState);
    mnavContainer.addEventListener('touchstart', setActiveState);
    mnavContainer.addEventListener('click', setActiveState);
    mnavContainer.addEventListener('scroll', setActiveState);
    
    // Отменяем таймер навигации при взаимодействии пользователя
    mnavContainer.addEventListener('touchstart', function() {
        if (navigationTimer) {
            clearTimeout(navigationTimer);
            navigationTimer = null;
        }
    }, { passive: true });
    
    mnavContainer.addEventListener('click', function() {
        if (navigationTimer) {
            clearTimeout(navigationTimer);
            navigationTimer = null;
        }
    });
    
    // Первоначальная центровка активного элемента
    function scrollActiveToCenter() {
        const activeItem = document.querySelector('.mnav-item.mnav-active');
        if (!activeItem || !carousel) return;
        
        // Вычисляем позицию для центрирования
        const scrollPosition = activeItem.offsetLeft - (carousel.offsetWidth - activeItem.offsetWidth) / 2;
        
        // Плавная прокрутка до активного элемента
        carousel.scrollTo({
            left: scrollPosition,
            behavior: 'smooth'
        });
        
        // Обновляем размеры иконок после прокрутки
        setTimeout(updateIconSizes, 300);
    }
    
    // Анимация прокрутки к центру при клике на элемент
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Отменяем таймер при клике, чтобы избежать двойной навигации
            if (navigationTimer) {
                clearTimeout(navigationTimer);
                navigationTimer = null;
            }
            
            // Так как это ссылки, мы не preventDefault(), чтобы сохранить навигацию
            
            // Вычисляем позицию для центрирования
            const scrollPosition = this.offsetLeft - (carousel.offsetWidth - this.offsetWidth) / 2;
            
            // Плавная прокрутка
            carousel.scrollTo({
                left: scrollPosition,
                behavior: 'smooth'
            });
        });
    });
    
    // Обработчик прокрутки карусели
    carousel.addEventListener('scroll', function() {
        // Сбрасываем таймер навигации при скролле
        if (navigationTimer) {
            clearTimeout(navigationTimer);
            navigationTimer = null;
        }
        
        if (!isScrolling) {
            window.requestAnimationFrame(function() {
                updateIconSizes();
                isScrolling = false;
            });
            isScrolling = true;
        }
    }, { passive: true });
    
    // Центрирование элемента при окончании скроллинга
    carousel.addEventListener('scrollend', function() {
        // Найдем ближайший к центру элемент
        const carouselMiddle = carousel.offsetWidth / 2;
        let closestItem = null;
        let minDistance = Infinity;
        
        navItems.forEach(item => {
            const itemMiddle = item.offsetLeft + item.offsetWidth / 2 - carousel.scrollLeft;
            const distance = Math.abs(carouselMiddle - itemMiddle);
            
            if (distance < minDistance) {
                minDistance = distance;
                closestItem = item;
            }
        });
        
        // Если нашли ближайший элемент, центрируем его
        if (closestItem) {
            const scrollPosition = closestItem.offsetLeft - (carousel.offsetWidth - closestItem.offsetWidth) / 2;
            
            carousel.scrollTo({
                left: scrollPosition,
                behavior: 'smooth'
            });
            
            // Обновляем размеры иконок
            setTimeout(updateIconSizes, 300);
        }
    }, { passive: true });
    
    // Инициализация при загрузке страницы
    // Сначала пометим активный элемент
    const activeNavItem = document.querySelector('.mnav-item.mnav-active');
    if (activeNavItem) {
        activeNavItem.classList.add('mnav-active');
    }
    
    // Затем запустим центрирование и обновление размеров с небольшой задержкой для стабилизации
    setTimeout(scrollActiveToCenter, 100);
    
    window.addEventListener('resize', function() {
        // При изменении размера окна, если экран стал большим, отключаем таймер перехода
        if (window.innerWidth >= 768 && navigationTimer) {
            clearTimeout(navigationTimer);
            navigationTimer = null;
        }
        updateIconSizes();
    });
    
    // Обработка свайп-жестов для улучшения навигации на мобильных
    let touchStartX = 0;
    let touchEndX = 0;
    
    carousel.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
        setActiveState();
    }, { passive: true });
    
    carousel.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: true });
    
    function handleSwipe() {
        const swipeDistance = touchEndX - touchStartX;
        if (Math.abs(swipeDistance) > 50) {
            // Нашли текущий центральный элемент
            const centerItem = document.querySelector('.mnav-item.mnav-center');
            if (centerItem) {
                const index = parseInt(centerItem.getAttribute('data-index'));
                let nextIndex;
                
                if (swipeDistance > 0) {
                    // Свайп вправо - предыдущий элемент
                    nextIndex = index - 1;
                } else {
                    // Свайп влево - следующий элемент
                    nextIndex = index + 1;
                }
                
                // Находим элемент по индексу
                const nextItem = document.querySelector(`.mnav-item[data-index="${nextIndex}"]`);
                if (nextItem) {
                    const scrollPosition = nextItem.offsetLeft - (carousel.offsetWidth - nextItem.offsetWidth) / 2;
                    carousel.scrollTo({
                        left: scrollPosition,
                        behavior: 'smooth'
                    });
                }
            }
        }
    }
    
    // ================= ЛОГИКА ДЛЯ САМОПИСНОГО МОДАЛЬНОГО ОКНА СКАНИРОВАНИЯ QR ================

    // Элементы пользовательского интерфейса
    const qrScanButton = document.getElementById('qrScanButton');
    const qrScanModal = document.getElementById('qrScanModal');
    const closeButtons = document.querySelectorAll('.custom-modal-close, .custom-modal-close-btn');
    let videoElem = document.getElementById('qr-video');
    let scannerActive = false;
    let videoStream = null;

    // Открытие модального окна при клике на кнопку QR
    qrScanButton.addEventListener('click', function(e) {
        e.preventDefault();
        openQrModal();
    });

    // Закрытие модального окна при клике на кнопки закрытия
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            closeQrModal();
        });
    });

    // Закрытие модального окна при клике вне его содержимого
    qrScanModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeQrModal();
        }
    });

    // Функция открытия модального окна
    function openQrModal() {
        qrScanModal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Блокируем прокрутку страницы

        // Загружаем библиотеку jsQR и запускаем сканер
        loadJsQR().then(() => {
            startQrScanner();
        }).catch(err => {
            console.error('Failed to load jsQR library:', err);
            document.getElementById('scanner-container').innerHTML = 
                '<div class="alert alert-danger">Не удалось загрузить сканер QR-кодов.</div>';
        });
    }

    // Функция закрытия модального окна
    function closeQrModal() {
        qrScanModal.classList.remove('show');
        document.body.style.overflow = ''; // Восстанавливаем прокрутку страницы
        stopQrScanner();
    }

    // Подключаем библиотеку jsQR при необходимости
    function loadJsQR() {
        if (window.jsQR) {
            return Promise.resolve();
        }
        
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.3.1/dist/jsQR.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.body.appendChild(script);
        });
    }

    // Функция запуска сканера QR-кодов
    function startQrScanner() {
        if (scannerActive) return;
        
        // Проверяем доступ к камере
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showScanResult('Ваш браузер не поддерживает доступ к камере.', 'danger');
            return;
        }
        
        // Запрашиваем доступ к камере, предпочтительно задней
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: { ideal: 'environment' } 
            } 
        })
        .then(function(stream) {
            videoStream = stream;
            videoElem.srcObject = stream;
            videoElem.play();
            scannerActive = true;
            
            // Запускаем процесс сканирования
            requestAnimationFrame(scanQRCode);
        })
        .catch(function(error) {
            console.error('Ошибка при получении доступа к камере:', error);
            showScanResult('Не удалось получить доступ к камере. ' + error.message, 'danger');
        });
    }

    // Функция остановки сканера
    function stopQrScanner() {
        scannerActive = false;
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
        
        if (videoElem) {
            videoElem.srcObject = null;
        }
        
        // Скрываем результат сканирования
        const resultElement = document.getElementById('scan-result');
        if (resultElement) {
            resultElement.classList.add('d-none');
        }
    }

    // Функция для сканирования QR-кода из видеопотока
    function scanQRCode() {
        if (!scannerActive) return;
        
        const video = document.getElementById('qr-video');
        if (!video || !video.videoWidth) {
            requestAnimationFrame(scanQRCode);
            return;
        }
        
        // Создаем временный canvas для анализа кадра видео
        const canvas = document.createElement('canvas');
        const canvasContext = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Рисуем текущий кадр на canvas
        canvasContext.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Получаем данные изображения
        const imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);
        
        // Анализируем изображение на наличие QR-кода
        if (window.jsQR) {
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert",
            });
            
            if (code) {
                // Нашли QR-код
                processQRData(code.data);
                return;
            }
        }
        
        // Продолжаем сканирование
        requestAnimationFrame(scanQRCode);
    }

    // Обработка данных из QR-кода
    function processQRData(data) {
        try {
            const qrData = JSON.parse(data);
            
            if (qrData.action === 'change_template_status') {
                // Отправляем запрос на смену статуса шаблона
                changeTemplateStatus(qrData);
            } else {
                showScanResult('Неизвестный формат QR-кода', 'warning');
                requestAnimationFrame(scanQRCode); // Продолжаем сканирование
            }
        } catch (e) {
            console.error('Ошибка при обработке QR-кода:', e);
            showScanResult('Неверный формат QR-кода', 'danger');
            requestAnimationFrame(scanQRCode); // Продолжаем сканирование
        }
    }

    // Функция отправки запроса на изменение статуса шаблона
    function changeTemplateStatus(qrData) {
        // Формируем данные для отправки на сервер
        const formData = new FormData();
        formData.append('template_id', qrData.template_id);
        formData.append('user_id', qrData.user_id);
        formData.append('acquired_id', qrData.acquired_id);
        formData.append('_token', qrData.token);
        
        // Отправляем AJAX запрос
        fetch('/api/change-template-status', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showScanResult(data.message || 'Статус шаблона успешно изменен!', 'success');
                stopQrScanner(); // Останавливаем сканирование после успешного изменения статуса
            } else {
                showScanResult(data.message || 'Ошибка при изменении статуса шаблона', 'danger');
                requestAnimationFrame(scanQRCode); // Продолжаем сканирование
            }
        })
        .catch(error => {
            console.error('Ошибка при отправке запроса:', error);
            showScanResult('Произошла ошибка при обработке запроса', 'danger');
            requestAnimationFrame(scanQRCode); // Продолжаем сканирование
        });
    }

    // Функция отображения результата сканирования
    function showScanResult(message, type = 'info') {
        const resultElement = document.getElementById('scan-result');
        if (!resultElement) {
            // Если элемент не существует, создаем его
            const newResultElement = document.createElement('div');
            newResultElement.id = 'scan-result';
            newResultElement.className = `mt-3 alert alert-${type}`;
            newResultElement.textContent = message;
            
            const scannerContainer = document.getElementById('scanner-container');
            if (scannerContainer) {
                scannerContainer.appendChild(newResultElement);
            }
        } else {
            // Если существует, обновляем его
            resultElement.className = `mt-3 alert alert-${type}`;
            resultElement.textContent = message;
            resultElement.classList.remove('d-none');
        }
    }

    // Обрабатываем нажатие клавиши ESC для закрытия модального окна
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && qrScanModal.classList.contains('show')) {
            closeQrModal();
        }
    });
});
</script>
