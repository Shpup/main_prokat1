<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'description',
        'phone',
        'email',
        'discount_equipment',
        'discount_services',
        'discount_materials',
        'blacklisted',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
