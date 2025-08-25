<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'comment',
        'start_date',
        'end_date',
        'admin_id',
        'priority',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
