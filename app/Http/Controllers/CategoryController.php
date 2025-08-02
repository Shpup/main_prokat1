<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'user_id' => 'nullable|exists:users,id', // Для совместимости
        ]);

        $category = Category::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        // Загружаем связанные данные для корректного отображения
        $category->load('user');

        return response()->json([
            'success' => 'Категория создана',
            'category' => $category
        ]);
    }

    public function destroy(Category $category)
    {
        try {
            if (auth()->id() !== $category->user_id && !auth()->user()->hasRole('admin')) {
                return response()->json(['error' => 'У вас нет прав на удаление этой категории'], 403);
            }

            $category->children()->delete();
            $category->equipment()->delete();
            $category->delete();

            return response()->json(['success' => 'Категория и всё содержимое успешно удалены']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при удалении: ' . $e->getMessage()], 500);
        }
    }
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json(['name' => $category->name]);
    }
}
