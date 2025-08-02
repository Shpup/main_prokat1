<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class ManagerController extends Controller
{
    /**
     * Показывает список менеджеров (доступно только админу).
     */
    public function index(): View
    {
        $this->authorize('create projects');
        $managers = User::role('manager')->get();
        return view('managers.index', compact('managers'));
    }

    /**
     * Показывает форму создания менеджера.
     */
    public function create(): View
    {
        $this->authorize('create projects');
        return view('managers.create');
    }

    /**
     * Сохраняет нового менеджера.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create projects');
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('manager');
        $user->subscription()->create(['is_active' => false]);

        if ($request->ajax()) {
            return response()->json(['success' => 'Менеджер создан.', 'manager' => $user]);
        }

        return redirect()->route('managers.index')->with('success', 'Менеджер создан.');
    }
}
