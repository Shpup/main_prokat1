<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripSheet extends Model
{
    protected $fillable = [
        'date_time', 'site_id', 'address', 'vehicle_id', 'driver_id', 'distance',
        'status', 'cost', 'admin_id',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'distance' => 'float',
        'cost' => 'float',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
