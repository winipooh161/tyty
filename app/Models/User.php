<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'role',
        'status', // Добавляем поле status
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    /**
     * Получить все шаблоны пользователя.
     */
    public function userTemplates()
    {
        return $this->hasMany(UserTemplate::class);
    }
    
    /**
     * Получение шаблонов, приобретенных пользователем
     */
    public function acquiredTemplates()
    {
        return $this->hasMany(AcquiredTemplate::class);
    }
    
    /**
     * Получение папок шаблонов пользователя
     */
    public function templateFolders()
    {
        return $this->hasMany(TemplateFolder::class);
    }
    
    /**
     * Проверяет, имеет ли пользователь VIP статус
     *
     * @return bool
     */
    public function isVip()
    {
        return $this->status === 'vip';
    }
}
