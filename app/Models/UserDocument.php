<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    protected $fillable = [
        'user_id','type','series','number','issued_at','issued_by','expires_at','comment','categories','files'
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
        'categories' => 'array',
        'files' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


