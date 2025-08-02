<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    /**
     * Поля, которые можно массово заполнять.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Поля, которые скрыты при сериализации.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Преобразование типов для полей.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Связь с подпиской.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Проверка активной подписки.
     */
    public function hasActiveSubscription()
    {
        return $this->subscription &&
            $this->subscription->is_active &&
            ($this->subscription->expires_at === null || $this->subscription->expires_at->isFuture());
    }

    /**
     * Проекты, где пользователь является менеджером.
     */
    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'manager_id');
    }
}
