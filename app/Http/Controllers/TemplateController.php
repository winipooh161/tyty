<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\TemplateCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    /**
     * Показать список категорий шаблонов.
     *
     * @return \Illuminate\View\View
     */
    public function categories()
    {
        $categories = TemplateCategory::where('is_active', true)
            ->orderBy('display_order')
            ->get();
            
        return view('templates.categories', compact('categories'));
    }

    /**
     * Показать шаблоны определенной категории.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index($slug)
    {
        $category = TemplateCategory::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
            
        // Проверяем статус пользователя
        $user = Auth::user();
        
        // Если пользователь не VIP, перенаправляем на редактирование стандартного шаблона
        if (!$user->isVip()) {
            // Ищем стандартный шаблон категории
            $defaultTemplate = Template::where('template_category_id', $category->id)
                ->where('is_default', true)
                ->where('is_active', true)
                ->first();
                
            // Если найден, перенаправляем на страницу редактирования
            if ($defaultTemplate) {
                return redirect()->route('client.templates.editor', $defaultTemplate->id);
            }
        }
        
        // Для VIP-пользователей или если нет стандартного шаблона, показываем все шаблоны
        $templates = $category->activeTemplates()->get();
        
        return view('templates.index', compact('category', 'templates'));
    }

    /**
     * Показать страницу просмотра шаблона.
     *
     * @param  string  $categorySlug
     * @param  string  $templateSlug
     * @return \Illuminate\View\View
     */
    public function show($categorySlug, $templateSlug)
    {
        $category = TemplateCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();
            
        $template = Template::where('slug', $templateSlug)
            ->where('template_category_id', $category->id)
            ->where('is_active', true)
            ->firstOrFail();
            
        return view('templates.show', compact('category', 'template'));
    }
}
