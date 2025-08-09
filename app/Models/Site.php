<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'admin_id',
        'name',
        'address',
        'phone',
        'manager',
        'access_mode',
        'comment',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(SiteStaff::class);
    }
}
