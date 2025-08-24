<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {


        $categoryId = $request->query('category_id');
        $user = auth()->user();
        $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;

        $query = Equipment::where('admin_id', $adminId)
            ->with('category', 'projects');

        if ($categoryId) {
            $category = Category::find($categoryId);
            if ($category && $category->admin_id !== $adminId) {
                return response()->json(['error' => 'У вас нет доступа к этой категории'], 403);
            }
            $query->where('category_id', $categoryId);
        }

        $equipment = $query->get();

        if ($request->ajax()) {
            return view('equipment._table', compact('equipment', 'categoryId'));
        }

        return view('equipment.index', compact('equipment', 'categoryId'));
    }

    public function create(Request $request)
    {
        $this->authorize('create projects');

        $user = auth()->user();
        $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;
        $categories = Category::where('admin_id', $adminId)->get();
        $categoryId = $request->query('category_id');

        if ($request->ajax()) {
            return response()->json([
                'categories' => $categories->map(function ($category) {
                    return ['id' => $category->id, 'name' => $category->name];
                }),
                'canViewPrices' => $user->hasPermissionTo('view prices'),
                'categoryId' => $categoryId
            ]);
        }

        return view('equipment.create', compact('categories', 'categoryId'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create projects');

        $user = auth()->user();
        $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;

        $barcodePath = null;

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'nullable|exists:categories,id,admin_id,' . $adminId,
                'description' => 'nullable|string',
                'price' => 'nullable|numeric|min:0',
                'length_cm' => 'nullable|string',
                'width_cm' => 'nullable|string',
                'height_cm' => 'nullable|string',
                'weight_kg' => 'nullable|string',
                'power_w' => 'nullable|string',
                'current_a' => 'nullable|string',
                'image' => 'nullable|image|max:2048',
            ]);

            Log::info('Создание оборудования', ['data' => $validated]);

            $data = $validated;
            $data['admin_id'] = $adminId;
            
            // Собираем характеристики из отдельных полей
            $data['specifications'] = $this->buildSpecificationsFromForm($request);
            
            $qrPath = 'qrcodes/' . $adminId . '/' . uniqid() . '.svg';

            // Генерируем QR-код с данными оборудования
            $qrData = [
                'id' => null, // Будет установлен после создания записи
                'name' => $request->name,
                'description' => $request->description,
                'specifications' => $this->buildSpecificationsFromForm($request)
            ];
            
            $qrContent = json_encode($qrData, JSON_UNESCAPED_UNICODE);
            Log::info('QR Content: ' . $qrContent);
            
            try {
                // Создаем папку для админа, если её нет
                $adminQrPath = 'qrcodes/' . $adminId;
                if (!Storage::exists('public/' . $adminQrPath)) {
                    Storage::makeDirectory('public/' . $adminQrPath);
                    Log::info('Created directory: ' . $adminQrPath);
                }
                
                $generator = new \Milon\Barcode\DNS2D();
                Log::info('Generator created successfully');
                
                $qrImage = $generator->getBarcodeSVG("123, asdasd", 'QRCODE');
                Log::info('QR Image generated, size: ' . strlen($qrImage));
                
                Storage::put('public/' . $qrPath, $qrImage);
                Log::info('QR Code saved to: ' . $qrPath);
                
                // Проверяем, что файл действительно создался
                if (Storage::exists('public/' . $qrPath)) {
                    $fileSize = Storage::size('public/' . $qrPath);
                    Log::info('File exists, size: ' . $fileSize . ' bytes');
                } else {
                    Log::error('File was not created!');
                }
            } catch (\Exception $e) {
                Log::error('QR Code generation failed: ' . $e->getMessage());
                Log::error('Exception trace: ' . $e->getTraceAsString());
                // Fallback - создаем простой QR-код
                $qrImage = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
                Storage::put('public/' . $qrPath, $qrImage);
            }
            $data['qrcode'] = $qrPath;

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('equipment', 'public');
            }

            $equipment = Equipment::create($data);

            // Обновляем QR-код с правильным ID
            $qrData = [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'description' => $equipment->description,
                'specifications' => $equipment->specifications
            ];
            
            $qrContent = json_encode($qrData, JSON_UNESCAPED_UNICODE);
            $generator = new \Milon\Barcode\DNS2D();
            $qrImage = $generator->getBarcodeSVG($qrContent, 'QRCODE');
            Storage::put('public/' . $equipment->qrcode, $qrImage);

            return response()->json([
                'success' => 'Оборудование добавлено.',
                'equipment' => [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'barcode' => Storage::url($equipment->barcode),
                    'price' => $equipment->price,
                    'category_id' => $equipment->category_id,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Ошибка валидации при создании оборудования', ['errors' => $e->errors()]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Ошибка сервера при создании оборудования', ['error' => $e->getMessage(), 'barcodePath' => $barcodePath]);
            if ($barcodePath && Storage::exists('public/' . $barcodePath)) {
                Storage::delete('public/' . $barcodePath);
            }
            return response()->json(['error' => 'Ошибка сервера: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $this->authorize('edit projects');

        $equipment = Equipment::findOrFail($id);
        $user = auth()->user();
        $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;

        if ($equipment->admin_id !== $adminId) {
            return response()->json(['error' => 'У вас нет доступа к этому оборудованию'], 403);
        }

        // Получаем характеристики в нужном формате для формы
        $specifications = $equipment->getSpecificationsForForm();
        
        $equipmentData = $equipment->toArray();
        $equipmentData['specifications'] = $specifications;

        Log::info('Equipment data for edit: ', $equipmentData);
        return response()->json($equipmentData);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit projects');

        $equipment = Equipment::findOrFail($id);
        $user = auth()->user();
        $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;

        if ($equipment->admin_id !== $adminId) {
            return response()->json(['error' => 'У вас нет доступа к этому оборудованию'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id,admin_id,' . $adminId,
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'length_cm' => 'nullable|string',
            'width_cm' => 'nullable|string',
            'height_cm' => 'nullable|string',
            'weight_kg' => 'nullable|string',
            'power_w' => 'nullable|string',
            'current_a' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        // Собираем характеристики из отдельных полей
        $data['specifications'] = $this->buildSpecificationsFromForm($request);
        
        $equipment->update($data);
        
        // Обновляем QR-код с новыми данными
        $qrData = [
            'id' => $equipment->id,
            'name' => $equipment->name,
            'description' => $equipment->description,
            'specifications' => $equipment->specifications
        ];
        
        $qrContent = json_encode($qrData, JSON_UNESCAPED_UNICODE);
        Log::info('Updating QR Code for equipment ' . $equipment->id . ' with content: ' . $qrContent);
        
        try {
            // Создаем папку для админа, если её нет
            $adminQrPath = 'qrcodes/' . $adminId;
            if (!Storage::exists('public/' . $adminQrPath)) {
                Storage::makeDirectory('public/' . $adminQrPath);
                Log::info('Created directory: ' . $adminQrPath);
            }
            
            $generator = new \Milon\Barcode\DNS2D();
            $qrImage = $generator->getBarcodeSVG($qrContent, 'QRCODE');
            Storage::put('public/' . $equipment->qrcode, $qrImage);
            Log::info('QR Code updated successfully for equipment ' . $equipment->id);
        } catch (\Exception $e) {
            Log::error('Failed to update QR Code for equipment ' . $equipment->id . ': ' . $e->getMessage());
        }
        
        if ($request->hasFile('image')) {
            if ($equipment->image) {
                Storage::delete('public/' . $equipment->image);
            }
            $equipment->image = $request->file('image')->store('equipment', 'public');
            $equipment->save();
        }

        return response()->json(['success' => 'Оборудование успешно обновлено']);
    }

    public function destroy($id)
    {
        $this->authorize('delete projects');

        $equipment = Equipment::findOrFail($id);
        $user = auth()->user();
        $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;

        if ($equipment->admin_id !== $adminId) {
            return response()->json(['error' => 'У вас нет доступа к этому оборудованию'], 403);
        }

        if ($equipment->image) {
            Storage::delete('public/' . $equipment->image);
        }
        if ($equipment->qrcode) {
            Storage::delete('public/' . $equipment->qrcode);
        }
        $equipment->delete();

        return response()->json(['success' => 'Оборудование успешно удалено']);
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
     * Собирает характеристики из отдельных полей формы
     */
    private function buildSpecificationsFromForm(Request $request): array
    {
        $specifications = [];
        
        $fields = [
            'length_cm' => 'length_cm',
            'width_cm' => 'width_cm', 
            'height_cm' => 'height_cm',
            'weight_kg' => 'weight_kg',
            'power_w' => 'power_w',
            'current_a' => 'current_a'
        ];

        foreach ($fields as $fieldName => $jsonKey) {
            $value = $request->input($fieldName);
            $normalizedValue = $this->normalizeSpecificationValue($value);
            if ($normalizedValue !== null) {
                $specifications[$jsonKey] = $normalizedValue;
            }
        }

        return $specifications;
    }
}
