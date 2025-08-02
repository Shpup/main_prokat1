<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles', 'permissions')->get();
        $permissions = Permission::all();

        return view('users.index', compact('users', 'permissions'));
    }

    public function updatePermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'array',
        ]);

        $permissions = $request->input('permissions', []);
        $user->syncPermissions($permissions);

        return response()->json(['success' => 'Разрешения обновлены']);
    }
}
