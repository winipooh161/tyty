<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Отключаем директиву @vite в шаблонах Blade
        Blade::directive('vite', function ($expression) {
            return '<!-- Vite ресурсы отключены, используйте прямые ссылки на файлы -->';
        });
        
        // Создаем символическую ссылку между storage и public, если она отсутствует
        if (!file_exists(public_path('storage'))) {
            try {
                $target = storage_path('app/public');
                $link = public_path('storage');
                
                if (PHP_OS_FAMILY === 'Windows') {
                    // Windows требует специальной команды для создания символической ссылки
                    exec('mklink /D "' . str_replace('/', '\\', $link) . '" "' . str_replace('/', '\\', $target) . '"');
                } else {
                    symlink($target, $link);
                }
            } catch (\Exception $e) {
                // Логируем ошибку, но не останавливаем работу приложения
                \Log::error('Невозможно создать символическую ссылку storage: ' . $e->getMessage());
            }
        }
    }
}
