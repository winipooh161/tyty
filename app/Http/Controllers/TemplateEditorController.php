<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\UserTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class TemplateEditorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:client,admin');
    }

    /**
     * Показать редактор шаблона.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $template = Template::findOrFail($id);
        
        // Проверяем, есть ли у текущего пользователя сохраненный шаблон
        $userTemplate = UserTemplate::where('user_id', Auth::id())
                        ->where('template_id', $template->id)
                        ->latest()
                        ->first();
        
        // Сбрасываем данные об обложке, если они сохранены в сессии
        if (session()->has('cover_preview')) {
            session()->forget('cover_preview');
        }
        
        // Передаем параметр is_new_template = false
        $is_new_template = false;
        
        return view('templates.editor', compact('template', 'userTemplate', 'is_new_template'));
    }
    
    /**
     * Создать новый шаблон на основе существующего.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function createNew($id)
    {
        $template = Template::findOrFail($id);
        
        // Принудительно создаем новый шаблон
        $userTemplate = null;
        
        // Сбрасываем данные об обложке, если они сохранены в сессии
        if (session()->has('cover_preview')) {
            session()->forget('cover_preview');
        }
        
        // Передаем параметр is_new_template = true
        $is_new_template = true;
        
        return view('templates.editor', compact('template', 'userTemplate', 'is_new_template'));
    }

    /**
     * Сохранить отредактированный пользователем шаблон.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request, $id)
    {
        $template = Template::findOrFail($id);
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'html_content' => 'required|string',
            'custom_data' => 'nullable',
            'cover_file' => 'required_without:has_existing_cover|file|mimes:jpeg,png,gif,webp,mp4,webm|max:20480', // 20MB максимум
        ]);
        
        // Обработка данных custom_data
        $customData = json_decode($validatedData['custom_data'] ?? '{}', true) ?? [];
        
        // Данные для создания/обновления шаблона
        $templateData = [
            'name' => $validatedData['name'],
            'html_content' => $validatedData['html_content'],
            'custom_data' => $customData,
            // Всегда устанавливаем статус "published"
            'status' => 'published',
        ];
        
        // Проверяем, нужно ли принудительно создать новый шаблон
        if ($request->input('is_new_template') == '1') {
            // Принудительно создаем новый шаблон
            $userTemplate = null;
        } else {
            // Находим существующий шаблон
            $userTemplate = UserTemplate::where('user_id', Auth::id())
                         ->where('template_id', $template->id)
                         ->latest()
                         ->first();
        }

        // Проверяем, загружена ли новая обложка
        if ($request->hasFile('cover_file')) {
            // Обработка загруженного файла обложки
            $coverFile = $request->file('cover_file');
            $fileExtension = strtolower($coverFile->getClientOriginalExtension());
            $fileName = time() . '_' . Str::random(10) . '.' . $fileExtension;
            
            // Определяем тип файла
            $isVideo = in_array($fileExtension, ['mp4', 'webm']);
            $coverType = $isVideo ? 'video' : 'image';
            
            // Создаем полный путь к директории
            $publicStorage = storage_path('app/public');
            $coversPath = $publicStorage . '/template_covers';
            
            // Создаем директории, если их нет
            if (!File::isDirectory($publicStorage)) {
                File::makeDirectory($publicStorage, 0755, true);
            }
            
            if (!File::isDirectory($coversPath)) {
                File::makeDirectory($coversPath, 0755, true);
            }
            
            if ($isVideo) {
                try {
                    // Путь для сохранения
                    $outputPath = $coversPath . '/' . $fileName;
                    
                    // Проверяем наличие FFmpeg
                    $hasFFmpeg = $this->checkFFmpegInstalled();
                    
                    if ($hasFFmpeg) {
                        // Временный путь
                        $tempPath = $coverFile->getRealPath();
                        
                        // Экранируем пути для безопасного использования в командах
                        $escapedTempPath = escapeshellarg($tempPath);
                        $escapedOutputPath = escapeshellarg($outputPath);
                        
                        // Получаем длительность видео, если возможно
                        try {
                            $ffprobeCmd = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 {$escapedTempPath}";
                            $duration = (float)trim(shell_exec($ffprobeCmd));
                            
                            if ($duration > 15) {
                                return redirect()->back()->withErrors(['cover_file' => 'Видео должно быть не длиннее 15 секунд']);
                            }
                        } catch (\Exception $e) {
                            // Если не удалось определить длину, продолжаем без проверки
                            Log::warning('Не удалось определить длительность видео: ' . $e->getMessage());
                        }
                        
                        // Сжимаем видео с использованием FFmpeg
                        $ffmpegCmd = "ffmpeg -i {$escapedTempPath} -vf scale=640:-2 -c:v libx264 -preset medium -crf 28 -c:a aac -b:a 96k {$escapedOutputPath}";
                        $output = null;
                        $returnVar = null;
                        exec($ffmpegCmd, $output, $returnVar);
                        
                        if ($returnVar !== 0) {
                            // Ошибка при выполнении ffmpeg, логируем и используем стандартное сохранение
                            Log::error('FFmpeg ошибка: ' . implode("\n", $output));
                            $this->saveFileDirectly($coverFile, $outputPath);
                        }
                    } else {
                        // FFmpeg не установлен, сохраняем файл без обработки
                        $this->saveFileDirectly($coverFile, $outputPath);
                    }
                    
                    $templateData['cover_path'] = $fileName;
                    $templateData['cover_type'] = 'video';
                } catch (\Exception $e) {
                    Log::error('Ошибка при сохранении видео: ' . $e->getMessage());
                    return redirect()->back()->withErrors(['cover_file' => 'Ошибка при сохранении видео: ' . $e->getMessage()]);
                }
            } else {
                // Обработка изображения с Intervention Image
                try {
                    $img = Image::make($coverFile->getRealPath());
                    $img->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    
                    // Сохраняем с более высоким сжатием для JPG
                    if (in_array($fileExtension, ['jpg', 'jpeg'])) {
                        $img->save($coversPath . '/' . $fileName, 75);
                    } else {
                        $img->save($coversPath . '/' . $fileName);
                    }
                    
                    $templateData['cover_path'] = $fileName;
                    $templateData['cover_type'] = 'image';
                } catch (\Exception $e) {
                    Log::error('Ошибка при сохранении изображения: ' . $e->getMessage());
                    return redirect()->back()->withErrors(['cover_file' => 'Ошибка при сохранении изображения: ' . $e->getMessage()]);
                }
            }
            
            // Если был старый файл обложки, удаляем его
            if ($userTemplate && $userTemplate->cover_path) {
                $oldFilePath = $coversPath . '/' . $userTemplate->cover_path;
                if (File::exists($oldFilePath)) {
                    File::delete($oldFilePath);
                }
            }
        } elseif (!$userTemplate || !$userTemplate->cover_path) {
            return redirect()->back()->withErrors(['cover_file' => 'Обложка обязательна для шаблона']);
        }
        
        // Создаем или обновляем пользовательский шаблон
        if ($userTemplate && $request->input('is_new_template') != '1') {
            $userTemplate->update($templateData);
        } else {
            $templateData['user_id'] = Auth::id();
            $templateData['template_id'] = $template->id;
            $userTemplate = UserTemplate::create($templateData);
        }
        
        // Очищаем сессию после сохранения
        if (session()->has('cover_preview')) {
            session()->forget('cover_preview');
        }
        
        return redirect()->route('user.templates')->with('status', 'Шаблон успешно сохранен и опубликован!');
    }
    
    /**
     * Проверяет, установлен ли FFmpeg на сервере
     *
     * @return bool
     */
    private function checkFFmpegInstalled()
    {
        $output = null;
        $returnVar = null;
        
        // Выполняем простую проверку наличия ffmpeg
        @exec('ffmpeg -version', $output, $returnVar);
        
        return $returnVar === 0;
    }
    
    /**
     * Сохраняет файл напрямую без использования FFmpeg
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $outputPath
     * @return bool
     */
    private function saveFileDirectly($file, $outputPath)
    {
        try {
            return $file->move(dirname($outputPath), basename($outputPath));
        } catch (\Exception $e) {
            Log::error('Ошибка при прямом сохранении файла: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Сохранить черновик шаблона с помощью AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAjax(Request $request, $id)
    {
        $template = Template::findOrFail($id);
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'html_content' => 'required|string',
            'custom_data' => 'nullable',
        ]);
        
        // Обработка данных custom_data
        $customData = json_decode($validatedData['custom_data'], true) ?? [];
        
        // Создаем или обновляем пользовательский шаблон
        $userTemplate = UserTemplate::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'template_id' => $template->id
            ],
            [
                'name' => $validatedData['name'],
                'html_content' => $validatedData['html_content'],
                'custom_data' => $customData,
                'status' => 'published', // Автоматически публикуем при сохранении
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Шаблон сохранен и опубликован',
            'template_id' => $userTemplate->id
        ]);
    }
}
