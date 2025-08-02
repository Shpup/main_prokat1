<?php

namespace App\Http\Controllers;

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
        // Временное отключение authorize для отладки
        // $this->authorize('view projects');

        Log::info('Доступ к /equipment', ['user' => auth()->check() ? auth()->user()->email : 'неавторизован']);

        $categoryId = $request->query('category_id');
        $equipment = Equipment::when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->with('category', 'projects')
            ->get();

        return view('equipment.index', compact('equipment', 'categoryId'));
    }
    public function edit($id)
    {
        $equipment = Equipment::with('category')->findOrFail($id);
        \Log::info('Equipment data for edit: ', $equipment->toArray()); // Добавим лог для отладки
        return response()->json($equipment);
    }

    public function update(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'specifications' => 'nullable|json',
            'image' => 'nullable|image|max:2048',
        ]);

        $equipment->update($request->all());
        if ($request->hasFile('image')) {
            $equipment->image = $request->file('image')->store('equipment', 'public');
            $equipment->save();
        }

        return response()->json(['success' => 'Оборудование успешно обновлено']);
    }
    public function destroy($id)
    {
        $equipment = Equipment::findOrFail($id);
        $equipment->delete();

        return response()->json(['success' => 'Оборудование успешно удалено']);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create projects');

        $barcodePath = null;

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
                'price' => 'nullable|numeric|min:0',
                'specifications' => 'nullable|json',
                'image' => 'nullable|image|max:2048',
            ]);

            Log::info('Создание оборудования', ['data' => $validated]);

            $data = $validated;
            $barcodeContent = $request->name . '-' . uniqid();
            $barcodePath = 'barcodes/' . uniqid() . '.png';

            $generator = new BarcodeGeneratorPNG();
            $barcodeImage = $generator->getBarcode($barcodeContent, $generator::TYPE_CODE_128);
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
}
