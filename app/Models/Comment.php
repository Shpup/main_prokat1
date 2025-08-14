<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'project_id',
        'date',
        'start_time',
        'end_time',
        'comment',
        'is_global',
    ];
}


