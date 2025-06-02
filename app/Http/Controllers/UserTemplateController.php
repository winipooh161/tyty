<?php

namespace App\Http\Controllers;

use App\Models\UserTemplate;
use App\Models\TemplateFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserTemplateController extends Controller
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
     * Показать список шаблонов текущего пользователя.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $userTemplates = UserTemplate::where('user_id', Auth::id())->get();
        $folders = TemplateFolder::where('user_id', Auth::id())
            ->orderBy('display_order')
            ->get();
            
        return view('user.templates.index', compact('userTemplates', 'folders'));
    }

    /**
     * Показать отдельный пользовательский шаблон.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $userTemplate = UserTemplate::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('template.category')
            ->firstOrFail();
            
        return view('user.templates.show', compact('userTemplate'));
    }

    /**
     * Редактировать существующий пользовательский шаблон.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $userTemplate = UserTemplate::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('template')
            ->firstOrFail();
            
        return view('templates.editor', [
            'template' => $userTemplate->template,
            'userTemplate' => $userTemplate
        ]);
    }

    /**
     * Удалить пользовательский шаблон.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $userTemplate = UserTemplate::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
            
        $userTemplate->delete();
        
        return redirect()->route('user.templates')->with('status', 'Шаблон успешно удален!');
    }

    /**
     * Опубликовать шаблон пользователя.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish($id)
    {
        $userTemplate = UserTemplate::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $userTemplate->update(['status' => 'published']);
        
        return redirect()->back()->with('status', 'Шаблон успешно опубликован!');
    }
    
    /**
     * Отменить публикацию шаблона пользователя.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unpublish($id)
    {
        $userTemplate = UserTemplate::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $userTemplate->update(['status' => 'draft']);
        
        return redirect()->back()->with('status', 'Публикация шаблона отменена!');
    }
    
    /**
     * Переместить шаблон в папку.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function moveToFolder(Request $request, $id)
    {
        $userTemplate = UserTemplate::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'folder_id' => 'nullable|exists:template_folders,id',
        ]);

        // Проверяем, что папка принадлежит текущему пользователю, если указана
        if ($request->folder_id) {
            $folder = TemplateFolder::where('id', $request->folder_id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
        }

        try {
            $userTemplate->update(['folder_id' => $request->folder_id]);
        } catch (\Exception $e) {
            // В случае ошибки выводим детальное сообщение в логи
            \Log::error('Ошибка при перемещении шаблона: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при перемещении шаблона. Пожалуйста, свяжитесь с администратором.');
        }

        return redirect()->back()->with('status', 'Шаблон успешно перемещен!');
    }
}
