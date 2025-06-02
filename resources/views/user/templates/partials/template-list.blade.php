@if($templates->count() > 0)
<div class="row g-2">
    @foreach($templates as $template)
    <div class="col-4">
        <div class="card h-100 template-card">
            <!-- Превью карточки (если есть) -->
            <div class="card-img-top template-preview">
                @if($template->cover_path)
                    @if($template->cover_type === 'video')
                        <video src="{{ asset('storage/template_covers/'.$template->cover_path) }}" 
                               class="img-fluid" autoplay loop muted></video>
                    @else
                        <img src="{{ asset('storage/template_covers/'.$template->cover_path) }}" 
                             alt="{{ $template->name }}" class="img-fluid">
                    @endif
                @elseif($template->template->preview_image)
                    <img src="{{ asset('storage/template_previews/'.$template->template->preview_image) }}" 
                         alt="{{ $template->name }}" class="img-fluid">
                @else
                    <div class="default-preview d-flex align-items-center justify-content-center">
                        <i class="bi bi-file-earmark-text template-icon"></i>
                    </div>
                @endif
                
                <!-- Статус шаблона -->
                <div class="template-status">
                    @if($template->status === 'published')
                    <span class="badge bg-success status-badge">✓</span>
                    @else
                    <span class="badge bg-warning text-dark status-badge">◯</span>
                    @endif
                </div>
                
                <!-- Индикатор папки (если есть) -->
                @if($template->folder_id)
                <div class="template-folder-indicator">
                    <span class="badge rounded-pill" style="background-color: {{ $template->folder->color }};">
                        <i class="bi bi-folder-fill"></i>
                    </span>
                </div>
                @endif
             
            </div>
            
            <!-- Кнопки действий -->
            <div class="template-actions">
                <div class="action-buttons">
                    @if($template->status === 'published')
                    <a href="{{ route('public.template', $template->id) }}" class="action-btn" title="Публичный просмотр" target="_blank">
                        <i class="bi bi-globe"></i>
                    </a>
                    @endif
                    
                
                 
                    
                    <a href="{{ route('client.templates.editor', $template->template_id) }}" class="action-btn" title="Редактировать">
                        <i class="bi bi-pencil"></i>
                    </a>
                    
                  
                    <button type="button" class="action-btn" title="Переместить в папку" 
                            data-bs-toggle="modal" data-bs-target="#moveTemplateModal" 
                            data-template-id="{{ $template->id }}" data-template-name="{{ $template->name }}"
                            data-current-folder="{{ $template->folder_id ?? '' }}">
                        <i class="bi bi-folder-symlink"></i>
                    </button>
                    
                    @if($template->status === 'published')
                    <form action="{{ route('user.templates.unpublish', $template->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="action-btn" title="Отменить публикацию">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </form>
                    @else
                    <form action="{{ route('user.templates.publish', $template->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="action-btn" title="Опубликовать">
                            <i class="bi bi-eye"></i>
                        </button>
                    </form>
                    @endif
                    
                    <button type="button" class="action-btn delete-template" title="Удалить" 
                            data-bs-toggle="modal" data-bs-target="#deleteTemplateModal" 
                            data-id="{{ $template->id }}" data-name="{{ $template->name }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<style>
    /* Стили для индикатора папки */
    .template-folder-indicator {
        position: absolute;
        bottom: 5px;
        left: 5px;
        z-index: 2;
    }
    
    /* Стилизация видео в карточках */
    .template-preview video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
    }
    
    /* Стилизация пустого списка внутри папки */
    .empty-folder {
        text-align: center;
        padding: 40px 0;
    }
    
    .empty-folder-icon {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 15px;
    }
</style>

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteTemplateModal" tabindex="-1" aria-labelledby="deleteTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTemplateModalLabel">Удаление шаблона</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Вы действительно хотите удалить шаблон <strong id="template-name-to-delete"></strong>?<br>
                Это действие нельзя будет отменить.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="delete-template-form" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-template');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const templateId = this.getAttribute('data-id');
            const templateName = this.getAttribute('data-name');
            
            document.getElementById('template-name-to-delete').textContent = templateName;
            document.getElementById('delete-template-form').action = `/client/my-templates/${templateId}`;
        });
    });
});
</script>
@else
<div class="empty-folder text-center">
    <div class="empty-folder-icon">
        <i class="bi bi-folder2-open"></i>
    </div>
    <h4 class="text-muted">Папка пуста</h4>
    <p class="text-muted mb-4">У вас пока нет шаблонов в этой категории.</p>
    
    @if(isset($currentFolder))
    <p>
        <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#moveTemplateModal">
            <i class="bi bi-folder-symlink me-1"></i> Переместить шаблоны в эту папку
        </a>
    </p>
    @endif
</div>
@endif
