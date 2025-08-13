<?php
namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::where('admin_id', Auth::id());

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%')
                    ->orWhere('country', 'ilike', '%' . $search . '%')
                    ->orWhere('comment', 'ilike', '%' . $search . '%');
            });
        }

        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');
        $query->orderBy($sort, $direction);

        $companies = $query->get();

        if ($request->ajax()) {
            $view = view('companies.partials.table', compact('companies'))->render();
            return response()->json([
                'view' => $view,
                'companies' => $companies,
            ]);
        }

        return view('companies.index', compact('companies'));
    }

    public function show(Company $company)
    {
        if ($company->admin_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }
        return view('companies.show', compact('company'));
    }

    public function store(Request $request)
    {
        \Log::info('Store request data:', $request->all());
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|in:ip,ur,fl',
            'country' => 'nullable|string|max:255',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'accounting_method' => 'nullable|in:osn_inclusive,osn_exclusive,usn_inclusive,usn_exclusive',
            'comment' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        $data['admin_id'] = Auth::id();
        if (!empty($data['is_default'])) {
            Company::where('admin_id', Auth::id())->update(['is_default' => false]);
        }

        Company::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, Company $company)
    {
        \Log::info('Update request data:', $request->all());
        if ($company->admin_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|in:ip,ur,fl',
            'country' => 'nullable|string|max:255',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'accounting_method' => 'nullable|in:osn_inclusive,osn_exclusive,usn_inclusive,usn_exclusive',
            'comment' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        if (!empty($data['is_default'])) {
            Company::where('admin_id', Auth::id())->update(['is_default' => false]);
        }

        $company->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(Company $company)
    {
        if ($company->admin_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        $company->delete();
        return response()->json(['success' => true]);
    }

    public function updateLegal(Request $request, Company $company)
    {
        \Log::info('Update legal request data:', $request->all());
        if ($company->admin_id !== Auth::id()) {
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

        $company->update($data);
        return response()->json(['success' => true]);
    }

    public function updateTax(Request $request, Company $company)
    {
        \Log::info('Update tax request data:', $request->all());
        if ($company->admin_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        $data = $request->validate([
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'accounting_method' => 'nullable|in:osn_inclusive,osn_exclusive,usn_inclusive,usn_exclusive',
        ]);

        $company->update($data);
        return response()->json(['success' => true]);
    }

    public function updateBasic(Request $request, Company $company)
    {
        \Log::info('Update basic request data:', $request->all());
        if ($company->admin_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'comment' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        if (!empty($data['is_default'])) {
            Company::where('admin_id', Auth::id())->update(['is_default' => false]);
        }

        $company->update($data);
        return response()->json(['success' => true]);
    }
}
