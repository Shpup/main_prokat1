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

    protected $attributes = [
        'status' => 'on_warehouse'
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

    /**
     * Нормализует значение характеристики, убирая единицы измерения и приводя к числу
     */
    private function normalizeSpecificationValue($value): ?float
    {
        if (empty($value)) {
            return null;
        }

        // Убираем пробелы и приводим к строке
        $value = trim((string) $value);
        
        // Заменяем запятую на точку
        $value = str_replace(',', '.', $value);
        
        // Убираем единицы измерения (см, кг, Вт, А и их варианты)
        $value = preg_replace('/\s*(см|cm|кг|kg|Вт|W|А|A)\s*$/i', '', $value);
        
        // Проверяем, что осталось число
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        return null;
    }

    /**
     * Преобразует старые форматы характеристик в новый
     */
    public function migrateOldSpecifications($specifications): array
    {
        if (empty($specifications) || !is_array($specifications)) {
            return [];
        }

        $newSpecs = [];

        // Маппинг старых ключей на новые
        $keyMapping = [
            'lenght' => 'length_cm',
            'length' => 'length_cm',
            'widht' => 'width_cm',
            'width' => 'width_cm',
            'height' => 'height_cm',
            'weight' => 'weight_kg',
            'power' => 'power_w',
            'current' => 'current_a',
        ];

        foreach ($specifications as $key => $value) {
            $normalizedKey = strtolower(trim($key));
            
            // Проверяем маппинг ключей
            if (isset($keyMapping[$normalizedKey])) {
                $newKey = $keyMapping[$normalizedKey];
                $normalizedValue = $this->normalizeSpecificationValue($value);
                if ($normalizedValue !== null) {
                    $newSpecs[$newKey] = $normalizedValue;
                }
                continue;
            }

            // Обработка поля size вида "100x50" или "100x50cm"
            if ($normalizedKey === 'size' && is_string($value)) {
                $sizeParts = explode('x', $value);
                if (count($sizeParts) === 2) {
                    $length = $this->normalizeSpecificationValue($sizeParts[0]);
                    $width = $this->normalizeSpecificationValue($sizeParts[1]);
                    
                    if ($length !== null) {
                        $newSpecs['length_cm'] = $length;
                    }
                    if ($width !== null) {
                        $newSpecs['width_cm'] = $width;
                    }
                }
                continue;
            }

            // Если ключ не распознан, но значение можно нормализовать, сохраняем как есть
            $normalizedValue = $this->normalizeSpecificationValue($value);
            if ($normalizedValue !== null) {
                $newSpecs[$key] = $normalizedValue;
            }
        }

        return $newSpecs;
    }

    /**
     * Получает характеристики для отображения в форме (с миграцией старых данных)
     */
    public function getSpecificationsForForm(): array
    {
        $specifications = $this->specifications ?? [];
        
        // Если это старый формат, мигрируем его
        if (!empty($specifications) && !isset($specifications['length_cm'])) {
            $specifications = $this->migrateOldSpecifications($specifications);
        }

        return $specifications;
    }

    /**
     * Получает отформатированные характеристики для отображения
     */
    public function getFormattedSpecificationsAttribute()
    {
        $specifications = $this->getSpecificationsForForm();
        
        if (empty($specifications)) {
            return [];
        }

        $labels = [
            'length_cm' => 'Длина',
            'width_cm' => 'Ширина', 
            'height_cm' => 'Высота',
            'weight_kg' => 'Вес',
            'power_w' => 'Мощность',
            'current_a' => 'Ток'
        ];

        $units = [
            'length_cm' => 'см',
            'width_cm' => 'см',
            'height_cm' => 'см', 
            'weight_kg' => 'кг',
            'power_w' => 'Вт',
            'current_a' => 'А'
        ];

        $formatted = [];
        foreach ($labels as $key => $label) {
            if (isset($specifications[$key]) && $specifications[$key] !== null) {
                $formatted[] = [
                    'label' => $label,
                    'value' => $specifications[$key],
                    'unit' => $units[$key]
                ];
            }
        }

        return $formatted;
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
