<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    protected $fillable = ['name', 'parent_id', 'user_id'];

    /**
     * Связь с родительской категорией.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с дочерними категориями.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Связь с оборудованием.
     */
    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
}
