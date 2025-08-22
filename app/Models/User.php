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
        'admin_id',
        'phone',
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

    /**
     * Проекты, где пользователь прикреплён как сотрудник
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user', 'user_id', 'project_id')
            ->withTimestamps();
    }

    public function contacts()
    {
        return $this->hasMany(UserContact::class);
    }

    public function phones()
    {
        return $this->contacts()->where('type', 'phone');
    }

    public function emails()
    {
        return $this->contacts()->where('type', 'email');
    }

    public function documents()
    {
        return $this->hasMany(UserDocument::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Связь со статусом сотрудника.
     */
    public function employeeStatus()
    {
        return $this->hasOne(EmployeeStatus::class, 'employee_id');
    }
}
