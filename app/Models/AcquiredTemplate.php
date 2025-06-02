<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcquiredTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',           // ID пользователя, который получил шаблон
        'user_template_id',  // ID оригинального шаблона пользователя
        'acquired_at',       // Дата и время получения шаблона
        'used_at',           // Дата и время использования шаблона
        'status',            // Статус шаблона (приобретен, активен, использован и т.д.)
        'folder_id',         // ID папки, в которой находится шаблон
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'acquired_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Получить пользователя, которому принадлежит этот полученный шаблон.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить оригинальный шаблон пользователя.
     */
    public function userTemplate()
    {
        return $this->belongsTo(UserTemplate::class, 'user_template_id');
    }
    
    /**
     * Связь с папкой, в которой находится шаблон.
     */
    public function folder()
    {
        return $this->belongsTo(TemplateFolder::class, 'folder_id');
    }
}
