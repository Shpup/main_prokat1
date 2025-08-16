<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserContact extends Model
{
    protected $fillable = ['user_id','type','value','comment','is_primary'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePhones($query)
    {
        return $query->where('type', 'phone');
    }

    public function scopeEmails($query)
    {
        return $query->where('type', 'email');
    }
}


