@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <!-- Отображение полученных шаблонов в стиле страницы my-templates -->
    @php
        $acquiredTemplates = Auth::user()->acquiredTemplates()
            ->with('userTemplate.template.category', 'userTemplate.user')
            ->latest('created_at')
            ->get();
        
        // Получение папок пользователя
        $folders = Auth::user()->templateFolders()->orderBy('name')->get();
    @endphp
    
    @if($acquiredTemplates->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <h4>Полученные шаблоны</h4>
            <p class="text-muted">Шаблоны, которые вы получили из серий других пользователей</p>
        </div>
    </div>
    
 

    <div class="tab-content" id="templateTabsContent">
        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
            <div class="row g-2">
                @foreach($acquiredTemplates as $acquisition)
                    @if($acquisition->userTemplate) <!-- Проверяем, что шаблон существует -->
                    <div class="col-4">
                        <div class="card h-100 template-card">
                            <!-- Превью карточки -->
                            <div class="card-img-top template-preview">
                                @if($acquisition->userTemplate->cover_path)
                                    @if($acquisition->userTemplate->cover_type === 'video')
                                        <video src="{{ asset('storage/template_covers/'.$acquisition->userTemplate->cover_path) }}" 
                                            class="img-fluid" autoplay loop muted></video>
                                    @else
                                        <img src="{{ asset('storage/template_covers/'.$acquisition->userTemplate->cover_path) }}" 
                                            alt="{{ $acquisition->userTemplate->name }}" class="img-fluid">
                                    @endif
                                @else
                                    <div class="default-preview d-flex align-items-center justify-content-center">
                                        <i class="bi bi-file-earmark-text template-icon"></i>
                                    </div>
                                @endif
                                
                                <!-- Статус шаблона -->
                                <div class="template-status">
                                    @if($acquisition->status === 'active')
                                        <span class="badge bg-success status-badge" title="Активный">✓</span>
                                    @elseif($acquisition->status === 'used')
                                        <span class="badge bg-secondary status-badge" title="Использованный">✓</span>
                                    @endif
                                </div>
                                
                               
                            </div>
                            
                            <!-- Кнопки действий -->
                            <div class="template-actions">
                                <div class="action-buttons">
                                    <a href="{{ route('public.template', $acquisition->userTemplate->id) }}" class="action-btn" title="Просмотреть" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    <!-- Кнопка перемещения в папку -->
                                    <button type="button" class="action-btn" title="Переместить в папку" 
                                            data-bs-toggle="modal" data-bs-target="#moveTemplateModal" 
                                            data-template-id="{{ $acquisition->id }}" 
                                            data-template-name="{{ $acquisition->userTemplate->name }}"
                                            data-current-folder="{{ $acquisition->folder_id ?? '' }}">
                                        <i class="bi bi-folder-symlink"></i>
                                    </button>
                                    
                                    <!-- Отображение автора шаблона -->
                                    <div class="template-owner">
                                        <span class="badge bg-dark">
                                            Автор: {{ $acquisition->userTemplate->user->name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        
        <!-- Активные шаблоны -->
        <div class="tab-pane fade" id="active" role="tabpanel" aria-labelledby="active-tab">
            <div class="row g-2">
                @foreach($acquiredTemplates->where('status', 'active') as $acquisition)
                    @if($acquisition->userTemplate)
                    <div class="col-4">
                        <div class="card h-100 template-card">
                            <!-- Аналогичное содержимое карточки как выше -->
                            <div class="card-img-top template-preview">
                                @if($acquisition->userTemplate->cover_path)
                                    @if($acquisition->userTemplate->cover_type === 'video')
                                        <video src="{{ asset('storage/template_covers/'.$acquisition->userTemplate->cover_path) }}" 
                                            class="img-fluid" autoplay loop muted></video>
                                    @else
                                        <img src="{{ asset('storage/template_covers/'.$acquisition->userTemplate->cover_path) }}" 
                                            alt="{{ $acquisition->userTemplate->name }}" class="img-fluid">
                                    @endif
                                @else
                                    <div class="default-preview d-flex align-items-center justify-content-center">
                                        <i class="bi bi-file-earmark-text template-icon"></i>
                                    </div>
                                @endif
                                
                                <div class="template-status">
                                    <span class="badge bg-success status-badge" title="Активный">✓</span>
                                </div>
                                
                           
                            </div>
                            
                            <div class="template-actions">
                                <div class="action-buttons">
                                    <a href="{{ route('public.template', $acquisition->userTemplate->id) }}" class="action-btn" title="Просмотреть" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    <button type="button" class="action-btn" title="Переместить в папку" 
                                            data-bs-toggle="modal" data-bs-target="#moveTemplateModal" 
                                            data-template-id="{{ $acquisition->id }}" 
                                            data-template-name="{{ $acquisition->userTemplate->name }}"
                                            data-current-folder="{{ $acquisition->folder_id ?? '' }}">
                                        <i class="bi bi-folder-symlink"></i>
                                    </button>
                                    
                                    <div class="template-owner">
                                        <span class="badge bg-dark">
                                            Автор: {{ $acquisition->userTemplate->user->name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        
        <!-- Использованные шаблоны -->
        <div class="tab-pane fade" id="used" role="tabpanel" aria-labelledby="used-tab">
            <div class="row g-2">
                @foreach($acquiredTemplates->where('status', 'used') as $acquisition)
                    @if($acquisition->userTemplate)
                    <div class="col-4">
                        <div class="card h-100 template-card">
                            <!-- Аналогичное содержимое карточки как выше -->
                            <div class="card-img-top template-preview">
                                @if($acquisition->userTemplate->cover_path)
                                    @if($acquisition->userTemplate->cover_type === 'video')
                                        <video src="{{ asset('storage/template_covers/'.$acquisition->userTemplate->cover_path) }}" 
                                            class="img-fluid" autoplay loop muted></video>
                                    @else
                                        <img src="{{ asset('storage/template_covers/'.$acquisition->userTemplate->cover_path) }}" 
                                            alt="{{ $acquisition->userTemplate->name }}" class="img-fluid">
                                    @endif
                                @else
                                    <div class="default-preview d-flex align-items-center justify-content-center">
                                        <i class="bi bi-file-earmark-text template-icon"></i>
                                    </div>
                                @endif
                                
                                <div class="template-status">
                                    <span class="badge bg-secondary status-badge" title="Использованный">✓</span>
                                </div>
                                
                              
                            </div>
                            
                            <div class="template-actions">
                                <div class="action-buttons">
                                    <a href="{{ route('public.template', $acquisition->userTemplate->id) }}" class="action-btn" title="Просмотреть" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    <button type="button" class="action-btn" title="Переместить в папку" 
                                            data-bs-toggle="modal" data-bs-target="#moveTemplateModal" 
                                            data-template-id="{{ $acquisition->id }}" 
                                            data-template-name="{{ $acquisition->userTemplate->name }}"
                                            data-current-folder="{{ $acquisition->folder_id ?? '' }}">
                                        <i class="bi bi-folder-symlink"></i>
                                    </button>
                                    
                                    <div class="template-owner">
                                        <span class="badge bg-dark">
                                            Автор: {{ $acquisition->userTemplate->user->name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        
        <!-- Шаблоны по папкам -->
        @foreach ($folders as $folder)
            <div class="tab-pane fade" id="folder-{{ $folder->id }}" role="tabpanel" 
                 aria-labelledby="folder-{{ $folder->id }}-tab">
                <div class="row g-2">
                    @php
                        $folderTemplates = $acquiredTemplates->where('folder_id', $folder->id);
                    @endphp
                    
                    @if($folderTemplates->count() > 0)
                        @foreach($folderTemplates as $acquisition)
                            @if($acquisition->userTemplate)
                            <div class="col-4">
                                <div class="card h-100 template-card">
                                    <!-- Аналогичное содержимое карточки как выше -->
                                    <div class="card-img-top template-preview">
                                        @if($acquisition->userTemplate->cover_path)
                                            @if($acquisition->userTemplate->cover_type === 'video')
                                                <video src="{{ asset('storage/template_covers/'.$acquisition->userTemplate->cover_path) }}" 
                                                    class="img-fluid" autoplay loop muted></video>
                                            @else
                                                <img src="{{ asset('storage/template_covers/'.$acquisition->userTemplate->cover_path) }}" 
                                                    alt="{{ $acquisition->userTemplate->name }}" class="img-fluid">
                                            @endif
                                        @else
                                            <div class="default-preview d-flex align-items-center justify-content-center">
                                                <i class="bi bi-file-earmark-text template-icon"></i>
                                            </div>
                                        @endif
                                        
                                        <div class="template-status">
                                            @if($acquisition->status === 'active')
                                                <span class="badge bg-success status-badge" title="Активный">✓</span>
                                            @elseif($acquisition->status === 'used')
                                                <span class="badge bg-secondary status-badge" title="Использованный">✓</span>
                                            @endif
                                        </div>
                                        
                                        
                                    </div>
                                    
                                    <div class="template-actions">
                                        <div class="action-buttons">
                                            <a href="{{ route('public.template', $acquisition->userTemplate->id) }}" class="action-btn" title="Просмотреть" target="_blank">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            <button type="button" class="action-btn" title="Переместить в папку" 
                                                    data-bs-toggle="modal" data-bs-target="#moveTemplateModal" 
                                                    data-template-id="{{ $acquisition->id }}" 
                                                    data-template-name="{{ $acquisition->userTemplate->name }}"
                                                    data-current-folder="{{ $acquisition->folder_id ?? '' }}">
                                                <i class="bi bi-folder-symlink"></i>
                                            </button>
                                            
                                            <div class="template-owner">
                                                <span class="badge bg-dark">
                                                    Автор: {{ $acquisition->userTemplate->user->name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    @else
                        <div class="col-12">
                            <div class="empty-folder text-center">
                                <div class="empty-folder-icon">
                                    <i class="bi bi-folder2-open"></i>
                                </div>
                                <h4 class="text-muted">Папка пуста</h4>
                                <p class="text-muted mb-4">У вас пока нет шаблонов в этой папке.</p>
                                
                                <p>
                                    <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#moveTemplateModal">
                                        <i class="bi bi-folder-symlink me-1"></i> Переместить шаблоны в эту папку
                                    </a>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Модальное окно для создания новой папки -->
    <div class="modal fade" id="newFolderModal" tabindex="-1" aria-labelledby="newFolderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newFolderModalLabel">Создать новую папку</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('client.folders.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="folder-name" class="form-label">Название папки</label>
                            <input type="text" class="form-control" id="folder-name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="folder-color" class="form-label">Цвет папки</label>
                            <div class="d-flex">
                                <input type="color" class="form-control form-control-color" id="folder-color"
                                    name="color" value="#6c757d">
                                <span class="ms-2 d-flex align-items-center">
                                    <i class="bi bi-folder-fill" style="font-size: 1.5rem; color: #6c757d;"
                                        id="folder-color-preview"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Создать папку</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Модальное окно для перемещения шаблона -->
    <div class="modal fade" id="moveTemplateModal" tabindex="-1" aria-labelledby="moveTemplateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="moveTemplateModalLabel">Переместить шаблон</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="move-template-form" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Выберите папку для шаблона <strong id="move-template-name"></strong>:</p>

                        <div class="list-group">
                            <label class="list-group-item">
                                <input class="form-check-input me-2" type="radio" name="folder_id" value=""
                                    checked>
                                <i class="bi bi-folder text-muted me-2"></i> Без папки
                            </label>

                            @foreach ($folders as $folder)
                                <label class="list-group-item">
                                    <input class="form-check-input me-2" type="radio" name="folder_id"
                                        value="{{ $folder->id }}">
                                    <i class="bi bi-folder-fill me-2" style="color: {{ $folder->color }};"></i>
                                    {{ $folder->name }}
                                </label>
                            @endforeach
                        </div>
                        <input type="hidden" name="template_id" id="move-template-id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Переместить</button>
                    </div>
                </form>
            </div>
        </div>
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
        
        /* Стили для владельца шаблона */
        .template-owner {
            position: absolute;
            bottom: 5px;
            left: 5px;
            z-index: 2;
        }
        
        .template-owner .badge {
            font-size: 10px;
            font-weight: normal;
            padding: 4px 8px;
            border-radius: 10px;
            opacity: 0.9;
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Управление предпросмотром цвета папки при создании
            const folderColor = document.getElementById('folder-color');
            const folderColorPreview = document.getElementById('folder-color-preview');

            if (folderColor && folderColorPreview) {
                folderColor.addEventListener('input', function() {
                    folderColorPreview.style.color = this.value;
                });
            }
            
            // Заполнение данных для перемещения шаблона
            document.querySelectorAll('[data-bs-target="#moveTemplateModal"]').forEach(element => {
                element.addEventListener('click', function() {
                    const templateId = this.getAttribute('data-template-id');
                    const templateName = this.getAttribute('data-template-name');
                    const currentFolder = this.getAttribute('data-current-folder');
                    
                    document.getElementById('move-template-name').textContent = templateName;
                    document.getElementById('move-template-id').value = templateId;
                    
                    // Устанавливаем действие формы с правильным ID шаблона
                    document.getElementById('move-template-form').action = `/client/acquired-templates/${templateId}/move`;
                    
                    // Устанавливаем текущую папку в форме
                    if (currentFolder) {
                        const radioButton = document.querySelector(
                            `input[name="folder_id"][value="${currentFolder}"]`);
                        if (radioButton) radioButton.checked = true;
                    } else {
                        // Если нет текущей папки, выбираем "Без папки"
                        const noFolderRadio = document.querySelector(`input[name="folder_id"][value=""]`);
                        if (noFolderRadio) noFolderRadio.checked = true;
                    }
                });
            });
        });
    </script>
    @else
        <div class="alert alert-info">
            У вас пока нет полученных шаблонов. Вы можете получить шаблоны из серий других пользователей.
        </div>
    @endif
</div>
 <style>
        @media (max-width: 767px) {
            .tab-text {
                display: none;
            }

            .nav-link .bi {
                margin-right: 0 !important;
                font-size: 1.2rem;
            }

            .nav-item {
                margin: 0 2px;
            }

            .nav-tabs .nav-link {
                padding: 0.5rem 0.7rem;
            }

            .folder-tab .dropdown {
                margin-left: 5px !important;
            }
           
        }
         .nav-tabs .nav-link span{
                color: #000
            }
    </style>
@endsection
