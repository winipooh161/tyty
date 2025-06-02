@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Категории шаблонов</h2>
            <p class="text-muted">Выберите категорию для просмотра доступных шаблонов</p>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        @foreach($categories as $category)
        <div class="col">
            <div class="card h-100 ">
                @if($category->image)
                <img src="{{ asset('storage/category_images/'.$category->image) }}" class="card-img-top category-img d-none d-lg-block" alt="{{ $category->name }}">
                @else
                <div class="card-img-top category-img-placeholder d-flex align-items-center justify-content-center bg-light d-none d-lg-flex">
                    <i class="bi bi-card-image text-muted" style="font-size: 3rem;"></i>
                </div>
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $category->name }}</h5>
                    <p class="card-text">{{ $category->description }}</p>
                    
                    @if(Auth::user()->isVip())
                        <!-- VIP пользователи видят все шаблоны -->
                        <a href="{{ route('client.templates.index', $category->slug) }}" class="btn btn-primary">
                            <i class="bi bi-grid me-1"></i> Выбрать шаблон
                        </a>
                        <span class="badge bg-warning text-dark ms-2">VIP</span>
                    @else
                        <!-- Обычные пользователи направляются сразу на стандартный шаблон, если он есть -->
                        @php
                            $defaultTemplate = \App\Models\Template::where('template_category_id', $category->id)
                                ->where('is_default', true)
                                ->where('is_active', true)
                                ->first();
                        @endphp
                        
                        @if($defaultTemplate)
                            <a href="{{ route('client.templates.create-new', $defaultTemplate->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil-square me-1"></i> Создать
                            </a>
                        @else
                            <a href="{{ route('client.templates.index', $category->slug) }}" class="btn btn-secondary disabled">
                                Шаблон недоступен
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
