<?php

namespace App\Http\Controllers;

use App\Models\UserTemplate;
use App\Models\AcquiredTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SeriesTemplateController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Получить шаблон из серии (или несерийный шаблон)
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function acquire($id)
    {
        // Получаем шаблон
        $userTemplate = UserTemplate::findOrFail($id);
        
        // Проверяем, что пользователь не является владельцем шаблона
        if ($userTemplate->user_id == Auth::id()) {
            return back()->with('error', 'Вы не можете получить свой собственный шаблон.');
        }
        
        // Проверяем, не получил ли пользователь уже этот шаблон
        $alreadyAcquired = AcquiredTemplate::where('user_id', Auth::id())
            ->where('user_template_id', $userTemplate->id)
            ->exists();
            
        if ($alreadyAcquired) {
            return back()->with('error', 'Вы уже получили этот шаблон.');
        }
        
        // Проверяем, является ли шаблон серией
        $isSeries = isset($userTemplate->custom_data['is_series']) && $userTemplate->custom_data['is_series'];
        
        // Для серий проверяем доступное количество, для несерийных устанавливаем лимит = 1
        $acquiredCount = AcquiredTemplate::where('user_template_id', $userTemplate->id)->count();
        $totalCount = $isSeries ? ($userTemplate->custom_data['series_quantity'] ?? 0) : 1;
        
        // Проверка доступности шаблона
        if ($acquiredCount >= $totalCount) {
            return back()->with('error', 'Этот шаблон больше не доступен для получения.');
        }
        
        // Создаем запись о получении шаблона
        AcquiredTemplate::create([
            'user_id' => Auth::id(),
            'user_template_id' => $userTemplate->id,
            'status' => 'active',
            'acquired_at' => now(), // Добавляем обязательное поле acquired_at
            'custom_data' => [
                'acquired_date' => now()->toDateTimeString(),
                'is_series' => $isSeries
            ]
        ]);
        
        // Возвращаемся с сообщением об успехе
        return back()->with('status', 'Шаблон успешно получен и доступен в разделе "Полученные шаблоны".');
    }
}
