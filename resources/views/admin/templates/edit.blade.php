@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="mb-4 d-flex align-items-center">
                <h2>Редактирование шаблона: {{ $template->name }}</h2>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.templates.update', $template->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Название шаблона *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $template->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="template_category_id" class="form-label">Категория *</label>
                                <select class="form-select @error('template_category_id') is-invalid @enderror" id="template_category_id" name="template_category_id" required>
                                    <option value="">Выберите категорию</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (old('template_category_id', $template->template_category_id) == $category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('template_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $template->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="html_content" class="form-label">HTML содержимое шаблона *</label>
                            <textarea class="form-control @error('html_content') is-invalid @enderror" id="html_content" name="html_content" rows="10" required>{{ old('html_content', $template->html_content) }}</textarea>
                            <div class="form-text">
                                Используйте атрибут <code>data-editable="field-name"</code> для элементов, которые пользователь сможет редактировать.
                                Например: <code>&lt;h1 data-editable="title"&gt;Заголовок&lt;/h1&gt;</code>
                            </div>
                            @error('html_content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="editable_fields" class="form-label">Редактируемые поля (JSON)</label>
                            <textarea class="form-control @error('editable_fields') is-invalid @enderror" id="editable_fields" name="editable_fields" rows="5">{{ old('editable_fields', json_encode($template->editable_fields, JSON_PRETTY_PRINT)) }}</textarea>
                            <div class="form-text">
                                Укажите JSON-объект, где ключи - это названия полей из атрибутов data-editable, 
                                а значения - описания этих полей для пользователя.<br>
                                Пример: <code>{"title": "Заголовок приглашения", "date": "Дата мероприятия"}</code>
                            </div>
                            @error('editable_fields')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="preview_image" class="form-label">Изображение превью</label>
                            @if($template->preview_image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/template_previews/'.$template->preview_image) }}" alt="{{ $template->name }}" class="img-thumbnail" style="max-height: 150px;">
                                <div class="form-text">Текущее изображение</div>
                            </div>
                            @endif
                            <input type="file" class="form-control @error('preview_image') is-invalid @enderror" id="preview_image" name="preview_image" accept="image/*">
                            <div class="form-text">Загрузите новое изображение, если хотите заменить текущее. Рекомендуемый размер: 600x400px, максимальный размер: 2MB</div>
                            @error('preview_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="display_order" class="form-label">Порядок отображения</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" id="display_order" name="display_order" value="{{ old('display_order', $template->display_order) }}" min="0">
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Активный шаблон
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $template->is_default) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_default">
                                        Стандартный шаблон для категории (доступен для всех пользователей)
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Функция для автоматического извлечения data-editable полей из HTML
    function extractEditableFields() {
        const htmlContent = document.getElementById('html_content').value;
        const regex = /data-editable=["']([^"']+)["']/g;
        let match;
        const fields = {};
        
        while ((match = regex.exec(htmlContent)) !== null) {
            const fieldName = match[1];
            if (!fields[fieldName]) {
                fields[fieldName] = `Описание поля "${fieldName}"`;
            }
        }
        
        // Получаем текущие поля
        let currentFields = {};
        try {
            currentFields = JSON.parse(document.getElementById('editable_fields').value);
        } catch (e) {
            currentFields = {};
        }
        
        // Объединяем существующие описания с новыми полями
        const result = {};
        Object.keys(fields).forEach(key => {
            result[key] = currentFields[key] || fields[key];
        });
        
        document.getElementById('editable_fields').value = JSON.stringify(result, null, 2);
    }

    // Добавляем кнопку для извлечения полей
    const htmlTextarea = document.getElementById('html_content');
    const extractButton = document.createElement('button');
    extractButton.type = 'button';
    extractButton.className = 'btn btn-sm btn-outline-secondary mt-2';
    extractButton.textContent = 'Извлечь редактируемые поля';
    extractButton.addEventListener('click', function(e) {
        e.preventDefault();
        extractEditableFields();
    });
    
    htmlTextarea.parentNode.insertBefore(extractButton, htmlTextarea.nextSibling);
});
</script>
@endsection
