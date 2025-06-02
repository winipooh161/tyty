<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    
    <!-- CSRF Token для JavaScript -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SEO метаданные -->
    <title>{{ $userTemplate->name }} | {{ config('app.name') }}</title>
    <meta name="description" content="{{ $userTemplate->description ?? 'Просмотр шаблона ' . $userTemplate->name }}">
    
    <!-- Стили -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Подключаем основные стили для шаблона -->
    @include('public.partials.styles')
    
    <!-- Внешние стили, используемые в шаблоне, если они есть -->
    @if(isset($userTemplate->custom_data['external_styles']))
        @foreach($userTemplate->custom_data['external_styles'] as $style)
            <link rel="stylesheet" href="{{ $style }}">
        @endforeach
    @endif
    
    <!-- Дополнительные стили для кнопки получения шаблона и самописного модального окна QR-кода -->
    <style>
       
    </style>
</head>
<body>
    <!-- Подключаем компонент информационной панели -->
    @include('public.partials.info-panel')

    <!-- Подключаем компонент обложки -->
    @include('public.partials.cover')
    

    <!-- Индикатор серии, если это серия -->
    @if(isset($userTemplate->custom_data['is_series']) && $userTemplate->custom_data['is_series'])
        @php
            $acquiredCount = \App\Models\AcquiredTemplate::where('user_template_id', $userTemplate->id)->count();
            $totalCount = $userTemplate->custom_data['series_quantity'] ?? 0;
            $remainingCount = max(0, $totalCount - $acquiredCount);
        @endphp
        <div class="series-badge">
            <i class="bi bi-collection me-1"></i> Серия: {{ $remainingCount }} из {{ $totalCount }} доступно
        </div>
    @endif
    
    <!-- Непосредственное содержимое шаблона без оболочки -->
    {!! $userTemplate->html_content !!}
    
    <!-- Кнопка для получения шаблона (для всех типов шаблонов) -->
    @auth
        @php
            $alreadyAcquired = \App\Models\AcquiredTemplate::where('user_id', Auth::id())
                ->where('user_template_id', $userTemplate->id)
                ->exists();
                
            $isOwner = $userTemplate->user_id == Auth::id();
            
            // Проверяем, является ли шаблон серией
            $isSeries = isset($userTemplate->custom_data['is_series']) && $userTemplate->custom_data['is_series'];
            
            $acquiredCount = \App\Models\AcquiredTemplate::where('user_template_id', $userTemplate->id)->count();
            
            // Для серий используем указанное количество, для обычных - максимум 1
            $totalCount = $isSeries ? ($userTemplate->custom_data['series_quantity'] ?? 0) : 1;
            $isAvailable = $acquiredCount < $totalCount;
        @endphp
        
        @if(!$alreadyAcquired && !$isOwner && $isAvailable)
            <!-- Заменяем ссылку на форму с методом POST -->
            <form action="{{ route('series.acquire', $userTemplate->id) }}" method="POST" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
                @csrf
                <button type="submit" class="acquire-template-btn" style="border: none; cursor: pointer;">
                    <i class="bi bi-download"></i> Получить шаблон
                </button>
            </form>
        @elseif($alreadyAcquired)
            <!-- Теперь не отображаем этот элемент, т.к. у нас есть кнопка QR -->
        @endif
    @else
        @if(isset($userTemplate->custom_data['is_series']) && $userTemplate->custom_data['is_series'])
            <a href="{{ route('login') }}" class="acquire-template-btn">
                <i class="bi bi-box-arrow-in-right"></i> Войти для получения
            </a>
        @endif
    @endauth

    <!-- Кнопка для показа QR-кода (добавляем только для пользователей, которые получили шаблон) -->
    @auth
        @if(isset($userTemplate->custom_data['is_series']) && $userTemplate->custom_data['is_series'] && 
            \App\Models\AcquiredTemplate::where('user_template_id', $userTemplate->id)->where('user_id', Auth::id())->exists())
            <button type="button" class="qr-code-btn" id="showQrCodeBtn">
                <i class="bi bi-qr-code"></i> Показать QR-код статуса
            </button>
        @endif
    @endauth
    
    <!-- Самописное модальное окно с QR-кодом для изменения статуса шаблона -->
    <div id="qrCodeModalOverlay" class="custom-modal-overlay">
        <div class="custom-modal-container">
         
            <div class="custom-modal-body">
                <div id="qrcode-container">
                    <div id="qrcode"></div>
                    <div class="qr-loading" id="qr-loading">
                        <div class="qr-spinner"></div>
                        <p>Генерация QR-кода...</p>
                    </div>
                </div>
             
            </div>
           
        </div>
    </div>

    <!-- Подключаем скрипты -->
    @include('public.partials.scripts')
    
    <!-- Скрипт для автоматической подстановки имени пользователя -->
    @auth
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Находим все элементы с data-editable="recipient_name"
                const recipientNameFields = document.querySelectorAll('[data-editable="recipient_name"]');
                
                if (recipientNameFields.length > 0) {
                    // Имя текущего пользователя
                    const userName = @json(Auth::user()->name);
                    
                    recipientNameFields.forEach(field => {
                        // Подставляем имя пользователя в поле
                        field.innerHTML = userName;
                    });
                }
            });
        </script>
    @endauth

    <!-- Скрипт для генерации QR-кода и управления модальным окном -->
    <!-- Подключаем несколько библиотек QR-кода для надежности -->
    <script src="https://cdn.jsdelivr.net/npm/davidshimjs-qrcodejs@0.0.2/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Элементы управления модальным окном
            const qrModalOverlay = document.getElementById('qrCodeModalOverlay');
            const showQrModalBtn = document.getElementById('showQrCodeBtn');
            const closeQrModalBtns = document.querySelectorAll('#closeQrModal, #closeQrModalBtn');
            const qrCodeContainer = document.getElementById('qrcode');
            const qrLoadingContainer = document.getElementById('qr-loading');
            
            // Открытие модального окна
            if (showQrModalBtn) {
                showQrModalBtn.addEventListener('click', function() {
                    openQrModal();
                });
            }
            
            // Закрытие модального окна
            closeQrModalBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    closeQrModal();
                });
            });
            
            // Закрытие модального окна при клике вне его содержимого
            qrModalOverlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeQrModal();
                }
            });
            
            // Функция для открытия модального окна QR
            function openQrModal() {
                document.body.style.overflow = 'hidden'; // Блокируем прокрутку страницы
                qrModalOverlay.classList.add('show');
                qrLoadingContainer.style.display = 'block';
                qrCodeContainer.innerHTML = '';
                
                // Небольшая задержка перед генерацией QR-кода для плавной анимации
                setTimeout(generateQRCode, 400);
                
                // Добавляем обработчик клавиши Escape
                document.addEventListener('keydown', handleEscapeKey);
            }
            
            // Функция для закрытия модального окна QR
            function closeQrModal() {
                qrModalOverlay.style.animation = 'fadeOut 0.3s forwards';
                
                setTimeout(() => {
                    qrModalOverlay.classList.remove('show');
                    qrModalOverlay.style.animation = '';
                    document.body.style.overflow = ''; // Восстанавливаем прокрутку страницы
                    
                    // Удаляем обработчик клавиши Escape
                    document.removeEventListener('keydown', handleEscapeKey);
                }, 300);
            }
            
            // Обработчик нажатия клавиши Escape
            function handleEscapeKey(e) {
                if (e.key === 'Escape') {
                    closeQrModal();
                }
            }
            
            // Функция для генерации QR-кода с прямой ссылкой вместо JSON
            function generateQRCode() {
                // Параметры для создания URL
                const templateId = {{ $userTemplate->id }};
                const userId = {{ Auth::id() }};
                const acquiredId = {{ \App\Models\AcquiredTemplate::where('user_template_id', $userTemplate->id)
                    ->where('user_id', Auth::id())->first()->id ?? 0 }};
                const timestamp = Math.floor(Date.now() / 1000); // Текущий UNIX-timestamp в секундах
                
                // Безопасно получаем CSRF-токен, обрабатывая случай, когда мета-тег отсутствует
                let csrfToken = '{{ csrf_token() }}';
                try {
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag && metaTag.getAttribute) {
                        csrfToken = metaTag.getAttribute('content') || csrfToken;
                    }
                } catch (e) {
                    console.warn('Не удалось получить CSRF-токен из мета-тега:', e);
                }
                
                // Создаем прямую ссылку для QR-кода
                const baseUrl = '{{ url("/") }}';
                const changeStatusUrl = `${baseUrl}/template-status/change/${templateId}/${userId}/${acquiredId}/${timestamp}?_token=${csrfToken}`;
                
                // Пытаемся сгенерировать QR-код с URL вместо JSON данных
                try {
                    // Очищаем предыдущий QR-код, если он есть
                    qrCodeContainer.innerHTML = '';
                    
                    // Генерируем QR-код с URL
                    if (typeof QRCode !== 'undefined') {
                        new QRCode(qrCodeContainer, {
                            text: changeStatusUrl,
                            width: 200,
                            height: 200,
                            colorDark: "#000000",
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.H
                        });
                        
                        // Добавляем ссылку под QR-кодом
                        const linkContainer = document.createElement('div');
                        linkContainer.className = 'mt-3 text-center';
                        linkContainer.innerHTML = `
                            <a href="${changeStatusUrl}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="bi bi-link-45deg"></i> Открыть ссылку напрямую
                            </a>
                            <p class="mt-2 small text-muted">Можно перейти по ссылке вместо сканирования QR-кода</p>
                        `;
                        qrCodeContainer.appendChild(linkContainer);
                        
                        // Скрываем загрузку
                        setTimeout(() => {
                            qrLoadingContainer.style.display = 'none';
                        }, 300);
                        return;
                    } else {
                        throw new Error('QRCode библиотека не загружена');
                    }
                } catch (err) {
                    console.error('Ошибка при генерации QR-кода:', err);
                    qrCodeContainer.innerHTML = `
                        <div class="alert alert-danger">Не удалось сгенерировать QR-код: ${err.message}</div>
                        <div class="text-center mt-3">
                            <a href="${changeStatusUrl}" class="btn btn-outline-primary">
                                Открыть ссылку напрямую
                            </a>
                        </div>
                    `;
                    qrLoadingContainer.style.display = 'none';
                }
            }
            
            // Улучшенная функция обработки ошибок
            function handleQRError(error) {
                console.error('Ошибка при генерации QR-кода:', error);
                
                // Создаем JSON с данными для QR-кода напрямую без API
                const directQrData = JSON.stringify({
                    action: 'change_template_status',
                    template_id: {{ $userTemplate->id }},
                    user_id: {{ Auth::id() }},
                    acquired_id: {{ \App\Models\AcquiredTemplate::where('user_template_id', $userTemplate->id)
                        ->where('user_id', Auth::id())->first()->id ?? 0 }},
                    timestamp: Date.now(),
                    token: '{{ csrf_token() }}'
                });
                
                qrCodeContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <p><i class="bi bi-exclamation-triangle me-2"></i> Не удалось получить безопасную ссылку. Используем прямой QR-код.</p>
                    </div>
                `;
                
                // Генерируем QR-код напрямую с JSON данными
                try {
                    const backupQrDiv = document.createElement('div');
                    qrCodeContainer.appendChild(backupQrDiv);
                    
                    new QRCode(backupQrDiv, {
                        text: directQrData,
                        width: 200,
                        height: 200,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                    
                    qrLoadingContainer.style.display = 'none';
                } catch (err) {
                    qrCodeContainer.innerHTML += `
                        <div class="alert alert-danger">Не удалось сгенерировать QR-код: ${err.message}</div>
                        <div class="text-center mt-3">
                            <a href="${window.location.href}" class="btn btn-outline-primary">Обновить страницу</a>
                        </div>
                    `;
                    qrLoadingContainer.style.display = 'none';
                }
            }
            
            // Защитное закрытие модального окна при перезагрузке
            window.addEventListener('beforeunload', function() {
                document.body.style.overflow = '';
            });
        });
    </script>
</body>
</html>
