<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'template_id',
        'folder_id',
        'name',
        'html_content',
        'custom_data',
        'status',
        'cover_path',   // Путь к файлу обложки
        'cover_type',   // Тип обложки: 'image' или 'video'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'custom_data' => 'array',
    ];

    /**
     * Получить пользователя, которому принадлежит этот шаблон.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить оригинальный шаблон, на котором основан этот пользовательский шаблон.
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
    
    /**
     * Получить папку, в которой находится шаблон.
     */
    public function folder()
    {
        return $this->belongsTo(TemplateFolder::class);
    }
}
