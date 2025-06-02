<?php

namespace App\Http\Controllers;

use App\Models\AcquiredTemplate;
use App\Models\UserTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TemplateStatusController extends Controller
{
    /**
     * Изменение статуса шаблона через API (для сканирования QR-кода)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request)
    {
        try {
            // Проверяем, что пользователь авторизован
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Требуется авторизация'
                ], 401);
            }
            
            // Валидация данных
            $validated = $request->validate([
                'template_id' => 'required|integer|exists:user_templates,id',
                'user_id' => 'required|integer|exists:users,id',
                'acquired_id' => 'required|integer|exists:acquired_templates,id',
            ]);
            
            // Находим шаблон
            $userTemplate = UserTemplate::findOrFail($validated['template_id']);
            
            // Проверяем, что текущий пользователь является владельцем шаблона
            if ($userTemplate->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Только владелец шаблона может изменить его статус'
                ], 403);
            }
            
            // Находим запись о полученном шаблоне
            $acquiredTemplate = AcquiredTemplate::findOrFail($validated['acquired_id']);
            
            // Проверяем, что запись принадлежит указанному пользователю
            if ($acquiredTemplate->user_id != $validated['user_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Некорректные данные запроса'
                ], 400);
            }
            
            // Проверяем, что шаблон активен
            if ($acquiredTemplate->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Шаблон уже не активен'
                ]);
            }
            
            // Меняем статус на "used" (использованный)
            $acquiredTemplate->status = 'used';
            $acquiredTemplate->used_at = now();
            $acquiredTemplate->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Статус шаблона успешно изменен на "Использованный"'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при изменении статуса шаблона: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при обработке запроса'
            ], 500);
        }
    }

    /**
     * Изменение статуса шаблона через URL (для перехода по ссылке из QR-кода)
     *
     * @param  int  $template_id
     * @param  int  $user_id
     * @param  int  $acquired_id
     * @param  int  $timestamp
     * @return \Illuminate\Http\Response
     */
    public function changeStatusByUrl($template_id, $user_id, $acquired_id, $timestamp, Request $request)
    {
        // Проверка CSRF токена
        if (!$request->hasValidSignature() && $request->_token !== csrf_token()) {
            return view('public.status-change', [
                'success' => false,
                'message' => 'Недействительная или устаревшая ссылка. Проверьте CSRF токен.'
            ]);
        }
        
        // Проверяем, что ссылка не старше 24 часов
        $currentTimestamp = time();
        if ($currentTimestamp - $timestamp > 86400) {
            return view('public.status-change', [
                'success' => false,
                'message' => 'Срок действия ссылки истек'
            ]);
        }
        
        try {
            // Находим шаблон
            $userTemplate = UserTemplate::findOrFail($template_id);
            
            // Находим запись о полученном шаблоне
            $acquiredTemplate = AcquiredTemplate::findOrFail($acquired_id);
            
            // Проверяем, что запись принадлежит указанному пользователю
            if ($acquiredTemplate->user_id != $user_id) {
                return view('public.status-change', [
                    'success' => false,
                    'message' => 'Некорректные данные в ссылке'
                ]);
            }
            
            // Проверяем, что шаблон активен
            if ($acquiredTemplate->status !== 'active') {
                return view('public.status-change', [
                    'success' => false,
                    'message' => 'Шаблон уже был отмечен как использованный'
                ]);
            }
            
            // Меняем статус на "used" (использованный)
            $acquiredTemplate->status = 'used';
            $acquiredTemplate->used_at = now();
            $acquiredTemplate->save();
            
            return view('public.status-change', [
                'success' => true,
                'message' => 'Статус шаблона успешно изменен на "Использованный"',
                'template' => $userTemplate
            ]);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при изменении статуса шаблона по ссылке: ' . $e->getMessage());
            
            return view('public.status-change', [
                'success' => false,
                'message' => 'Произошла ошибка при обработке запроса'
            ]);
        }
    }
}
