<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Estimate;
use App\Models\Client;
use App\Models\Site;
use App\Models\Company;
use App\Models\Equipment;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\WorkInterval;
use PDF;
use App\Exports\EstimateExport;
use Maatwebsite\Excel\Facades\Excel;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $projects = Project::with('manager')->where('admin_id', $user->id)->where('status', '!=', 'cancelled')->get();
        } else {
            $projects = Project::with('manager')
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($user) {
                    $query->where('manager_id', $user->id)
                        ->orWhereHas('staff', function ($staffQuery) use ($user) {
                            $staffQuery->where('user_id', $user->id);
                        });
                })
                ->get();
        }


        if ($request->ajax()) {
            return response()->json($projects);
        }

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

        $user = auth()->user();

        // Базовый запрос в зависимости от роли
        if ($user->hasRole('admin')) {
            // Админ видит все проекты, которые он создал (admin_id), кроме отмененных
            $query = Project::query()
                ->join('users', 'projects.manager_id', '=', 'users.id')
                ->where('projects.admin_id', $user->id)
                ->where('projects.status', '!=', 'cancelled')
                ->select('projects.*', 'users.name as manager_name');
        } else {
            // Менеджер и обычный пользователь видят проекты, на которые они назначены, кроме отмененных
            $query = Project::query()
                ->join('users', 'projects.manager_id', '=', 'users.id')
                ->where('projects.status', '!=', 'cancelled')
                ->where(function ($subQuery) use ($user) {
                    $subQuery->where('projects.manager_id', $user->id)
                        ->orWhereHas('staff', function ($staffQuery) use ($user) {
                            $staffQuery->where('user_id', $user->id);
                        });
                })
                ->select('projects.*', 'users.name as manager_name');
        }

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
            'status' => 'required|in:new,active,completed,cancelled'
        ]);

        $project->update(['status' => $validated['status']]);

        return response()->json(['success' => true, 'status' => $validated['status']]);
    }
    public function exportExcel(Estimate $estimate)
    {
        return Excel::download(
            new EstimateExport($estimate),
            'estimate_' . $estimate->id . '.xlsx'
        );
    }
    public function show(Project $project): View
    {

        $estimates = $project->estimates;
        if ($estimates->isEmpty()) {
            // Создаём первую смету автоматически
            $estimate = Estimate::create([
                'project_id' => $project->id,
                'name' => 'Смета 1',
                'company_id' => Company::where('admin_id', auth()->id())->where('is_default', true)->first()?->id,
            ]);
            $estimates = collect([$estimate]);
        }

        // Для каждой сметы рассчитываем
        $estimates = $estimates->map(function ($est) {
            $est->calculated = $est->getEstimate();
            return $est;
        });

        $clients = Client::where('admin_id', auth()->id())->get();
        $companies = Company::where('admin_id', auth()->id())->get();
        $defaultCompany = $companies->where('is_default', true)->first();
        $managers = User::where('admin_id', auth()->id())->get();
        $sites = Site::where('admin_id', auth()->id())->get();

        // Определяем текущую смету для sub-tab
        $estId = request('est_id', $estimates->first()->id);
        $currentEstimate = $estimates->firstWhere('id', $estId) ?? $estimates->first();

        return view('projects.show', compact('project', 'estimates', 'currentEstimate', 'clients', 'companies', 'defaultCompany', 'managers', 'sites'));
    }
    public function getSites(Request $request): JsonResponse
    {
        $clientId = $request->query('client_id');
        $sites = Site::where('admin_id', auth()->id())->get();
        return response()->json($sites);
    }

    public function createEstimate(Request $request, Project $project): JsonResponse
    {
        $this->authorize('edit projects');
        if ($project->status === 'completed') {
            return response()->json(['error' => 'Проект завершён, нельзя добавлять сметы'], 403);
        }
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $estimate = Estimate::create([
            'project_id' => $project->id,
            'name' => $validated['name'],
            'company_id' => Company::where('admin_id', auth()->id())->where('is_default', true)->first()?->id,
        ]);
        return response()->json(['success' => true, 'estimate' => $estimate, 'redirect' => route('projects.show', ['project' => $project, 'tab' => 'estimate', 'est_id' => $estimate->id])]);
    }

    public function updateEstimate(Request $request, Estimate $estimate): JsonResponse
    {
        $this->authorize('edit projects');
        if ($estimate->project->status === 'completed') {
            return response()->json(['error' => 'Смета фиксирована для завершённого проекта'], 403);
        }
        $validated = $request->validate([
            'delivery_cost' => 'nullable|numeric|min:0',
            'client_id' => 'nullable|exists:clients,id',
            'company_id' => 'nullable|exists:companies,id',
        ]);
        $estimate->update($validated);
        $calculated = $estimate->getEstimate();
        return response()->json(['success' => true, 'estimate' => $calculated]);
    }

    public function deleteEstimate(Estimate $estimate): JsonResponse
    {
        $this->authorize('delete projects');
        if ($estimate->project->status === 'completed') {
            return response()->json(['error' => 'Нельзя удалять смету завершённого проекта'], 403);
        }
        $estimate->delete();
        return response()->json(['success' => true]);
    }

    public function exportEstimate(Estimate $estimate)
    {
        $calculated = $estimate->getEstimate();
        $project = $estimate->project;
        $pdf = PDF::loadView('projects.estimate_pdf', compact('project', 'estimate', 'calculated'));
        return $pdf->download('estimate_' . $estimate->id . '.pdf');
    }

    public function getCatalog()
    {
        $user = auth()->user();
        $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;
        $categories = Category::where('admin_id', $adminId)->whereNull('parent_id')->with('children.equipment')->get();

        $tree = $this->buildCatalogTree($categories);
        return response()->json($tree);
    }

    private function buildCatalogTree($cats)
    {
        $tree = [];
        foreach ($cats as $cat) {
            $sub = $cat->children ? $this->buildCatalogTree($cat->children) : [];
            $eqGroup = $cat->equipment->groupBy('name')->map(function ($group) {
                return [
                    'id' => $group->first()->id,
                    'qty' => $group->count(),
                    'price' => $group->first()->price,
                    'is_consumable' => $group->first()->is_consumable
                ];
            })->toArray();
            $tree[$cat->name] = ['sub' => $sub, 'equipment' => $eqGroup];
        }
        return $tree;
    }

    public function addToEstimate(Request $request, Estimate $estimate): JsonResponse
    {
        $this->authorize('edit projects');

        if ($estimate->project->status === 'completed') {
            return response()->json(['error' => 'Нельзя добавлять оборудование в смету завершённого проекта'], 403);
        }

        try {
            // Валидация входящих данных
            $validated = $request->validate([
                'equipment_id' => 'required|integer|exists:equipment,id',
                'quantity' => 'required|integer|min:1',
                'status' => 'required|in:on_stock,assigned,used',
                'coefficient' => 'nullable|numeric|min:0.1',
                'discount' => 'nullable|numeric|min:0|max:100'
            ]);

            // Получаем оборудование без глобального скоупа
            $equipment = Equipment::withoutGlobalScope('admin')->findOrFail($validated['equipment_id']);

            // Проверяем права доступа к оборудованию
            $user = auth()->user();
            $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;
            if ($equipment->admin_id != $adminId) {
                return response()->json(['error' => 'У вас нет доступа к этому оборудованию'], 403);
            }

            // Добавляем оборудование
            $estimate->attachEquipment(
                $validated['equipment_id'],
                $validated['quantity'],
                $validated['status'],
                $validated['coefficient'] ?? 1.0,
                $validated['discount'] ?? 0
            );

            // Пересчитываем смету
            $calculated = $estimate->getEstimate();

            // Получаем путь категории
            $categoryPath = $this->getCategoryPath($equipment->category_id);

            // Формируем данные для нового оборудования
            $newEquipment = [
                'id' => $equipment->id,
                'name' => $equipment->name ?? 'Без названия',
                'quantity' => $validated['quantity'],
                'price' => (float) ($equipment->price ?? 0),
                'coefficient' => (float) ($validated['coefficient'] ?? 1.0),
                'discount' => (float) ($validated['discount'] ?? 0),
                'status' => $validated['status'],
                'category_path' => $categoryPath,
                'is_consumable' => $equipment->is_consumable ?? false,
                'sum' => (float) ($equipment->price ?? 0) * ($validated['coefficient'] ?? 1.0) * $validated['quantity'],
                'after_discount' => (float) ($equipment->price ?? 0) * ($validated['coefficient'] ?? 1.0) * $validated['quantity'] * (1 - (($validated['discount'] ?? 0) / 100))
            ];

            return response()->json([
                'success' => true,
                'estimate_id' => $estimate->id,
                'new_equipment' => $newEquipment,
                'calculated' => [
                    'equipment' => [
                        'total' => $calculated['equipment']['total'] ?? 0,
                        'after_disc' => $calculated['equipment']['after_disc'] ?? 0,
                        'discount' => $calculated['equipment']['discount'] ?? 0
                    ],
                    'materials' => [
                        'total' => $calculated['materials']['total'] ?? 0,
                        'after_disc' => $calculated['materials']['after_disc'] ?? 0,
                        'discount' => $calculated['materials']['discount'] ?? 0
                    ],
                    'services' => [
                        'total' => $calculated['services']['total'] ?? 0,
                        'after_disc' => $calculated['services']['after_disc'] ?? 0,
                        'discount' => $calculated['services']['discount'] ?? 0
                    ],
                    'subtotal' => $calculated['subtotal'] ?? 0,
                    'tax' => $calculated['tax'] ?? 0,
                    'total' => $calculated['total'] ?? 0,
                    'tax_method' => $calculated['tax_method'] ?? 'none'
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed in addToEstimate', ['errors' => $e->errors(), 'input' => $request->all()]);
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error in addToEstimate', ['message' => $e->getMessage(), 'input' => $request->all()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function removeFromEstimate(Request $request, Estimate $estimate): JsonResponse
    {
        $this->authorize('edit projects');

        if ($estimate->project->status === 'completed') {
            return response()->json(['error' => 'Нельзя удалять оборудование из сметы завершённого проекта'], 403);
        }

        try {
            $validated = $request->validate([
                'equipment_id' => 'required|integer|exists:equipment,id',
            ]);

            // Удаляем оборудование из сметы
            $estimate->detachEquipment($validated['equipment_id']);

            // Пересчитываем смету
            $calculated = $estimate->getEstimate();

            return response()->json([
                'success' => true,
                'calculated' => $calculated
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in removeFromEstimate', ['message' => $e->getMessage(), 'input' => $request->all()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function updateEquipmentPivot(Request $request, Estimate $estimate): JsonResponse
    {
        $this->authorize('edit projects');

        if ($estimate->project->status === 'completed') {
            return response()->json(['error' => 'Нельзя редактировать оборудование в смете завершённого проекта'], 403);
        }

        try {
            $validated = $request->validate([
                'equipment_id' => 'required|integer|exists:equipment,id',
                'quantity' => 'nullable|integer|min:1',
                'price' => 'nullable|numeric|min:0',
                'coefficient' => 'nullable|numeric|min:0.1',
                'discount' => 'nullable|numeric|min:0|max:100'
            ]);

            $equipment = Equipment::withoutGlobalScope('admin')->findOrFail($validated['equipment_id']);
            $user = auth()->user();
            $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;
            if ($equipment->admin_id != $adminId) {
                return response()->json(['error' => 'У вас нет доступа к этому оборудованию'], 403);
            }

            $pivotData = [];
            if (isset($validated['quantity'])) {
                $pivotData['quantity'] = $validated['quantity'];
            }
            if (isset($validated['coefficient'])) {
                $pivotData['coefficient'] = $validated['coefficient'];
            }
            if (isset($validated['discount'])) {
                $pivotData['discount'] = $validated['discount'];
            }

            if (!empty($pivotData)) {
                $estimate->equipment()->updateExistingPivot($validated['equipment_id'], $pivotData);
            }

            if (isset($validated['price'])) {
                $equipment->price = $validated['price'];
                $equipment->save();
            }

            $calculated = $estimate->getEstimate();

            return response()->json([
                'success' => true,
                'calculated' => $calculated,
                'updated_equipment' => [
                    'id' => $equipment->id,
                    'quantity' => $pivotData['quantity'] ?? $estimate->equipment()->where('equipment_id', $equipment->id)->first()->pivot->quantity,
                    'price' => (float) ($equipment->price ?? 0),
                    'coefficient' => $pivotData['coefficient'] ?? $estimate->equipment()->where('equipment_id', $equipment->id)->first()->pivot->coefficient,
                    'discount' => $pivotData['discount'] ?? $estimate->equipment()->where('equipment_id', $equipment->id)->first()->pivot->discount,
                    'sum' => (float) ($equipment->price ?? 0) * ($pivotData['coefficient'] ?? $estimate->equipment()->where('equipment_id', $equipment->id)->first()->pivot->coefficient) * ($pivotData['quantity'] ?? $estimate->equipment()->where('equipment_id', $equipment->id)->first()->pivot->quantity),
                    'after_discount' => (float) ($equipment->price ?? 0) * ($pivotData['coefficient'] ?? $estimate->equipment()->where('equipment_id', $equipment->id)->first()->pivot->coefficient) * ($pivotData['quantity'] ?? $estimate->equipment()->where('equipment_id', $equipment->id)->first()->pivot->quantity) * (1 - (($pivotData['discount'] ?? $estimate->equipment()->where('equipment_id', $equipment->id)->first()->pivot->discount) / 100))
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in updateEquipmentPivot', ['message' => $e->getMessage(), 'input' => $request->all()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function getCategoryPath($categoryId): array
    {
        if (!$categoryId) {
            return ['Без категории'];
        }

        $category = Category::find($categoryId);
        if (!$category) {
            return ['Без категории'];
        }

        $path = [$category->name];
        $current = $category;

        while ($current->parent_id) {
            $current = Category::find($current->parent_id);
            if ($current) {
                array_unshift($path, $current->name);
            } else {
                break;
            }
        }

        return $path;
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

    public function attachStaff(Request $request, Project $project, User $user): JsonResponse
    {
        $this->authorize('edit projects');

        $request->validate([
            'rate_type' => 'nullable|in:hour,project',
            'rate' => 'nullable|numeric|min:0',
        ]);

        // Привязываем сотрудника к проекту
        $project->staff()->attach($user->id);

        // Если указана ставка, создаем запись в work_intervals с null датами
        if ($request->rate_type && $request->rate) {
            WorkInterval::create([
                'employee_id' => $user->id,
                'project_id' => $project->id,
                'date' => null, // Дата не указана - сотрудник просто привязан к проекту
                'start_time' => null, // Время не указано
                'end_time' => null, // Время не указано
                'type' => 'busy',
                'hour_rate' => $request->rate_type === 'hour' ? $request->rate : null,
                'project_rate' => $request->rate_type === 'project' ? $request->rate : null,
            ]);
        }

        return response()->json(['success' => 'Сотрудник добавлен на проект.']);
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
        if ($project->status === 'completed') {
            return response()->json(['error' => 'Проект завершён, редактирование невозможно'], 403);
        }

        Log::debug('Updating project ID: ' . $project->id . ', input data: ' . json_encode($request->all(), JSON_UNESCAPED_UNICODE));

        $rules = [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'exists:users,id',
            'start_date' => 'date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'in:new,active,completed,cancelled',
            'client_id' => 'nullable|exists:clients,id',
            'site_id' => 'nullable|exists:sites,id',
        ];

        $validated = $request->validate(array_filter($rules, function ($key) use ($request) {
            return $request->has($key);
        }, ARRAY_FILTER_USE_KEY));

        Log::debug('Validated data: ' . json_encode($validated, JSON_UNESCAPED_UNICODE));

        $updated = $project->update($validated);

        if (!$updated) {
            Log::error('Failed to update project ID: ' . $project->id . ', validated data: ' . json_encode($validated, JSON_UNESCAPED_UNICODE));
            return response()->json(['error' => 'Не удалось обновить проект'], 500);
        }

        // Перезагружаем модель, чтобы получить актуальные данные
        $project->refresh();

        Log::debug('Project update result: success, updated data: ' . json_encode($project->toArray(), JSON_UNESCAPED_UNICODE));

        return response()->json([
            'success' => 'Проект обновлен.',
            'project' => $project->toArray()
        ]);
    }
    public function store(Request $request): RedirectResponse|JsonResponse
    {
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
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete projects');
        $project->delete();
        return response()->json(['success' => 'Проект удален']);
    }

    public function equipmentList(Request $request, Project $project): JsonResponse
    {
        $categoryId = $request->query('category_id');
        $equipment = $project->equipment()
            ->where('category_id', $categoryId)
            ->select('equipment.*', 'project_equipment.status as pivot_status')
            ->get()
            ->map(function ($eq) {
                return [
                    'id' => $eq->id,
                    'name' => $eq->name,
                    'description' => $eq->description,
                    'price' => $eq->price,
                    'status' => $eq->pivot_status,
                ];
            });

        return response()->json($equipment);
    }
}
