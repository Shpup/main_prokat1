<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripSheet extends Model
{
    protected $fillable = [
        'date_time',
        'vehicle_id',
        'driver_id',
        'location_id',
        'address',
        'distance',
        'cost',
        'admin_id',
        'status',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function location()
    {
        return $this->belongsTo(Site::class);
    }
}
