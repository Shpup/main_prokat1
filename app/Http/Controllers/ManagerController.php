<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\WorkInterval;
use App\Models\EmployeeStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class ManagerController extends Controller
{
    /**
     * Показывает список сотрудников.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('create projects');
        
        $query = User::where('admin_id', auth()->id());
        
        // Поиск по имени
        if ($request->filled('query.name')) {
            $query->where('name', 'like', '%' . $request->input('query.name') . '%');
        }
        
        // Поиск по email
        if ($request->filled('query.email')) {
            $query->where('email', 'like', '%' . $request->input('query.email') . '%');
        }
        
        // Поиск по роли
        if ($request->filled('query.role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('query.role') . '%');
            });
        }
        
        // Сортировка
        $sort = $request->input('sort', 'name');
        $order = $request->input('order', 'asc');
        
        if (in_array($sort, ['name', 'email'])) {
            $query->orderBy($sort, $order);
        } elseif ($sort === 'role') {
            $query->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                  ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                  ->orderBy('roles.name', $order)
                  ->select('users.*');
        }
        
                $employees = $query->with(['roles', 'employeeStatus'])->get();

        // Создаем статусы для сотрудников, у которых их нет
        foreach ($employees as $employee) {
            if (!$employee->employeeStatus) {
                EmployeeStatus::create([
                    'employee_id' => $employee->id,
                    'status' => 'free'
                ]);
                // Перезагружаем отношение
                $employee->load('employeeStatus');
            }
        }

        if ($request->ajax()) {
            return response()->json($employees);
        }
        
        $projects = Project::where('admin_id', auth()->id())
            ->whereIn('status', ['new', 'active'])
            ->orderBy('name', 'asc')
            ->get();
        return view('managers.index', compact('employees', 'projects'));
    }

    /**
     * Показывает форму создания сотрудника.
     */
    public function create(): View
    {
        $this->authorize('create projects');
        $roles = Role::all();
        return view('managers.create', compact('roles'));
    }

    /**
     * Сохраняет нового сотрудника.
     */
        public function store(Request $request): JsonResponse
    {
        try {
            $this->authorize('create projects');
            
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|string|exists:roles,name',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'admin_id' => auth()->id(),
            ]);

            $user->assignRole($request->role);
            
            // Создаем запись статуса по умолчанию "free"
            EmployeeStatus::create([
                'employee_id' => $user->id,
                'status' => 'free'
            ]);

            return response()->json(['success' => 'Сотрудник создан.']);
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании сотрудника: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка при создании сотрудника: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Обновляет сотрудника.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('create projects');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        $user->syncRoles([$request->role]);

        return response()->json(['success' => 'Сотрудник обновлен.', 'employee' => $user->load('roles')]);
    }

    /**
     * Удаляет сотрудника.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('create projects');
        
        $user->delete();
        
        return response()->json(['success' => 'Сотрудник удален.']);
    }

    /**
     * Обновляет статус сотрудника.
     */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $this->authorize('create projects');
        
        $request->validate([
            'status' => 'required|in:free,unavailable,assigned',
        ]);

        // Создаем или обновляем статус
        EmployeeStatus::updateOrCreate(
            ['employee_id' => $user->id],
            ['status' => $request->status]
        );
        
        return response()->json(['success' => 'Статус обновлен.', 'status' => $request->status]);
    }

    /**
     * Обновляет комментарий к статусу.
     */
    public function updateStatusComment(Request $request, User $user): JsonResponse
    {
        $this->authorize('create projects');
        
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        // Обновляем комментарий в статусе
        EmployeeStatus::where('employee_id', $user->id)
            ->update(['status_comment' => $request->comment]);
        
        return response()->json(['success' => 'Комментарий сохранен.']);
    }

    /**
     * Получает назначения сотрудника на проекты.
     */
    public function getAssignments(User $user): JsonResponse
    {
        $this->authorize('create projects');
        
        // Получаем проекты, на которые назначен сотрудник через project_user
        // Только активные проекты (new и active)
        $assignments = $user->projects()
            ->whereIn('status', ['new', 'active'])
            ->orderBy('start_date', 'asc')
            ->orderBy('end_date', 'asc')
            ->get()
            ->map(function ($project) {
                return [
                    'project_name' => $project->name,
                    'project_id' => $project->id,
                    'start_date' => $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') : 'не установлено',
                    'end_date' => $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d.m.Y') : 'не установлен',
                    'project_url' => route('projects.show', $project->id)
                ];
            });
        
        return response()->json($assignments);
    }

    /**
     * Добавляет сотрудника на проект.
     */
    public function createAssignment(Request $request): JsonResponse
    {
        $this->authorize('create projects');
        
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
        ]);

        // Находим проект
        $project = \App\Models\Project::find($request->project_id);
        
        // Проверяем статус проекта - нельзя добавлять на завершенные или отмененные проекты
        if (in_array($project->status, ['completed', 'cancelled'])) {
            return response()->json(['message' => 'Нельзя добавить сотрудника на завершенный или отмененный проект.'], 400);
        }
        
        // Проверяем, не привязан ли уже сотрудник к проекту
        if ($project->staff()->where('user_id', $request->employee_id)->exists()) {
            return response()->json(['message' => 'Сотрудник уже назначен на этот проект.'], 400);
        }
        
        // Привязываем сотрудника к проекту через project_user
        $project->staff()->attach($request->employee_id);

        return response()->json(['success' => 'Сотрудник добавлен на проект.']);
    }

    /**
     * Получает данные сотрудника.
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('create projects');
        
        return response()->json($user->load('roles'));
    }

    /**
     * API для автодополнения проектов в модалке добавления сотрудника на проект.
     */
    public function autocompleteProjects(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q', '');
            
            // Если запрос пустой, возвращаем все проекты (без фильтрации)
            if (empty($query) || mb_strlen($query) < 1) {
                $user = auth()->user();
                
                $projects = \App\Models\Project::query()
                    ->where('admin_id', $user->id)
                    ->whereIn('status', ['new', 'active'])
                    ->orderBy('name', 'asc')
                    ->select(['id', 'name', 'description', 'status', 'start_date', 'end_date'])
                    ->take(10)
                    ->get()
                    ->map(function($project) {
                        return [
                            'id' => $project->id,
                            'title' => $project->name,
                            'location' => $project->description ?? '',
                            'date' => $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') : 'Дата не установлена',
                            'status' => $project->status
                        ];
                    })
                    ->values();
                
                return response()->json(['suggestions' => $projects]);
            }

            $user = auth()->user();
            
            // Получаем только проекты текущего админа со статусами new и active
            $projectsQuery = \App\Models\Project::query()
                ->where('admin_id', $user->id)
                ->whereIn('status', ['new', 'active'])
                ->orderBy('name', 'asc');

            $search = mb_strtolower(trim($query));
            
            $projects = $projectsQuery->select(['id', 'name', 'description', 'status', 'start_date', 'end_date'])->get();
            
            // Отладочная информация
            \Illuminate\Support\Facades\Log::info('ManagerController: autocompleteProjects debug', [
                'user_id' => $user->id,
                'query' => $query,
                'search' => $search,
                'projects_count' => $projects->count(),
                'projects' => $projects->toArray()
            ]);

            $suggestions = $projects
                ->filter(function($project) use ($search) {
                    $title = mb_strtolower($project->name ?? '');
                    $description = mb_strtolower($project->description ?? '');
                    
                    // Поиск по первым буквам названия
                    if ($search !== '' && mb_strpos($title, $search) === 0) {
                        return true;
                    }
                    
                    // Поиск по первым буквам описания
                    if ($search !== '' && mb_strpos($description, $search) === 0) {
                        return true;
                    }
                    
                    // Поиск по любой части названия
                    if ($search !== '' && mb_strpos($title, $search) !== false) {
                        return true;
                    }
                    
                    // Поиск по любой части описания
                    if ($search !== '' && mb_strpos($description, $search) !== false) {
                        return true;
                    }
                    
                    return false;
                })
                ->take(10)
                ->map(function($project) {
                    return [
                        'id' => $project->id,
                        'title' => $project->name,
                        'location' => $project->description ?? '',
                        'date' => $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') : 'Дата не установлена',
                        'status' => $project->status
                    ];
                })
                ->values(); // Преобразуем в массив с числовыми индексами

            $result = ['suggestions' => $suggestions];
            
            // Отладочная информация
            \Illuminate\Support\Facades\Log::info('ManagerController: autocompleteProjects result', [
                'suggestions_count' => count($suggestions),
                'result' => $result
            ]);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ManagerController: autocompleteProjects error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['suggestions' => []], 500);
        }
    }
}
