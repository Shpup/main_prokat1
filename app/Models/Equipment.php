<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Equipment extends Model
{
    protected $fillable = [
        'name', 'description', 'price', 'specifications', 'image',
        'category_id', 'barcode', 'qrcode', 'status',
    ];

    protected $casts = [
        'specifications' => 'array',
        'price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Используем одну таблицу pivot: project_equipment
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_equipment')
            ->withPivot('status')
            ->withTimestamps();
    }
}
