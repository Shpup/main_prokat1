<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles', 'permissions')
            ->where('admin_id', Auth::id());

        // Сортировка
        $sortColumn = $request->input('sort', 'id');
        $sortDirection = $request->input('direction', 'asc');
        $validColumns = ['id', 'name', 'email', 'roles_count'];

        if (in_array($sortColumn, $validColumns)) {
            if ($sortColumn === 'roles_count') {
                $query->withCount('roles')->orderBy('roles_count', $sortDirection);
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        }

        // Фильтрация с игнорированием регистра
        if ($request->filled('name')) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->input('name')) . '%']);
        }
        if ($request->filled('email')) {
            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($request->input('email')) . '%']);
        }
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->input('role'));
            });
        }

        $users = $query->get();
        $permissions = Permission::all();
        $roles = Role::all()->pluck('name')->toArray();

        return view('users.index', compact('users', 'permissions', 'roles', 'sortColumn', 'sortDirection'));
    }

    public function updatePermissions(Request $request, User $user)
    {
        if ($user->admin_id !== Auth::id()) {
            Log::warning('Unauthorized updatePermissions attempt', [
                'user_id' => $user->id,
                'auth_id' => Auth::id(),
            ]);
            return response()->json(['error' => 'У вас нет прав для изменения разрешений этого пользователя.'], 403);
        }

        $request->validate([
            'permissions' => 'array',
        ]);

        $permissions = $request->input('permissions', []);
        Log::info('updatePermissions called', [
            'user_id' => $user->id,
            'permissions' => $permissions,
            'auth_id' => Auth::id(),
        ]);

        $user->syncPermissions($permissions);

        return response()->json(['success' => 'Разрешения обновлены']);
    }
}
