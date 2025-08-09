<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'admin_id',
        'brand',
        'model',
        'year',
        'license_plate',
        'status',
        'mileage',
        'fuel_type',
        'fuel_grade',
        'fuel_consumption',
        'diesel_consumption',
        'battery_capacity',
        'range',
        'hybrid_consumption',
        'hybrid_range',
        'comment',
    ];
}
