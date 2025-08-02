<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    /**
     * Отображает список проектов и календарь.
     */
    public function index(): View
    {
        $projects = Project::with('manager')->get();
        return view('projects.index', compact('projects'));
    }

    /**
     * Показывает форму создания проекта (доступно только админу).
     */
    public function create(): View
    {
        $this->authorize('create projects');
        $managers = \App\Models\User::role('manager')->get();
        return view('projects.create', compact('managers'));
    }

    /**
     * Сохраняет новый проект.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create projects');
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $project = Project::create($request->all());

        if ($request->ajax()) {
            return response()->json(['success' => 'Проект создан.', 'project' => $project]);
        }

        return redirect()->route('dashboard')->with('success', 'Проект создан.');
    }

    /**
     * Отображает детали проекта и смету.
     */
    public function show(Project $project): View
    {
        $project->load(['equipment', 'manager']);
        $availableEquipment = Equipment::whereDoesntHave('projects', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })->get();
        return view('projects.show', compact('project', 'availableEquipment'));
    }

    /**
     * Добавляет оборудование в проект.
     */
    public function addEquipment(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('edit projects');
        $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'status' => 'required|in:on_stock,assigned,used',
        ]);

        $project->equipment()->attach($request->equipment_id, ['status' => $request->status]);
        return redirect()->route('projects.show', $project)->with('success', 'Оборудование добавлено в проект.');
    }
}
