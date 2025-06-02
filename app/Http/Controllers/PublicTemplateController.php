<?php

namespace App\Http\Controllers;

use App\Models\UserTemplate;
use Illuminate\Http\Request;

class PublicTemplateController extends Controller
{
    /**
     * Отображение опубликованного шаблона пользователя
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Находим только опубликованный шаблон
        $userTemplate = UserTemplate::where('id', $id)
                       ->where('status', 'published')
                       ->firstOrFail();
            
        return view('public.template', compact('userTemplate'));
    }
}
