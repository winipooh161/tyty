@extends('layouts.app')

@section('content')
<div class="editor-container">
    <!-- Плавающая панель с кнопками управления -->
    <div class="floating-controls">
        <button type="button" class="btn btn-primary rounded-circle btn-settings" data-bs-toggle="offcanvas" data-bs-target="#settingsOffcanvas">
            <i class="bi bi-gear"></i>
        </button>
        <button type="button" class="btn btn-secondary rounded-circle btn-save save-draft">
            <i class="bi bi-save"></i>
        </button>
        <a href="{{ route('client.templates.categories') }}" class="btn btn-light rounded-circle btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div> 
    
    <!-- Область предпросмотра шаблона (на весь экран) -->
    <div id="template-preview" class="template-container fullscreen">
        {!! isset($userTemplate) ? $userTemplate->html_content : $template->html_content !!}
    </div>
    
    <!-- Выезжающая снизу панель с настройками (вместо боковой) -->
    <div class="offcanvas offcanvas-bottom taller-offcanvas" tabindex="-1" id="settingsOffcanvas" aria-labelledby="settingsOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="settingsOffcanvasLabel">
                {{ isset($userTemplate) ? 'Редактирование шаблона' : 'Создание нового шаблона' }}
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="template-form" action="{{ route('client.templates.save', $template->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Добавляем скрытое поле для отслеживания создания нового шаблона -->
                <input type="hidden" name="is_new_template" value="{{ $is_new_template ? '1' : '0' }}">
                
                <div class="mb-3">

                    <input type="text" class="form-control" id="template-name" name="name" 
                           value="{{ isset($userTemplate) ? $userTemplate->name : $template->name }}" required>
                </div>
                
                <!-- Новый блок для загрузки обложки -->
                <div class="mb-4">
                    <h6 class="mb-3 border-bottom pb-2">Обложка шаблона (обязательно)</h6>
                    
                    <div class="mb-3">
                     
                        <input type="file" class="form-control" id="cover_file" name="cover_file" 
                               accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm" 
                               {{ !isset($userTemplate) || !$userTemplate->cover_path ? 'required' : '' }}>
                        <div class="form-text">Поддерживаются  (JPG, PNG, GIF) и видео (MP4, WebM)  15 секунд</div>
                    </div>
                    
                    <!-- Предпросмотр загруженной обложки -->
                    <div id="cover-preview-container" class="mb-3 {{ isset($userTemplate) && $userTemplate->cover_path ? '' : 'd-none' }}">
                        <div class="card">
                            <div class="card-body">
                                <h6>Предпросмотр обложки</h6>
                                @if(isset($userTemplate) && $userTemplate->cover_path)
                                    @if($userTemplate->cover_type === 'image')
                                        <img src="{{ asset('storage/template_covers/' . $userTemplate->cover_path) }}" 
                                             class="img-fluid rounded" alt="Обложка шаблона">
                                    @elseif($userTemplate->cover_type === 'video')
                                        <video class="w-100 rounded" controls muted>
                                            <source src="{{ asset('storage/template_covers/' . $userTemplate->cover_path) }}" 
                                                    type="video/{{ pathinfo($userTemplate->cover_path, PATHINFO_EXTENSION) }}">
                                            Ваш браузер не поддерживает видео.
                                        </video>
                                    @endif
                                @endif
                                <div id="dynamic-preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Добавляем новый блок для настроек серии шаблонов -->
                <div class="mb-4">
                 
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_series" name="is_series" 
                                   {{ isset($userTemplate) && isset($userTemplate->custom_data['is_series']) && $userTemplate->custom_data['is_series'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_series">
                                Создать серию шаблонов
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3 series-options {{ isset($userTemplate) && isset($userTemplate->custom_data['is_series']) && $userTemplate->custom_data['is_series'] ? '' : 'd-none' }}">
                        <label for="series_quantity" class="form-label">Количество шаблонов в серии</label>
                        <input type="number" class="form-control" id="series_quantity" name="series_quantity" 
                               value="{{ isset($userTemplate) && isset($userTemplate->custom_data['series_quantity']) ? $userTemplate->custom_data['series_quantity'] : '10' }}" 
                               min="1" max="1000">
                    </div>
                    
                    <div class="mb-3 series-options {{ isset($userTemplate) && isset($userTemplate->custom_data['is_series']) && $userTemplate->custom_data['is_series'] ? '' : 'd-none' }}">
                        <label for="series_description" class="form-label">Описание серии</label>
                        <textarea class="form-control" id="series_description" name="series_description" rows="2">{{ isset($userTemplate) && isset($userTemplate->custom_data['series_description']) ? $userTemplate->custom_data['series_description'] : '' }}</textarea>
                    </div>
                </div>
                
                <input type="hidden" name="html_content" id="html-content-input">
                <input type="hidden" name="custom_data" id="custom-data-input" 
                       value="{{ isset($userTemplate) ? json_encode($userTemplate->custom_data) : '{}' }}">
                
                <hr>
                
              
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> 
                        {{ isset($userTemplate) && !$is_new_template ? 'Сохранить изменения' : 'Создать и опубликовать шаблон' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Уведомление о сохранении -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
    <div id="saveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Уведомление</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Шаблон успешно сохранен и опубликован!
        </div>
    </div>
</div>

<!-- Модальное окно для обратной связи после отправки формы -->
<div class="modal fade" id="formSubmissionModal" tabindex="-1" aria-labelledby="formSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formSubmissionModalLabel">Отправка формы</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="form-submission-message"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>

</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const templatePreview = document.getElementById('template-preview');
    const templateForm = document.getElementById('template-form');
    const htmlContentInput = document.getElementById('html-content-input');
    const customDataInput = document.getElementById('custom-data-input');
    const coverFileInput = document.getElementById('cover_file');
    const coverPreviewContainer = document.getElementById('cover-preview-container');
    const dynamicPreview = document.getElementById('dynamic-preview');
    
    // Создаем экземпляр Toast
    const saveToast = new bootstrap.Toast(document.getElementById('saveToast'));
    
    // Получение пользовательских данных, если они существуют
    let customData = {};
    try {
        customData = JSON.parse(customDataInput.value);
    } catch (e) {
        console.error('Ошибка при парсинге пользовательских данных', e);
    }
    
    // Функция для прокрутки к полю и редактирования
    function scrollToFieldAndEdit(element) {
        // Прокрутка к элементу
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Задержка для завершения прокрутки
        setTimeout(() => {
            focusAndEnableEditing(element);
        }, 300);
    }
    
    // Функция для обновления скрытого поля с пользовательскими данными
    function updateCustomDataInput() {
        // Добавляем настройки серии в customData
        customData['is_series'] = document.getElementById('is_series').checked;
        customData['series_quantity'] = document.getElementById('series_quantity').value;
        customData['series_description'] = document.getElementById('series_description').value;
        
        customDataInput.value = JSON.stringify(customData);
    }
    
    // Функция для фокусировки и активации режима редактирования
    function focusAndEnableEditing(element) {
        // Снимаем выделение с других элементов
        document.querySelectorAll('[data-editable].editing').forEach(el => {
            if (el !== element) {
                el.classList.remove('editing');
                el.contentEditable = false;
            }
        });
        
        // Переключаем режим редактирования
        element.classList.add('editing');
        element.contentEditable = true;
        element.focus();
        
        // Установка курсора в конец текста
        const range = document.createRange();
        const sel = window.getSelection();
        range.selectNodeContents(element);
        range.collapse(false);
        sel.removeAllRanges();
        sel.addRange(range);
    }
    
    // Функция перехода к следующему/предыдущему полю
    function navigateToField(direction) {
        const currentField = document.querySelector('[data-editable].editing');
        if (!currentField) return;
        
        const editableElements = Array.from(templatePreview.querySelectorAll('[data-editable]'));
        const currentIndex = editableElements.indexOf(currentField);
        
        if (currentIndex !== -1) {
            let nextIndex;
            if (direction === 'next') {
                nextIndex = (currentIndex + 1) % editableElements.length;
            } else {
                nextIndex = (currentIndex - 1 + editableElements.length) % editableElements.length;
            }
            
            scrollToFieldAndEdit(editableElements[nextIndex]);
        }
    }
    
    // Функция для инициализации элементов шаблона для редактирования
    function initializeEditableElements() {
        // Находим все элементы с атрибутом data-editable
        const editableElements = templatePreview.querySelectorAll('[data-editable]');
        
        editableElements.forEach(element => {
            const fieldName = element.dataset.editable;
            
            // Сохраняем исходное содержимое
            if (!element.dataset.defaultContent) {
                element.dataset.defaultContent = element.innerHTML;
            }
            
            // Если есть пользовательские данные, устанавлием их
            if (customData[fieldName]) {
                element.innerHTML = customData[fieldName];
            }
            
            // Добавляем обработчики для управления прямым редактированием
            element.addEventListener('click', function(e) {
                e.stopPropagation();
                focusAndEnableEditing(this);
            });
            
            element.addEventListener('blur', function() {
                this.contentEditable = false;
                
                // Обновляем данные, когда пользователь завершает редактирование
                const fieldName = this.dataset.editable;
                customData[fieldName] = this.innerHTML;
                updateCustomDataInput();
            });
            
            // Обработка нажатия клавиш для навигации и сохранения
            element.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.blur();
                    
                    // Переход к следующему полю при нажатии Enter
                    setTimeout(() => navigateToField('next'), 100);
                }
                if (e.key === 'Tab') {
                    e.preventDefault();
                    this.blur();
                    
                    // Переход в зависимости от shift
                    setTimeout(() => navigateToField(e.shiftKey ? 'prev' : 'next'), 100);
                }
                if (e.key === 'Escape') {
                    // Отмена изменений и выход из режима редактирования
                    if (customData[fieldName]) {
                        this.innerHTML = customData[fieldName];
                    } else {
                        this.innerHTML = this.dataset.defaultContent || '';
                    }
                    this.blur();
                }
            });
        });
        
        // Добавляем горячие клавиши для навигации
        document.addEventListener('keydown', function(e) {
            // Alt+Right: следующее поле
            if (e.altKey && e.key === 'ArrowRight') {
                e.preventDefault();
                navigateToField('next');
            }
            
            // Alt+Left: предыдущее поле
            if (e.altKey && e.key === 'ArrowLeft') {
                e.preventDefault();
                navigateToField('prev');
            }
            
            // Ctrl+S: сохранить черновик
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.querySelector('.save-draft').click();
            }
        });
    }
    
    // Перехватываем отправку всех форм внутри шаблона
    function interceptFormSubmissions() {
        const forms = templatePreview.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // В реальном приложении здесь был бы AJAX запрос к бэкенду для отправки письма
                // Для демонстрации показываем модальное окно с результатом
                document.getElementById('form-submission-message').innerHTML = `
                    <div class="alert alert-success mb-3">
                        Сообщение отправлено успешно
                    </div>
                    <div class="mt-3">
                        <strong>Содержимое формы:</strong>
                        <div class="border p-3 mt-2 bg-light">
                            <p>Форма будет обработана при публикации шаблона</p>
                        </div>
                    </div>
                `;
                
                // Показываем модальное окно
                const formModal = new bootstrap.Modal(document.getElementById('formSubmissionModal'));
                formModal.show();
                
                // Сбрасываем форму
                this.reset();
            });
        });
    }
    
    // Обработчик отправки формы
    templateForm.addEventListener('submit', function(e) {
        // Обновляем настройки перед отправкой
        updateCustomDataInput();
        
        // Устанавливаем актуальное HTML содержимое шаблона
        htmlContentInput.value = templatePreview.innerHTML;
    });
    
    // Обработчик для сохранения черновика через AJAX
    document.querySelector('.save-draft').addEventListener('click', function() {
        const templateId = @json($template->id);
        const templateName = document.getElementById('template-name').value;
        
        // Обновляем настройки
        updateCustomDataInput();
        
        // Устанавливаем актуальное html содержимое шаблона
        htmlContentInput.value = templatePreview.innerHTML;
        
        // Формируем данные формы для отправки
        const formData = new FormData();
        formData.append('name', templateName);
        formData.append('html_content', templatePreview.innerHTML);
        formData.append('custom_data', customDataInput.value);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Отправляем данные на сервер
        fetch(`/client/templates/editor/${templateId}/save-ajax`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Показываем уведомление об успешном сохранении
                saveToast.show();
            } else {
                alert('Ошибка при сохранении черновика');
            }
        })
        .catch(error => {
            console.error('Ошибка при сохранении черновика', error);
            alert('Ошибка при сохранении черновика');
        });
    });
    
    // Обработчик для предпросмотра загружаемой обложки
    if (coverFileInput) {
        coverFileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const reader = new FileReader();
                
                // Проверяем размер файла (максимум 20MB)
                if (file.size > 20 * 1024 * 1024) {
                    alert('Файл слишком большой. Максимальный размер 20MB.');
                    this.value = '';
                    return;
                }
                
                // Если это видео, проверяем его длительность
                if (file.type.startsWith('video/')) {
                    const video = document.createElement('video');
                    video.preload = 'metadata';
                    
                    video.onloadedmetadata = function() {
                        window.URL.revokeObjectURL(video.src);
                        if (video.duration > 16) {
                            alert('Видео слишком длинное. Максимальная длительность 15 секунд.');
                            coverFileInput.value = '';
                            return;
                        }
                        
                        // Показываем предпросмотр видео
                        coverPreviewContainer.classList.remove('d-none');
                        dynamicPreview.innerHTML = `
                            <video class="w-100 rounded" controls muted>
                                <source src="${URL.createObjectURL(file)}" type="${file.type}">
                                Ваш браузер не поддерживает видео.
                            </video>
                        `;
                    };
                    
                    video.src = URL.createObjectURL(file);
                } else if (file.type.startsWith('image/')) {
                    reader.onload = function(e) {
                        // Показываем предпросмотр изображения
                        coverPreviewContainer.classList.remove('d-none');
                        dynamicPreview.innerHTML = `
                            <img src="${e.target.result}" class="img-fluid rounded" alt="Обложка шаблона">
                        `;
                    };
                    
                    reader.readAsDataURL(file);
                }
            }
        });
    }
    
    // Инициализируем интерфейс
    initializeEditableElements();
    
    // Инициализируем перехват форм
    interceptFormSubmissions();
    
    // Обработчик для показа/скрытия настроек серии
    const isSeriesCheckbox = document.getElementById('is_series');
    const seriesOptions = document.querySelectorAll('.series-options');
    
    if (isSeriesCheckbox) {
        isSeriesCheckbox.addEventListener('change', function() {
            seriesOptions.forEach(option => {
                if (this.checked) {
                    option.classList.remove('d-none');
                } else {
                    option.classList.add('d-none');
                }
            });
        });
    }
});
</script>
@endsection
