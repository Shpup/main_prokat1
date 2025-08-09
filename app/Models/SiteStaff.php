<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteStaff extends Model
{
    protected $table = 'site_staff';

    protected $fillable = [
        'site_id',
        'name',
        'phone',
        'comment',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
