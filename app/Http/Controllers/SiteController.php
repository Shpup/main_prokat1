<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::with('staff')->latest()->paginate(10);
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

        $data['admin_id'] = Auth::id();
        $site = Site::create(array_diff_key($data, ['staff' => ''])); // Создаём только поля сайта

        if (!empty($data['staff'])) {
            foreach ($data['staff'] as $staff) {
                $site->staff()->create(array_filter($staff)); // Создаём сотрудников только с заполненными полями
            }
        }

        $site->load('staff');
        return response()->json($site);
    }

    public function update(Request $request, Site $site)
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

        $site->update(array_diff_key($data, ['staff' => ''])); // Обновляем только поля сайта
        $site->staff()->delete(); // Удаляем старых сотрудников
        if (!empty($data['staff'])) {
            foreach ($data['staff'] as $staff) {
                $site->staff()->create(array_filter($staff)); // Создаём новых сотрудников
            }
        }

        $site->load('staff');
        return response()->json($site);
    }

    public function destroy(Site $site)
    {
        $site->delete();
        return response()->json(['deleted' => true]);
    }
}
