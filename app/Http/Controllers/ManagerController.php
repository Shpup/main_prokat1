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

        // Возвращаем JSON только если это AJAX-запрос с правильным заголовком
        if ($request->ajax() && $request->header('X-Requested-With') === 'XMLHttpRequest') {
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

        // Создаем или обновляем статус на "unavailable" с комментарием
        EmployeeStatus::updateOrCreate(
            ['employee_id' => $user->id],
            [
                'status' => 'unavailable',
                'status_comment' => $request->comment
            ]
        );

        return response()->json(['success' => 'Комментарий сохранен.']);
    }

    /**
     * Удаляет комментарий к статусу и сбрасывает статус на "free".
     */
    public function deleteStatusComment(User $user): JsonResponse
    {
        $this->authorize('create projects');

        // Удаляем запись статуса (это сбросит статус на "free" по умолчанию)
        EmployeeStatus::where('employee_id', $user->id)->delete();

        return response()->json(['success' => 'Статус снят.']);
    }

    /**
     * Проверяет и обновляет статус сотрудника на основе его назначений.
     */
    public function checkAndUpdateStatus(User $user): void
    {
        // Проверяем, есть ли активные назначения
        $hasActiveAssignments = $user->projects()
            ->whereIn('status', ['new', 'active'])
            ->exists();

        if (!$hasActiveAssignments) {
            // Если нет активных назначений, сбрасываем статус на "free"
            EmployeeStatus::where('employee_id', $user->id)->delete();
        } else {
            // Если есть активные назначения, устанавливаем статус "assigned"
            EmployeeStatus::updateOrCreate(
                ['employee_id' => $user->id],
                ['status' => 'assigned']
            );
        }
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

        // Автоматически обновляем статус на "assigned"
        $employee = User::find($request->employee_id);
        $this->checkAndUpdateStatus($employee);

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

    /**
     * Показывает профиль сотрудника.
     */
    public function showProfile(User $user): View
    {
        $this->authorize('create projects');

        // Проверяем, что сотрудник принадлежит текущему админу
        if ($user->admin_id !== auth()->id()) {
            abort(403);
        }

        // Загружаем отношения
        $user->load(['roles', 'employeeStatus', 'profile', 'contacts']);

        // Получаем все роли для селекта
        $roles = Role::all();

        return view('managers.profile', compact('user', 'roles'));
    }

    /**
     * Обновляет основную информацию сотрудника.
     */
    public function updateMain(Request $request, User $user): JsonResponse
    {
        $this->authorize('create projects');

        // Проверяем, что сотрудник принадлежит текущему админу
        if ($user->admin_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен'], 403);
        }

        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'city' => 'nullable|string|max:255',
            'role' => 'required|string|exists:roles,name',
        ]);

        try {
            // Обновляем или создаем профиль
            $profileData = [
                'first_name' => $request->firstname,
                'last_name' => $request->lastname,
                'middle_name' => $request->middlename,
                'birth_date' => $request->birth_date ?: null,
                'city' => $request->city,
            ];

            if ($user->profile) {
                $user->profile->update($profileData);
            } else {
                $profileData['user_id'] = $user->id;
                $user->profile()->create($profileData);
            }

            // Обновляем роль
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->syncRoles([$role]);
            }

            return response()->json(['success' => true, 'message' => 'Данные обновлены']);
        } catch (\Exception $e) {
            \Log::error('Ошибка при обновлении основного профиля: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении данных: ' . $e->getMessage()]);
        }
    }

    /**
     * Обновляет контакты пользователя.
     */
    private function updateUserContacts(User $user, array $contacts, string $type): void
    {
        // Удаляем старые контакты данного типа
        $user->contacts()->where('type', $type)->delete();

        // Добавляем новые контакты
        foreach ($contacts as $index => $contact) {
            if (!empty($contact)) {
                $user->contacts()->create([
                    'type' => $type,
                    'value' => $contact,
                    'is_primary' => $index === 0, // Первый контакт - основной
                ]);
            }
        }
    }

    /**
     * Обновляет контакты сотрудника.
     */
    public function updateContacts(Request $request, User $user): JsonResponse
    {
        $this->authorize('create projects');

        // Проверяем, что сотрудник принадлежит текущему админу
        if ($user->admin_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен'], 403);
        }

        $request->validate([
            'phones' => 'array',
            'phones.*' => 'nullable|string|max:20',
            'emails' => 'array',
            'emails.*' => 'nullable|email|max:255',
        ]);

        try {
            // Обновляем контакты
            $this->updateUserContacts($user, $request->phones, 'phone');
            $this->updateUserContacts($user, $request->emails, 'email');

            // Обновляем основной email
            if (!empty($request->emails[0])) {
                $user->update(['email' => $request->emails[0]]);
            }

            return response()->json(['success' => true, 'message' => 'Контакты обновлены']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении контактов']);
        }
    }

    /**
     * Обновляет отдельный контакт сотрудника.
     */
    public function updateContact(Request $request, User $user): JsonResponse
    {
        $this->authorize('create projects');

        if ($user->admin_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен'], 403);
        }

        $request->validate([
            'type' => 'required|in:phone,email',
            'value' => 'required|string|max:255',
            'is_primary' => 'required|boolean',
            'contact_id' => 'nullable|integer|exists:user_contacts,id'
        ]);

        try {
            $type = $request->type;
            $value = $request->value;
            $isPrimary = $request->is_primary;

            if ($isPrimary) {
                // Обновляем основной контакт
                $contact = $user->contacts()->where('type', $type)->where('is_primary', true)->first();

                if ($contact) {
                    $contact->update(['value' => $value]);
                } else {
                    // Создаем новый основной контакт
                    $user->contacts()->create([
                        'type' => $type,
                        'value' => $value,
                        'is_primary' => true
                    ]);
                }

                // Если это email, обновляем также поле email в таблице users
                if ($type === 'email') {
                    $user->update(['email' => $value]);
                }
            } else {
                // Обновляем или создаем дополнительный контакт
                if ($request->contact_id) {
                    $contact = $user->contacts()->find($request->contact_id);
                    if ($contact) {
                        $contact->update([
                            'value' => $value,
                            'comment' => $request->comment ?? null
                        ]);
                    } else {
                        return response()->json(['success' => false, 'message' => 'Контакт не найден'], 404);
                    }
                } else {
                    // Создаем новый дополнительный контакт
                    $contact = $user->contacts()->create([
                        'type' => $type,
                        'value' => $value,
                        'is_primary' => false,
                        'comment' => $request->comment ?? null
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Контакт обновлен',
                'contact_id' => $contact->id ?? null,
                'value' => $value,
                'comment' => $request->comment ?? null
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при обновлении контакта: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении контакта: ' . $e->getMessage()]);
        }
    }

    /**
     * Обновляет учетную запись сотрудника.
     */
    public function updateAccount(Request $request, User $user): JsonResponse
    {
        $this->authorize('create projects');

        // Проверяем, что сотрудник принадлежит текущему админу
        if ($user->admin_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен'], 403);
        }

        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        try {
            $user->update(['email' => $request->email]);
            return response()->json([
                'success' => true,
                'message' => 'Данные обновлены',
                'email' => $request->email
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при обновлении данных']);
        }
    }

    /**
     * Изменяет пароль сотрудника.
     */
    public function changePassword(Request $request, User $user): JsonResponse
    {
        $this->authorize('create projects');

        // Проверяем, что сотрудник принадлежит текущему админу
        if ($user->admin_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен'], 403);
        }

        $request->validate([
            'new_password' => 'required|string|min:8|regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            'confirm_password' => 'required|same:new_password',
        ], [
            'new_password.regex' => 'Пароль должен содержать буквы и цифры',
        ]);

        try {
            $user->update(['password' => Hash::make($request->new_password)]);
            return response()->json(['success' => true, 'message' => 'Пароль изменен']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при изменении пароля']);
        }
    }

    /**
     * Загружает аватар сотрудника.
     */
    public function uploadAvatar(Request $request, User $user): JsonResponse
    {
        $this->authorize('create projects');

        // Проверяем, что сотрудник принадлежит текущему админу
        if ($user->admin_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен'], 403);
        }

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png|max:2048',
        ]);

        try {
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('avatars', $filename, 'public');

                // Обновляем или создаем профиль с фото
                if ($user->profile) {
                    $user->profile->update(['photo_path' => '/storage/' . $path]);
                } else {
                    $user->profile()->create(['photo_path' => '/storage/' . $path]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Аватар загружен',
                    'avatar_url' => '/storage/' . $path
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Файл не найден']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при загрузке аватара']);
        }
    }

    /**
     * Обновляет основной email сотрудника.
     */
    public function updatePrimaryEmail(Request $request, User $user): JsonResponse
    {
        $this->authorize('create projects');

        if ($user->admin_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен'], 403);
        }

        try {
            $data = $request->validate([
                'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            ]);

            $user->email = $data['email'];
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Email обновлен',
                'email' => $data['email']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновляет основной телефон сотрудника.
     */
    public function updatePrimaryPhone(Request $request, User $user): JsonResponse
    {
        $this->authorize('create projects');

        if ($user->admin_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен'], 403);
        }

        try {
            $data = $request->validate([
                'phone' => 'nullable|string|regex:/^[\d+\-()\s]*$/|max:191',
            ]);

            $user->phone = $data['phone'];
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Телефон обновлен',
                'phone' => $data['phone']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении телефона: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Удаляет дополнительный контакт сотрудника.
     */
    public function deleteContact(Request $request, User $user): JsonResponse
    {
        $this->authorize('create projects');

        if ($user->admin_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Доступ запрещен'], 403);
        }

        try {
            $data = $request->validate([
                'contact_id' => 'required|integer|exists:user_contacts,id'
            ]);

            // Находим контакт и проверяем, что он принадлежит данному пользователю
            $contact = $user->contacts()->find($data['contact_id']);

            if (!$contact) {
                return response()->json(['success' => false, 'message' => 'Контакт не найден'], 404);
            }

            // Удаляем контакт
            $contact->delete();

            return response()->json([
                'success' => true,
                'message' => 'Контакт удален'
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при удалении контакта: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении контакта: ' . $e->getMessage()
            ], 500);
        }
    }
}
