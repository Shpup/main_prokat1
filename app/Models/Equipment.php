<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;
    protected $table = 'equipment';
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
    public function getCategoryPath()
    {
        $path = [];
        $category = $this->category;

        // Логируем информацию о category_id

        while ($category) {
            $path[] = $category->name;
            // Предполагаем, что модель Category имеет связь parent
            $category = $category->parent;
            // Защита от бесконечного цикла
            if (!$category || $category->id === $this->category_id) {
                 break;
            }
        }

        // Разворачиваем путь, чтобы корневая категория была первой
        $path = array_reverse($path);

        // Если путь пустой, возвращаем 'Без категории'
        return empty($path) ? ['Без категории'] : $path;
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
