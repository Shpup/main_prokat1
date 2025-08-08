<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'description', 'manager_id', 'start_date', 'end_date', 'admin_id', 'status'];

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

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
