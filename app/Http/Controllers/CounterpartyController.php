<?php
namespace App\Http\Controllers;

use App\Models\Counterparty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CounterpartyController extends Controller
{
    public function index(Request $request)
    {
        $query = Counterparty::where('admin_id', Auth::id());

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%')
                    ->orWhere('code', 'ilike', '%' . $search . '%')
                    ->orWhere('comment', 'ilike', '%' . $search . '%');
            });
        }

        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');
        $query->orderBy($sort, $direction);

        $counterparties = $query->get();
        $managers = User::where('role', 'manager')->get();

        if ($request->ajax()) {
            $view = view('counterparties.partials.table', compact('counterparties'))->render();
            return response()->json([
                'view' => $view,
                'counterparties' => $counterparties,
            ]);
        }

        return view('counterparties.index', compact('counterparties', 'managers'));
    }

    public function show(Counterparty $counterparty)
    {
        if ($counterparty->admin_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }
        return view('counterparties.show', compact('counterparty'));
    }

    public function store(Request $request)
    {
        \Log::info('Store counterparty request data:', $request->all());
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'code' => 'nullable|string|max:50',
            'status' => 'nullable|in:new,verified,dangerous',
            'actual_address' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'is_available_for_sublease' => 'nullable|boolean',
        ]);

        $data['admin_id'] = Auth::id();
        Counterparty::create($data);
        return response()->json(['success' => true]);
    }
    public function edit(Counterparty $counterparty)
    {
        $managers = User::where('role', 'manager')->get();
        return view('counterparties.edit', compact('counterparty', 'managers'));
    }

    public function updateBasic(Request $request, Counterparty $counterparty)
    {
        \Log::info('Update basic counterparty request data:', $request->all());
        if ($counterparty->admin_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'code' => 'nullable|string|max:50',
            'status' => 'nullable|in:new,verified,dangerous',
            'actual_address' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'is_available_for_sublease' => 'nullable|boolean',
        ]);

        $counterparty->update($data);
        return response()->json(['success' => true]);
    }

    public function updateLegal(Request $request, Counterparty $counterparty)
    {
        \Log::info('Update legal counterparty request data:', $request->all());
        if ($counterparty->admin_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        $validationRules = [
            'type' => 'required|in:ip,ur,fl',
            'registration_country' => 'nullable|string|max:255',
            'inn' => 'nullable|string|max:20',
            'full_name' => 'nullable|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'legal_address' => 'nullable|string|max:255',
            'postal_address' => 'nullable|string|max:255',
            'kpp' => 'nullable|string|max:20',
            'ogrn' => 'nullable|string|max:20',
            'okpo' => 'nullable|string|max:20',
            'bik' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:255',
            'correspondent_account' => 'nullable|string|max:20',
            'checking_account' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'registration_address' => 'nullable|string|max:255',
            'ogrnip' => 'nullable|string|max:20',
            'certificate_number' => 'nullable|string|max:20',
            'certificate_date' => 'nullable|date',
            'card_number' => 'nullable|string|max:20',
            'snils' => 'nullable|string|max:20',
            'passport_data' => 'nullable|string|max:255',
        ];

        $data = $request->validate($validationRules);

        $counterparty->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(Counterparty $counterparty)
    {
        if ($counterparty->admin_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        $counterparty->delete();
        return response()->json(['success' => true]);
    }
}
