@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.templates') }}">Мои шаблоны</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $userTemplate->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Обложка шаблона</h5>
                </div>
                <div class="card-body p-0">
                    @if($userTemplate->cover_path)
                        @if($userTemplate->cover_type === 'video')
                            <video class="w-100" controls autoplay loop muted>
                                <source src="{{ asset('storage/template_covers/'.$userTemplate->cover_path) }}" 
                                        type="video/{{ pathinfo($userTemplate->cover_path, PATHINFO_EXTENSION) }}">
                                Ваш браузер не поддерживает видео.
                            </video>
                        @else
                            <img src="{{ asset('storage/template_covers/'.$userTemplate->cover_path) }}" 
                                 class="img-fluid w-100" alt="{{ $userTemplate->name }}">
                        @endif
                    @else
                        <div class="text-center p-4 bg-light">
                            <i class="bi bi-file-earmark-text" style="font-size: 3rem;"></i>
                            <p class="mt-2">Обложка отсутствует</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $userTemplate->name }}</h5>
                        @if($userTemplate->status === 'published')
                            <span class="badge bg-success">Опубликован</span>
                            <a href="{{ route('public.template', $userTemplate->id) }}" target="_blank" class="ms-2 small">
                                <i class="bi bi-box-arrow-up-right"></i> Открыть публичный доступ
                            </a>
                        @else
                            <span class="badge bg-warning text-dark">Черновик</span>
                        @endif
                    </div>
                    <div>
                        <!-- Кнопка создания нового шаблона -->
                        <a href="{{ route('client.templates.create-new', $userTemplate->template_id) }}" class="btn btn-sm btn-info me-2">
                            <i class="bi bi-plus-square me-1"></i> Создать новый шаблон
                        </a>
                        
                        <!-- Кнопки публикации/отмены публикации -->
                        @if($userTemplate->status === 'published')
                            <form action="{{ route('user.templates.unpublish', $userTemplate->id) }}" method="POST" class="d-inline me-2">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-eye-slash me-1"></i> Отменить публикацию
                                </button>
                            </form>
                        @else
                            <form action="{{ route('user.templates.publish', $userTemplate->id) }}" method="POST" class="d-inline me-2">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-globe me-1"></i> Опубликовать
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('user.templates.edit', $userTemplate->id) }}" class="btn btn-sm btn-primary me-2">
                            <i class="bi bi-pencil me-1"></i> Редактировать
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-1"></i> Удалить
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="user-template-container">
                        {!! $userTemplate->html_content !!}
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">
                                <strong>Категория:</strong> {{ $userTemplate->template->category->name }}
                            </small>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">
                                <strong>Статус:</strong>
                                @if($userTemplate->status === 'published')
                                    <span class="badge bg-success">Опубликован</span>
                                @else
                                    <span class="badge bg-warning text-dark">Черновик</span>
                                @endif
                            </small>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">
                                <strong>Последнее обновление:</strong> {{ $userTemplate->updated_at->format('d.m.Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для удаления шаблона -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить шаблон "{{ $userTemplate->name }}"?</p>
                <p class="text-danger">Это действие нельзя будет отменить.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form action="{{ route('user.templates.destroy', $userTemplate->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
   
.user-template-container {
    min-height: 400px;
    padding: 30px;
    overflow: auto;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}
</style>
@endsection
