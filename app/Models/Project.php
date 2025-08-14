<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'manager_id',
        'start_date',
        'end_date',
        'status',
        'admin_id',
    ];
    /**
     * Связь с менеджером (пользователем).
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Связь с оборудованием (многие ко многим).
     */
    public function equipment()
    {
        return $this->belongsToMany(Equipment::class, 'project_equipment')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Сотрудники, прикрепленные к проекту (пивот project_user)
     */
    public function staff()
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id')
            ->withTimestamps();
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
