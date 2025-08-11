<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
    public function index()
    {
        $query = Site::with('staff');

        if (!Auth::user()->hasRole('admin')) {
            $query->where('admin_id', Auth::user()->admin_id);
        } else {
            $query->where('admin_id', Auth::id());
        }

        $sites = $query->latest()->paginate(10);
        return view('sites.index', compact('sites'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'manager' => 'nullable|string|max:255',
            'access_mode' => 'nullable|in:documents,passes,none',
            'comment' => 'nullable|string',
            'staff' => 'array',
            'staff.*.name' => 'nullable|string|max:255',
            'staff.*.phone' => 'nullable|string|max:50',
            'staff.*.comment' => 'nullable|string',
        ]);

        $data['admin_id'] = Auth::user()->hasRole('admin') ? Auth::id() : Auth::user()->admin_id;
        $site = Site::create(array_diff_key($data, ['staff' => '']));

        if (!empty($data['staff'])) {
            foreach ($data['staff'] as $staff) {
                $site->staff()->create(array_filter($staff));
            }
        }

        $site->load('staff');
        return response()->json($site);
    }

    public function update(Request $request, Site $site)
    {
        if (!Auth::user()->hasRole('admin') && $site->admin_id != Auth::user()->admin_id) {
            abort(403, 'Доступ запрещен');
        }

        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'manager' => 'nullable|string|max:255',
            'access_mode' => 'nullable|in:documents,passes,none',
            'comment' => 'nullable|string',
            'staff' => 'array',
            'staff.*.name' => 'nullable|string|max:255',
            'staff.*.phone' => 'nullable|string|max:50',
            'staff.*.comment' => 'nullable|string',
        ]);

        $site->update(array_diff_key($data, ['staff' => '']));
        $site->staff()->delete();
        if (!empty($data['staff'])) {
            foreach ($data['staff'] as $staff) {
                $site->staff()->create(array_filter($staff));
            }
        }

        $site->load('staff');
        return response()->json($site);
    }

    public function destroy(Site $site)
    {
        if (!Auth::user()->hasRole('admin') && $site->admin_id != Auth::user()->admin_id) {
            abort(403, 'Доступ запрещен');
        }

        $site->delete();
        return response()->json(['deleted' => true]);
    }
}
