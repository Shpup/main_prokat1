<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'specialty_id'
    ];

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function nonWorkingDays()
    {
        return $this->hasMany(NonWorkingDay::class);
    }
} 