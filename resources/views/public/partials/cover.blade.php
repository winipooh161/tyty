<!-- Контейнер для обложки -->
<div id="coverContainer" class="cover-container">
    @if($userTemplate->cover_path)
        @php
            $coverPath = 'storage/template_covers/'.$userTemplate->cover_path;
            $coverExists = file_exists(public_path($coverPath));
        @endphp
        
        @if($userTemplate->cover_type === 'video' && $coverExists)
            <video id="coverVideo" class="cover-video" autoplay loop muted playsinline>
                <source src="{{ asset($coverPath) }}" type="video/{{ pathinfo($userTemplate->cover_path, PATHINFO_EXTENSION) }}">
                Ваш браузер не поддерживает видео.
            </video>
        @elseif($userTemplate->cover_type === 'image' && $coverExists)
            <img src="{{ asset($coverPath) }}" class="cover-image" alt="{{ $userTemplate->name }}">
        @else
            <!-- Запасное изображение или сообщение при отсутствии обложки -->
            <div class="cover-fallback">
                <div class="fallback-content">
                    <i class="bi bi-image text-white mb-2" style="font-size: 3rem;"></i>
                    <h3 class="text-white">{{ $userTemplate->name }}</h3>
                </div>
            </div>
        @endif
    @else
        <!-- Запасное изображение при отсутствии обложки -->
        <div class="cover-fallback">
            <div class="fallback-content">
                <i class="bi bi-file-earmark-text text-white mb-2" style="font-size: 3rem;"></i>
                <h3 class="text-white">{{ $userTemplate->name }}</h3>
            </div>
        </div>
    @endif
    
    <!-- Кнопка пропуска обложки -->
    <div class="skip-btn" id="skipBtn">
        <span>Пропустить</span>
        <i class="bi bi-chevron-down"></i>
    </div>
    
    <!-- Индикатор прогресса свайпа -->
    <div class="swipe-progress-container">
        <div id="swipeProgress" class="swipe-progress"></div>
    </div>
</div>

<!-- Индикатор возврата к обложке -->
<div id="returnToCover" class="return-to-cover">
    <div class="return-indicator">
        <i class="bi bi-chevron-up"></i>
        <span>Вернуться к обложке</span>
    </div>
</div>
