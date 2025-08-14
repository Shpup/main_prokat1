<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\WorkInterval;

class ProjectController extends Controller
{
    /**
     * Отображает список проектов и календарь.
     */
    public function index(): View
    {
        $projects = Project::with('manager')->where('admin_id',auth()->id())->get();
        return view('projects.index', compact('projects'));
    }
    public function table(Request $request)
    {
        $search = $request->query('search', '');
        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');

        // Поля для сортировки
        $sortableColumns = ['name', 'description', 'start_date', 'end_date', 'status'];
        $sort = in_array($sort, $sortableColumns) ? $sort : 'name';
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        // Базовый запрос
        $query = Project::query()
            ->join('users', 'projects.manager_id', '=', 'users.id')
            ->select('projects.*', 'users.name as manager_name')
            ->where('projects.admin_id', Auth::id());

        // Фильтрация по всем столбцам
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('projects.name', 'ilike', '%' . $search . '%')
                    ->orWhere('projects.description', 'ilike', '%' . $search . '%')
                    ->orWhere('users.name', 'ilike', '%' . $search . '%')
                    ->orWhere('projects.start_date', 'ilike', '%' . $search . '%')
                    ->orWhere('projects.end_date', 'ilike', '%' . $search . '%')
                    ->orWhere('projects.status', 'ilike', '%' . $search . '%');
            });
        }

        // Сортировка
        if ($sort === 'manager') {
            $query->orderBy('users.name', $direction);
        } else {
            $query->orderBy('projects.' . $sort, $direction);
        }

        // Пагинация
        $projects = $query->paginate(10);

        // Для AJAX-запросов возвращаем данные и HTML таблицы
        if ($request->ajax()) {
            return response()->json([
                'projects' => $projects->items(),
                'view' => view('projects.partials.table', compact('projects'))->render()
            ]);
        }

        // Для обычного запроса возвращаем полную страницу
        return view('projects.table', compact('projects'));
    }

    public function updateStatus(Request $request, Project $project): JsonResponse
    {


        $this->authorize('edit projects');

        $validated = $request->validate([
            'status' => 'required|in:new,active,completed,cancelled',
        ]);

        try {
            $updated = $project->update(['status' => $validated['status']]);



            // Проверка текущего статуса в базе
            $project->refresh();


            if ($updated) {
                return response()->json([
                    'success' => 'Статус проекта обновлён.',
                    'status' => $project->status,
                ]);
            } else {

                return response()->json([
                    'error' => 'Не удалось обновить статус проекта.',
                ], 500);
            }
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Ошибка при обновлении статуса: ' . $e->getMessage(),
            ], 500);
        }
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
        $validated=$request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:new,active,completed,cancelled' // добавлено
        ]);

        $validated['admin_id'] = auth()->id();

        $project = Project::create($validated);

        return response()->json(['success' => 'Проект создан.', 'project' => $project]);



        //return redirect()->route('dashboard')->with('success', 'Проект создан.');
    }
    public function equipmentList(Request $request, Project $project)
    {
        $categoryId = $request->query('category_id');

        $equipment = Equipment::when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->with('projects') // чтобы видеть, где уже прикреплено
            ->get();

        // Возвращаем ТОЛЬКО HTML-фрагмент
        return view('projects.partials.equipment_table', [
            'equipment' => $equipment,
            'project'   => $project,
        ])->render();
    }
    public function attachEquipment(Project $project, Equipment $equipment): JsonResponse
    {
        $this->authorize('edit projects');

        // Проверка занятости по пересечению дат
        $overlap = $equipment->projects()
            ->where(function($q) use ($project) {
                $q->whereNull('projects.end_date')
                    ->orWhere(function($w) use ($project) {
                        $w->whereDate('projects.start_date', '<=', $project->end_date ?? $project->start_date)
                            ->where(function($z) use ($project) {
                                $z->whereNull('projects.end_date')
                                    ->orWhereDate('projects.end_date', '>=', $project->start_date);
                            });
                    });
            })
            ->where('projects.id', '!=', $project->id)
            ->exists();

        if ($overlap) {
            return response()->json(['error' => 'Оборудование уже занято в пересекающемся проекте'], 422);
        }

        // Прикрепляем
        $project->equipment()->syncWithoutDetaching([
            $equipment->id => ['status' => 'assigned']
        ]);

        return response()->json(['success' => 'Оборудование прикреплено']);
    }

    public function detachEquipment(Project $project, Equipment $equipment): JsonResponse
    {
        $this->authorize('edit projects');

        $project->equipment()->detach($equipment->id);

        return response()->json(['success' => 'Оборудование убрано из проекта']);
    }

    /**
     * Отображает детали проекта и смету.
     */

    /**
     * Отображает детали проекта и смету.
     */
    public function show(Project $project): View
    {
        $project->load(['equipment', 'manager', 'staff']);
        $availableEquipment = Equipment::whereDoesntHave('projects', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })->get();
        return view('projects.show', compact('project', 'availableEquipment'));
    }

    // === Staff attach/detach and summary (moved from routes/web.php) ===
    public function attachStaff(Request $request, Project $project, User $user): JsonResponse
    {
        $project->staff()->syncWithoutDetaching([$user->id]);
        $rateType = $request->input('rate_type');
        $rate     = $request->input('rate');
        if (in_array($rateType, ['hour','project'], true)) {
            WorkInterval::where('employee_id', $user->id)
                ->where('project_id', $project->id)
                ->where('type', 'busy')
                ->update([
                    'hour_rate'    => $rateType === 'hour' ? ($rate !== null ? (float)$rate : null) : null,
                    'project_rate' => $rateType === 'project' ? ($rate !== null ? (float)$rate : null) : null,
                ]);

            // Если у сотрудника по проекту нет ни одного busy-интервала,
            // закрепим ставку placeholder'ом 00:00–00:00, чтобы «за проект» сохранилось
            $hasBusy = WorkInterval::where('employee_id', $user->id)
                ->where('project_id', $project->id)
                ->where('type', 'busy')
                ->exists();
            if (!$hasBusy) {
                $placeholderDate = $project->start_date ? date('Y-m-d', strtotime((string)$project->start_date)) : date('Y-m-d');
                WorkInterval::create([
                    'employee_id' => $user->id,
                    'project_id'  => $project->id,
                    'date'        => $placeholderDate,
                    'start_time'  => '00:00',
                    'end_time'    => '00:00',
                    'type'        => 'busy',
                    'hour_rate'   => $rateType === 'hour' ? ($rate !== null ? (float)$rate : null) : null,
                    'project_rate'=> $rateType === 'project' ? ($rate !== null ? (float)$rate : null) : null,
                ]);
            }
        }
        return response()->json(['success'=>true]);
    }

    public function summary(Project $project, User $user): JsonResponse
    {
        $ivs = WorkInterval::where('employee_id', $user->id)
            ->where('project_id', $project->id)
            ->where('type', 'busy')
            ->get(['start_time','end_time','hour_rate','project_rate']);
        $minutes = 0; $hourRate = null; $projectRate = null;
        foreach ($ivs as $iv) {
            $s = strtotime((string)$iv->start_time); $e = strtotime((string)$iv->end_time);
            if ($e > $s) { $minutes += ($e - $s)/60; }
            if (!is_null($iv->project_rate)) { $projectRate = (float)$iv->project_rate; }
            if (is_null($hourRate) && !is_null($iv->hour_rate)) { $hourRate = (float)$iv->hour_rate; }
        }
        $sum = null; $rateType = null; $rate = null;
        if (!is_null($projectRate)) { $sum = $projectRate; $rateType='project'; $rate=$projectRate; }
        elseif (!is_null($hourRate)) { $sum = $hourRate*($minutes/60.0); $rateType='hour'; $rate=$hourRate; }
        return response()->json(['success'=>true, 'sum'=>$sum, 'minutes'=>$minutes, 'rate_type'=>$rateType, 'rate'=>$rate]);
    }

    public function detachStaff(Project $project, User $user): JsonResponse
    {
        $project->staff()->detach($user->id);
        return response()->json(['success'=>true]);
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
    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('edit projects');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:new,active,completed,cancelled'
        ]);

        $project->update($validated);

        return response()->json(['success' => 'Проект обновлен.', 'project' => $project]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete projects');
        $project->delete();
        return response()->json(['success' => 'Проект удален']);
    }
}
