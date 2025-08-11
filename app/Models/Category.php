<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'parent_id', 'admin_id'];

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

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
}
