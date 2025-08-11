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
                'specifications' => 'nullable|json',
                'image' => 'nullable|image|max:2048',
            ]);

            Log::info('Создание оборудования', ['data' => $validated]);

            $data = $validated;
            $data['admin_id'] = $adminId;
            $barcodeContent = $request->name . '-' . uniqid();
            $barcodePath = 'barcodes/' . uniqid() . '.png';


            $barcodeImage = 1;
            Storage::put('public/' . $barcodePath, $barcodeImage);
            $data['barcode'] = $barcodePath;

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('equipment', 'public');
            }

            $equipment = Equipment::create($data);

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

        Log::info('Equipment data for edit: ', $equipment->toArray());
        return response()->json($equipment);
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
            'specifications' => 'nullable|json',
            'image' => 'nullable|image|max:2048',
        ]);

        $equipment->update($request->all());
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
        if ($equipment->barcode) {
            Storage::delete('public/' . $equipment->barcode);
        }
        $equipment->delete();

        return response()->json(['success' => 'Оборудование успешно удалено']);
    }
}
