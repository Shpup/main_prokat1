<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create projects');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $user = auth()->user();
        $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;

        if ($validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);
            if ($parent && $parent->admin_id !== $adminId) {
                return response()->json(['error' => 'У вас нет доступа к этой родительской категории'], 403);
            }
        }

        $category = Category::create(array_merge($validated, [
            'admin_id' => $adminId,
        ]));

        $category->load('admin');

        return response()->json([
            'success' => 'Категория создана',
            'category' => $category
        ]);
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete projects');

        $user = auth()->user();
        $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;

        if ($category->admin_id !== $adminId) {
            return response()->json(['error' => 'У вас нет прав на удаление этой категории'], 403);
        }

        try {
            $category->children()->delete();
            $category->equipment()->delete();
            $category->delete();

            return response()->json(['success' => 'Категория и всё содержимое успешно удалены']);
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении категории: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка при удалении: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        $user = auth()->user();
        $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;

        if ($category->admin_id !== $adminId) {
            return response()->json(['error' => 'У вас нет доступа к этой категории'], 403);
        }

        return response()->json(['name' => $category->name]);
    }
}
