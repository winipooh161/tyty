@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="text-center mb-4">
            <img src="{{ Auth::user()->avatar ? asset('storage/avatars/' . Auth::user()->avatar) : asset('images/default-avatar.jpg') }}"
                class="profile-avatar rounded-circle" alt="Аватар">
            <h4 class="mt-3">{{ Auth::user()->name }}</h4>
            <p class="text-muted">{{ Auth::user()->email }}</p>
        </div>
        @if (session('status'))
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif
       
    </div>

    <div class="card-body">
         <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button"
                    role="tab" aria-controls="all" aria-selected="true">
                    <i class="bi bi-grid me-1"></i>
                    <span class="tab-text">Все</span>
                </button>
            </li>
            <li class="nav-item tab-text" role="presentation">
                <button class="nav-link" id="published-tab" data-bs-toggle="tab" data-bs-target="#published" type="button"
                    role="tab" aria-controls="published" aria-selected="false">
                    <i class="bi bi-check-circle me-1"></i>
                    <span class="tab-text">Опубликованные</span>
                </button>
            </li>
            <li class="nav-item tab-text" role="presentation">
                <button class="nav-link" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft" type="button"
                    role="tab" aria-controls="draft" aria-selected="false">
                    <i class="bi bi-pencil-square me-1"></i>
                    <span class="tab-text">Черновики</span>
                </button>
            </li>
          
            <!-- Для папок добавляем тот же стиль -->
            @foreach ($folders as $folder)
                <li class="nav-item" role="presentation">
                    <button class="nav-link folder-tab d-flex align-items-center" id="folder-{{ $folder->id }}-tab"
                        data-bs-toggle="tab" data-bs-target="#folder-{{ $folder->id }}" type="button" role="tab"
                        aria-controls="folder-{{ $folder->id }}" aria-selected="false">
                        <i class="bi bi-folder-fill" style="color: {{ $folder->color }};"></i>
                        <span class=" ms-1">{{ $folder->name }}</span>

                    </button>
                </li>
            @endforeach
              <button type="button" class="  me-1" style="border: none; background: none;" data-bs-toggle="modal"
                data-bs-target="#newFolderModal">
                <i class="bi bi-folder-plus"></i>
            </button>
        </ul>
        <div class="tab-content" id="myTabContent">
            <!-- Все шаблоны -->
            <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                @include('user.templates.partials.template-list', ['templates' => $userTemplates])
            </div>

            <!-- Опубликованные шаблоны -->
            <div class="tab-pane fade" id="published" role="tabpanel" aria-labelledby="published-tab">
                @include('user.templates.partials.template-list', [
                    'templates' => $userTemplates->where('status', 'published'),
                ])
            </div>

            <!-- Черновики -->
            <div class="tab-pane fade" id="draft" role="tabpanel" aria-labelledby="draft-tab">
                @include('user.templates.partials.template-list', [
                    'templates' => $userTemplates->where('status', 'draft'),
                ])
            </div>

            <!-- Шаблоны по папкам -->
            @foreach ($folders as $folder)
                <div class="tab-pane fade" id="folder-{{ $folder->id }}" role="tabpanel"
                    aria-labelledby="folder-{{ $folder->id }}-tab">
                    @include('user.templates.partials.template-list', [
                        'templates' => $userTemplates->where('folder_id', $folder->id),
                        'currentFolder' => $folder,
                    ])
                </div>
            @endforeach
        </div>

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

    <!-- Модальное окно для редактирования папки -->
    <div class="modal fade" id="editFolderModal" tabindex="-1" aria-labelledby="editFolderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFolderModalLabel">Изменить папку</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit-folder-form" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit-folder-name" class="form-label">Название папки</label>
                            <input type="text" class="form-control" id="edit-folder-name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-folder-color" class="form-label">Цвет папки</label>
                            <div class="d-flex">
                                <input type="color" class="form-control form-control-color" id="edit-folder-color"
                                    name="color" value="#6c757d">
                                <span class="ms-2 d-flex align-items-center">
                                    <i class="bi bi-folder-fill" style="font-size: 1.5rem;"
                                        id="edit-folder-color-preview"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно для удаления папки -->
    <div class="modal fade" id="deleteFolderModal" tabindex="-1" aria-labelledby="deleteFolderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteFolderModalLabel">Удалить папку</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить папку <strong id="delete-folder-name"></strong>?</p>
                    <p class="text-muted">Шаблоны из этой папки не будут удалены и станут доступны в общем списке.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form id="delete-folder-form" action="" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </form>
                </div>
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Переместить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Управление предпросмотром цвета папки при создании
            const folderColor = document.getElementById('folder-color');
            const folderColorPreview = document.getElementById('folder-color-preview');

            folderColor.addEventListener('input', function() {
                folderColorPreview.style.color = this.value;
            });

            // Управление предпросмотром цвета папки при редактировании
            const editFolderColor = document.getElementById('edit-folder-color');
            const editFolderColorPreview = document.getElementById('edit-folder-color-preview');

            editFolderColor.addEventListener('input', function() {
                editFolderColorPreview.style.color = this.value;
            });

            // Заполнение данных для редактирования папки
            document.querySelectorAll('[data-bs-target="#editFolderModal"]').forEach(element => {
                element.addEventListener('click', function() {
                    const folderId = this.getAttribute('data-folder-id');
                    const folderName = this.getAttribute('data-folder-name');
                    const folderColor = this.getAttribute('data-folder-color');

                    document.getElementById('edit-folder-name').value = folderName;
                    document.getElementById('edit-folder-color').value = folderColor;
                    document.getElementById('edit-folder-color-preview').style.color = folderColor;
                    document.getElementById('edit-folder-form').action =
                        `/client/folders/${folderId}`;
                });
            });

            // Заполнение данных для удаления папки
            document.querySelectorAll('[data-bs-target="#deleteFolderModal"]').forEach(element => {
                element.addEventListener('click', function() {
                    const folderId = this.getAttribute('data-folder-id');
                    const folderName = this.getAttribute('data-folder-name');

                    document.getElementById('delete-folder-name').textContent = folderName;
                    document.getElementById('delete-folder-form').action =
                        `/client/folders/${folderId}`;
                });
            });

            // Заполнение данных для перемещения шаблона
            document.querySelectorAll('[data-bs-target="#moveTemplateModal"]').forEach(element => {
                element.addEventListener('click', function() {
                    const templateId = this.getAttribute('data-template-id');
                    const templateName = this.getAttribute('data-template-name');
                    const currentFolder = this.getAttribute('data-current-folder');

                    document.getElementById('move-template-name').textContent = templateName;
                    document.getElementById('move-template-form').action =
                        `/client/my-templates/${templateId}/move`;

                    // Устанавливаем текущую папку в форме
                    if (currentFolder) {
                        const radioButton = document.querySelector(
                            `input[name="folder_id"][value="${currentFolder}"]`);
                        if (radioButton) radioButton.checked = true;
                    }
                });
            });
        });
    </script>

    <!-- Добавляем стили для адаптивных вкладок -->
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
                color: #000 !important;
            }
    </style>
@endsection
