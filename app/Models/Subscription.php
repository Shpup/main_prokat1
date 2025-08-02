<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    /**
     * Поля, которые можно массово заполнять.
     */
    protected $fillable = ['user_id', 'is_active', 'expires_at'];

    /**
     * Преобразование типов для полей.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Связь с моделью User (один пользователь - одна подписка).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
