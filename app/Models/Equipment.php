<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'price', 'specifications', 'image',
        'category_id', 'barcode', 'qrcode', 'status', 'admin_id','is_consumable'
    ];

    protected $casts = [
        'specifications' => 'array',
        'price' => 'decimal:2',
        'is_consumable' => 'boolean'
    ];

    protected static function booted()
    {
        static::addGlobalScope('admin', function ($builder) {
            $user = auth()->user();
            if ($user) {
                $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;
                $builder->where('admin_id', $adminId);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_equipment')
            ->withPivot('status')
            ->withTimestamps();
    }
}
