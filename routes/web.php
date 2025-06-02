<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TemplateEditorController;
use App\Http\Controllers\UserTemplateController; 
use App\Http\Controllers\SeriesTemplateController;
use App\Http\Controllers\PublicTemplateController;
use App\Http\Controllers\TemplateFolderController;
use App\Http\Controllers\Admin\TemplateCategoryController;
use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\TemplateStatusController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('login');
});

Auth::routes();

// Публичные маршруты (без авторизации)
Route::get('/template/{id}', [PublicTemplateController::class, 'show'])->name('public.template');

// Маршрут для изменения статуса шаблона через URL 
Route::get('/template-status/change/{template}/{user}/{acquired}/{timestamp}', 
    [TemplateStatusController::class, 'changeStatusByUrl'])
    ->name('template.status.change')
    ->middleware('web');

// Маршруты для всех типов пользователей
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Новые маршруты для работы с сериями шаблонов
Route::middleware('auth')->group(function() {
    Route::post('/series/acquire/{id}', [SeriesTemplateController::class, 'acquire'])->name('series.acquire');
});

// Маршруты профиля
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Маршруты для администраторов
Route::prefix('admin')->middleware('role:admin')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');
    
    // Маршруты для управления категориями шаблонов
    Route::resource('template-categories', TemplateCategoryController::class, ['as' => 'admin']);
    
    // Маршруты для управления шаблонами
    Route::resource('templates', AdminTemplateController::class, ['as' => 'admin']);
    
    // Маршруты для управления пользователями
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class, ['as' => 'admin']);
});

// Маршруты для клиентов (и администраторов)
Route::prefix('client')->middleware('role:client,admin')->group(function () {
    Route::get('/', [App\Http\Controllers\ClientController::class, 'index'])->name('client.dashboard');
    
    // Маршруты для работы с шаблонами
    Route::get('/templates/categories', [TemplateController::class, 'categories'])->name('client.templates.categories');
    Route::get('/templates/category/{slug}', [TemplateController::class, 'index'])->name('client.templates.index');
    Route::get('/templates/category/{categorySlug}/{templateSlug}', [TemplateController::class, 'show'])->name('client.templates.show');
    
    // Маршруты для редактирования шаблонов
    Route::get('/templates/editor/{id}', [TemplateEditorController::class, 'edit'])->name('client.templates.editor');
    Route::get('/templates/create-new/{id}', [TemplateEditorController::class, 'createNew'])->name('client.templates.create-new');
    Route::post('/templates/editor/{id}/save', [TemplateEditorController::class, 'save'])->name('client.templates.save');
    Route::post('/templates/editor/{id}/save-ajax', [TemplateEditorController::class, 'saveAjax'])->name('client.templates.save-ajax');
    
    // Новый маршрут для обработки отправки форм из шаблонов
    Route::post('/form-submission/{templateId}', [App\Http\Controllers\FormSubmissionController::class, 'submit'])->name('form.submit');
    
    // Маршруты для управления пользовательскими шаблонами
    Route::get('/my-templates', [UserTemplateController::class, 'index'])->name('user.templates');
    Route::get('/my-templates/{id}', [UserTemplateController::class, 'show'])->name('user.templates.show');
    Route::get('/my-templates/{id}/edit', [UserTemplateController::class, 'edit'])->name('user.templates.edit');
    Route::delete('/my-templates/{id}', [UserTemplateController::class, 'destroy'])->name('user.templates.destroy');
    
    // Новые маршруты для публикации/отмены публикации шаблонов
    Route::post('/my-templates/{id}/publish', [UserTemplateController::class, 'publish'])->name('user.templates.publish');
    Route::post('/my-templates/{id}/unpublish', [UserTemplateController::class, 'unpublish'])->name('user.templates.unpublish');
    
    // Новый маршрут для перемещения шаблона в папку
    Route::post('/my-templates/{id}/move', [UserTemplateController::class, 'moveToFolder'])->name('user.templates.move');
    
    // Маршруты для управления папками шаблонов
    Route::post('/folders', [TemplateFolderController::class, 'store'])->name('client.folders.store');
    Route::put('/folders/{id}', [TemplateFolderController::class, 'update'])->name('client.folders.update');
    Route::delete('/folders/{id}', [TemplateFolderController::class, 'destroy'])->name('client.folders.destroy');
    
    // Новый маршрут для перемещения приобретенных шаблонов в папки
    Route::post('/acquired-templates/{id}/move', [App\Http\Controllers\AcquiredTemplateController::class, 'moveToFolder'])->name('acquired.templates.move');
});

// Маршрут для обновления CSRF-токена
Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
})->name('refresh-csrf');

// Маршруты для обычных пользователей
Route::prefix('user')->middleware('role:user')->group(function () {
    Route::get('/', function () {
        return view('user.dashboard');
    })->name('user.dashboard');
    // Другие маршруты для обычных пользователей
});
if (app()->environment('production')) {
    URL::forceScheme('https');
}