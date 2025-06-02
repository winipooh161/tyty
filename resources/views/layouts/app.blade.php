<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Bootstrap CSS и JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Локальные стили вместо использования Vite -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
      @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Дополнительные стили и скрипты -->
    @yield('styles')
</head>
<body>
    <div id="app" class="d-flex">
        @auth
            <!-- Подключение боковой панели навигации для ПК (скрываем на странице редактора) -->
            @if(!request()->routeIs('client.templates.editor'))
                @include('layouts.partials.sidebar')
            @endif
            
            <!-- Подключение мобильной навигации (скрываем на странице редактора) -->
            @if(!request()->routeIs('client.templates.editor'))
                @include('layouts.partials.mobile-nav')
            @endif
        @endauth
        
        <main class="py-4 flex-grow-1 content-wrapper {{ request()->routeIs('client.templates.editor') ? 'p-0' : '' }}">
            @yield('content')
        </main>
    </div>
    
    <!-- Axios для AJAX-запросов -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <!-- Дополнительные скрипты внизу страницы -->
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
