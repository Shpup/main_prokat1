<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Только аутентификация
    }

    public function index(Request $request)
    {
        if (!\Auth::user()->hasRole(['admin', 'manager'])) {
            abort(403, 'Доступ запрещен');
        }

        $query = Client::query();

        if (!\Auth::user()->hasRole('admin')) {
            $query->where('admin_id', \Auth::user()->admin_id);
        }

        // Поиск (PostgreSQL ILIKE)
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Сортировка — защищаемся вайтлистом
        $allowedSort = [
            'name',
            'phone',
            'email',
            'discount_equipment',
            'discount_services',
            'discount_materials',
            'blacklisted',
            'created_at',
            'updated_at',
        ];
        $sortColumn = $request->input('sort', 'name');
        if (!in_array($sortColumn, $allowedSort, true)) {
            $sortColumn = 'name';
        }
        $sortDirection = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        // Пагинация на 15 строк (как у тебя)
        $clients = $query->paginate(15);

        if ($request->ajax()) {
            return response()->json([
                // оставлю, если вдруг захочешь использовать для пагинации на фронте
                'clients' => $clients,
                // главное — HTML строк таблицы:
                'view' => view('clients.partials.table', compact('clients'))->render(),
            ]);
        }

        return view('clients.index', compact('clients'));
    }


    public function store(Request $request)
    {
        if (!Auth::user()->hasRole(['admin', 'manager'])) {
            abort(403, 'Доступ запрещен');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:clients,email',
            'discount_equipment' => 'nullable|numeric|min:0|max:100',
            'discount_services' => 'nullable|numeric|min:0|max:100',
            'discount_materials' => 'nullable|numeric|min:0|max:100',
            'blacklisted' => 'boolean',
        ]);

        $validated['admin_id'] = Auth::user()->hasRole('admin') ? Auth::id() : Auth::user()->admin_id;

        Client::create($validated);

        return redirect()->route('clients.index')->with('success', 'Клиент добавлен');
    }

    public function edit(Client $client)
    {
        if (!Auth::user()->hasRole(['admin', 'manager'])) {
            abort(403, 'Доступ запрещен');
        }

        if (!Auth::user()->hasRole('admin') && $client->admin_id != Auth::user()->admin_id) {
            abort(403, 'Доступ запрещен');
        }

        return response()->json($client);
    }

    public function update(Request $request, Client $client)
    {
        if (!Auth::user()->hasRole(['admin', 'manager'])) {
            abort(403, 'Доступ запрещен');
        }

        if (!Auth::user()->hasRole('admin') && $client->admin_id != Auth::user()->admin_id) {
            abort(403, 'Доступ запрещен');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:clients,email,' . $client->id,
            'discount_equipment' => 'nullable|numeric|min:0|max:100',
            'discount_services' => 'nullable|numeric|min:0|max:100',
            'discount_materials' => 'nullable|numeric|min:0|max:100',
            'blacklisted' => 'boolean',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', 'Клиент обновлен');
    }

    public function destroy(Client $client)
    {
        if (!Auth::user()->hasRole(['admin', 'manager'])) {
            abort(403, 'Доступ запрещен');
        }

        if (!Auth::user()->hasRole('admin') && $client->admin_id != Auth::user()->admin_id) {
            abort(403, 'Доступ запрещен');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Клиент удален');
    }
}
