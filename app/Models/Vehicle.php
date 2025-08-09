<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    protected $fillable = [
        'brand', 'model', 'year', 'license_plate', 'status', 'mileage', 'fuel_type',
        'fuel_grade', 'fuel_consumption', 'diesel_consumption', 'battery_capacity',
        'range', 'hybrid_consumption', 'hybrid_range', 'comment', 'site_id', 'admin_id',
    ];

    protected $casts = [
        'year' => 'integer',
        'mileage' => 'integer',
        'fuel_consumption' => 'float',
        'diesel_consumption' => 'float',
        'battery_capacity' => 'float',
        'range' => 'integer',
        'hybrid_consumption' => 'float',
        'hybrid_range' => 'integer',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
}
