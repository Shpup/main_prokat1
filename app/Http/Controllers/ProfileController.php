<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\UserContact;
use App\Models\UserDocument;
use App\Models\Project;
use App\Models\WorkInterval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    /**
     * Главная ЛК: ближайшие проекты и задачи.
     */
    public function index(Request $request): View
    {
        $range = (int) $request->integer('range', 7);
        if (!in_array($range, [7, 14, 30], true)) { $range = 7; }

        // TODO: заменить заглушки реальными провайдерами AssignmentsProvider/TasksProvider
        $from = Carbon::now();
        $to   = Carbon::now()->copy()->addDays($range);

        // Получаем реальные проекты из базы данных
        $user = auth()->user();
        $projectsQuery = Project::query()
            ->where('admin_id', $user->id) // Только проекты, принадлежащие текущему пользователю
            ->with(['manager', 'staff']);

        $projects = $projectsQuery->get()->map(function ($project) use ($user) {
            $role = 'member';
            if ($project->manager_id === $user->id) {
                $role = 'owner';
            }
            
            // Получаем время работы пользователя в проекте
            $workIntervals = WorkInterval::where('employee_id', $user->id)
                ->where('project_id', $project->id)
                ->get();
            
            $timeRange = null;
            if ($workIntervals->isNotEmpty()) {
                $startTime = $workIntervals->min('start_time');
                $endTime = $workIntervals->max('end_time');
                if ($startTime && $endTime) {
                    $timeRange = $startTime . '-' . $endTime;
                }
            }
            
            // Получаем оплату
            $payment = null;
            if ($workIntervals->isNotEmpty()) {
                $firstInterval = $workIntervals->first();
                if ($firstInterval->project_rate) {
                    $payment = $firstInterval->project_rate . ' ₽';
                } elseif ($firstInterval->hour_rate) {
                    $payment = $firstInterval->hour_rate . ' ₽/час';
                }
            }
            
            return [
                'id' => $project->id,
                'title' => $project->name,
                'project_title' => $project->name,
                'location' => $project->description ?: 'Место не указано',
                'role' => $role,
                'time_range' => $timeRange,
                'payment' => $payment,
                'status' => $project->status, // new|active|completed|cancelled
                'date' => $project->start_date,
                'deadline' => $project->end_date,
                'description' => $project->description,
                'url' => route('projects.show', $project->id),
            ];
        })->toArray();

        $tasks = [];

        $groupedProjects = collect($projects)->groupBy(fn($i) => Carbon::parse($i['date'])->translatedFormat('j F Y'));
        $groupedTasks    = collect($tasks)->groupBy(fn($i) => Carbon::parse($i['date'])->translatedFormat('j F Y'));

        // Построение общего фида с поддержкой фильтров и сортировки
        $feed = collect($projects)->map(function ($p) {
            $deadline = $p['deadline'] ?? ($p['date'] ?? null);
            $id = $p['id'] ?? null;
            return [
                'type' => 'project',
                'id' => $id,
                'title' => $p['project_title'] ?? $p['title'] ?? 'Проект',
                'deadline' => $deadline ? Carbon::parse($deadline) : null,
                'description' => $p['description'] ?? '',
                'status' => $p['status'] ?? null,
                'priority' => $p['priority'] ?? 'medium',
                'url' => $id ? url("/projects/{$id}") : ($p['url'] ?? '#'),
            ];
        })->merge(
            collect($tasks)->map(function ($t) {
                $deadline = $t['deadline'] ?? ($t['date'] ?? null);
                $id = $t['id'] ?? null;
                return [
                    'type' => 'task',
                    'id' => $id,
                    'title' => $t['title'] ?? 'Задача',
                    'deadline' => $deadline ? Carbon::parse($deadline) : null,
                    'description' => $t['body'] ?? $t['description'] ?? '',
                    'status' => $t['status'] ?? null,
                    'priority' => $t['priority'] ?? 'medium',
                    'url' => $t['url'] ?? '#',
                ];
            })
        );

        $q = (string) $request->query('q', '');
        $type = (string) $request->query('type', 'all'); // project|task|all
        $status = (string) $request->query('status', ''); // in_progress|done|overdue
        $priority = (string) $request->query('priority', ''); // high|medium|low
        $sort = (string) $request->query('sort', 'deadline'); // deadline|priority|alpha

        $filtered = $feed
            ->when($q !== '', fn($c) => $c->filter(fn($i) => mb_stripos($i['title'], $q) !== false || mb_stripos($i['description'], $q) !== false))
            ->when(in_array($type, ['project','task'], true), fn($c) => $c->where('type', $type))
            ->when($status !== '', function ($c) use ($status) {
                if ($status === 'overdue') {
                    return $c->filter(fn($i) => $i['deadline'] instanceof Carbon && $i['deadline']->isPast());
                }
                return $c->filter(fn($i) => ($i['status'] ?? null) === $status);
            })
            ->when($priority !== '', fn($c) => $c->filter(fn($i) => ($i['priority'] ?? null) === $priority));

        $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
        $sorted = match ($sort) {
            'alpha' => $filtered->sortBy(fn($i) => mb_strtolower($i['title']))->values(),
            'priority' => $filtered->sortBy(fn($i) => $priorityOrder[$i['priority']] ?? 99)->values(),
            default => $filtered->sortBy(fn($i) => $i['deadline'] ?? Carbon::maxValue())->values(),
        };

        $filters = compact('q','type','status','priority','sort','range');

        // Projects view specific dataset (no period controls here)
        $projectsList = collect($projects)->map(function ($p) {
            return [
                'id' => $p['id'] ?? null,
                'title' => $p['project_title'] ?? $p['title'] ?? 'Проект',
                'location' => $p['location'] ?? '',
                'role' => $p['role'] ?? 'member', // Добавляем роль
                'time_range' => $p['time_range'] ?? null,
                'payment' => $p['payment'] ?? null,
                'status' => $p['status'] ?? 'new', // new|active|completed|cancelled
                'date' => $p['date'] ?? null,
                'url' => isset($p['id']) ? url("/projects/{$p['id']}") : ($p['url'] ?? '#'),
            ];
        });

        // Projects filters
        $pSearch = (string) $request->query('p_q', '');
        $pStatus = (string) $request->query('p_status', '');
        $pLocation = (string) $request->query('p_loc', '');
        $pRole = (string) $request->query('p_role', '');
        $pOnlyTimed = (bool) $request->boolean('p_timed', false);
        $pSort = (string) $request->query('p_sort', 'date'); // date|alpha|payment

        $projectsFiltered = $projectsList
            ->when($pSearch !== '', fn($c) => $c->filter(fn($i) => mb_stripos($i['title'], $pSearch) !== false || mb_stripos($i['location'], $pSearch) !== false))
            ->when($pStatus !== '', fn($c) => $c->filter(fn($i) => ($i['status'] ?? '') === $pStatus))
            ->when($pLocation !== '', fn($c) => $c->filter(fn($i) => mb_stripos($i['location'], $pLocation) !== false))
            ->when($pRole !== '', fn($c) => $c->filter(fn($i) => ($i['role'] ?? '') === $pRole))
            ->when($pOnlyTimed, fn($c) => $c->filter(fn($i) => !empty($i['time_range'])));

        $projectsSorted = match ($pSort) {
            'alpha' => $projectsFiltered->sortBy(fn($i) => mb_strtolower($i['title']))->values(),
            'payment' => $projectsFiltered->sortByDesc(fn($i) => (float) ($i['payment'] ?? 0))->values(),
            default => $projectsFiltered->sortBy(fn($i) => $i['date'] ? Carbon::parse($i['date']) : Carbon::maxValue())->values(),
        };

        // Pagination (simple)
        $pPage = max(1, (int) $request->query('p_page', 1));
        $pPerPage = 10;
        $pTotal = $projectsSorted->count();
        $pTotalPages = max(1, (int) ceil($pTotal / $pPerPage));
        if ($pPage > $pTotalPages) { $pPage = $pTotalPages; }
        $projectsPage = $projectsSorted->slice(($pPage - 1) * $pPerPage, $pPerPage)->values();

        // Group by date if meaningful (on current page)
        $projectsGrouped = $projectsPage->groupBy(function ($i) {
            if (empty($i['date'])) { return 'no_date'; }
            return Carbon::parse($i['date'])->translatedFormat('j F Y');
        });

        $projectsFilters = compact('pSearch','pStatus','pLocation','pRole','pOnlyTimed','pSort','pPage','pPerPage','pTotal','pTotalPages');

        // Отладочная информация
        \Log::info('ProfileController: Projects count = ' . count($projects));
        \Log::info('ProfileController: ProjectsList count = ' . $projectsList->count());
        \Log::info('ProfileController: ProjectsGrouped count = ' . $projectsGrouped->count());

        // -------- Tasks view dataset --------
        $tasksList = collect($tasks)->map(function ($t) {
            $deadline = $t['deadline'] ?? ($t['date'] ?? null);
            $assignee = $t['assignee'] ?? [];
            return [
                'id' => $t['id'] ?? null,
                'title' => $t['title'] ?? 'Задача',
                'project_id' => $t['project_id'] ?? null,
                'project_title' => $t['project_title'] ?? ($t['project']['title'] ?? ''),
                'assignee_id' => $assignee['id'] ?? null,
                'assignee_name' => $assignee['name'] ?? ($t['assignee_name'] ?? ''),
                'assignee_avatar' => $assignee['avatar'] ?? ($t['assignee_avatar'] ?? null),
                'deadline' => $deadline ? Carbon::parse($deadline) : null,
                'priority' => $t['priority'] ?? 'medium', // low|medium|high
                'status' => $t['status'] ?? 'new', // new|in_progress|done|overdue
                'mine' => isset($assignee['id']) ? $assignee['id'] === auth()->id() : false,
                'url' => $t['url'] ?? '#',
            ];
        });

        $taskProjectsOptions = $tasksList
            ->pluck('project_title', 'project_id')
            ->filter(fn($title, $id) => !empty($id) && !empty($title))
            ->sort()
            ->map(fn($title, $id) => ['id' => $id, 'title' => $title])
            ->values();

        $tSearch = (string) $request->query('t_q', '');
        $tStatus = (string) $request->query('t_status', '');
        $tPriority = (string) $request->query('t_priority', ''); // 'low'|'medium'|'high' or ''
        $tProject = $request->query('t_project');
        $tMine = (bool) $request->boolean('t_mine', false);
        $tSort = (string) $request->query('t_sort', 'deadline'); // deadline|priority|project|status

        $tasksFiltered = $tasksList
            ->when($tSearch !== '', fn($c) => $c->filter(function ($i) use ($tSearch) {
                return mb_stripos($i['title'], $tSearch) !== false
                    || mb_stripos((string) $i['project_title'], $tSearch) !== false
                    || mb_stripos((string) $i['assignee_name'], $tSearch) !== false;
            }))
            ->when($tStatus !== '', fn($c) => $c->filter(fn($i) => ($i['status'] ?? '') === $tStatus))
            ->when($tPriority !== '', fn($c) => $c->filter(fn($i) => ($i['priority'] ?? '') === $tPriority))
            ->when(!empty($tProject), fn($c) => $c->filter(fn($i) => (string) $i['project_id'] === (string) $tProject))
            ->when($tMine, fn($c) => $c->filter(fn($i) => (bool) $i['mine']));

        $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
        $statusOrder = ['new' => 1, 'in_progress' => 2, 'done' => 3, 'overdue' => 4];
        $tasksSorted = match ($tSort) {
            'priority' => $tasksFiltered->sortBy(fn($i) => $priorityOrder[$i['priority']] ?? 99)->values(),
            'project' => $tasksFiltered->sortBy(fn($i) => mb_strtolower($i['project_title'] ?? '')) ->values(),
            'status' => $tasksFiltered->sortBy(fn($i) => $statusOrder[$i['status']] ?? 99)->values(),
            default => $tasksFiltered->sortBy(fn($i) => $i['deadline'] ?? Carbon::maxValue())->values(),
        };

        // Pagination for tasks
        $tPage = max(1, (int) $request->query('t_page', 1));
        $tPerPage = 10;
        $tTotal = $tasksSorted->count();
        $tTotalPages = max(1, (int) ceil($tTotal / $tPerPage));
        if ($tPage > $tTotalPages) { $tPage = $tTotalPages; }
        $tasksPage = $tasksSorted->slice(($tPage - 1) * $tPerPage, $tPerPage)->values();

        $tasksFilters = compact('tSearch','tStatus','tPriority','tProject','tMine','tSort','tPage','tPerPage','tTotal','tTotalPages');

        return view('profile.index', compact(
            'groupedProjects', 'groupedTasks', 'sorted', 'filters',
            'projectsGrouped', 'projectsFilters',
            'tasksPage', 'tasksFilters', 'taskProjectsOptions'
        ));
    }

    // -------- О себе --------
    public function aboutEdit(): View
    {
        $u = auth()->user()->load(['contacts','documents','profile']);
        
        // Если профиль не существует, создаем его
        if (!$u->profile) {
            $u->profile()->create([
                'user_id' => $u->id
            ]);
            $u->load('profile');
        }
        
        return view('profile.about', compact('u'));
    }

    public function aboutUpdateInfo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'last_name' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'city' => 'nullable|string|max:100',
        ]);
        
        $user = auth()->user();
        
        // Обновляем или создаем профиль
        if ($user->profile) {
            $user->profile->update($data);
        } else {
            $user->profile()->create(array_merge($data, ['user_id' => $user->id]));
        }
        
        // Перезагружаем профиль для получения актуальных данных
        $user->load('profile');
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Профиль обновлен',
                'profile' => $user->profile ? [
                    'last_name' => $user->profile->last_name,
                    'first_name' => $user->profile->first_name,
                    'middle_name' => $user->profile->middle_name,
                    'birth_date' => $user->profile->birth_date ? $user->profile->birth_date->format('Y-m-d') : '',
                    'city' => $user->profile->city,
                ] : null
            ]);
        }
        return Redirect::back()->with('ok', 'Сохранено');
    }

    public function aboutUpdatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Пароль обновлён'
            ]);
        }
        return Redirect::back()->with('ok', 'Пароль обновлён');
    }

    public function aboutUpdateLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
        ]);
        
        $user = auth()->user();
        $user->email = $request->email;
        $user->save();
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email обновлён',
                'email' => $user->email
            ]);
        }
        return Redirect::back()->with('ok', 'Email обновлён');
    }

    // -------- Контакты: телефоны --------
    public function storePhone(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'value' => 'required|string|max:191',
            'comment' => 'nullable|string|max:255',
        ]);
        $created = auth()->user()->contacts()->create([
            'type' => 'phone',
            'value' => $data['value'],
            'comment' => $data['comment'] ?? null,
        ]);
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Телефон добавлен',
                'id' => $created->id,
                'value' => $created->value,
                'comment' => $created->comment,
            ]);
        }
        return Redirect::back()->with('ok', 'Телефон добавлен');
    }

    public function updatePhone(Request $request, UserContact $phone): RedirectResponse
    {
        abort_if($phone->user_id !== auth()->id(), 403);
        abort_if($phone->type !== 'phone', 404);
        $data = $request->validate([
            'value' => 'required|string|max:191',
            'comment' => 'nullable|string|max:255',
        ]);
        $phone->update($data);
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Телефон обновлен',
                'value' => $phone->value,
                'comment' => $phone->comment,
            ]);
        }
        return Redirect::back()->with('ok', 'Телефон обновлен');
    }

    public function destroyPhone(Request $request, UserContact $phone): RedirectResponse
    {
        abort_if($phone->user_id !== auth()->id(), 403);
        abort_if($phone->type !== 'phone', 404);
        $phone->delete();
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Телефон удален',
            ]);
        }
        return Redirect::back()->with('ok', 'Телефон удален');
    }

    // -------- Контакты: email --------
    public function storeEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'value' => 'required|email|max:191',
            'comment' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
        ]);
        $email = auth()->user()->contacts()->create([
            'type' => 'email',
            'value' => $data['value'],
            'comment' => $data['comment'] ?? null,
            'is_primary' => (bool)($data['is_primary'] ?? false),
        ]);
        if ($email->is_primary) {
            $this->demoteOtherEmails($email);
        }
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email добавлен',
                'id' => $email->id,
                'value' => $email->value,
                'comment' => $email->comment,
                'is_primary' => $email->is_primary,
            ]);
        }
        return Redirect::back()->with('ok', 'Email добавлен');
    }

    public function updateEmail(Request $request, UserContact $email): RedirectResponse
    {
        abort_if($email->user_id !== auth()->id(), 403);
        abort_if($email->type !== 'email', 404);
        $data = $request->validate([
            'value' => 'required|email|max:191',
            'comment' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
        ]);
        $email->update($data);
        if ($email->type === 'email' && ($data['is_primary'] ?? false)) {
            $this->demoteOtherEmails($email);
        }
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email обновлен',
                'value' => $email->value,
                'comment' => $email->comment,
                'is_primary' => $email->is_primary,
            ]);
        }
        return Redirect::back()->with('ok', 'Email обновлен');
    }

    public function destroyEmail(Request $request, UserContact $email): RedirectResponse
    {
        abort_if($email->user_id !== auth()->id(), 403);
        abort_if($email->type !== 'email', 404);
        $email->delete();
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email удален',
            ]);
        }
        return Redirect::back()->with('ok', 'Email удален');
    }

    private function demoteOtherEmails(UserContact $primary): void
    {
        UserContact::where('user_id', $primary->user_id)
            ->where('type', 'email')
            ->where('id', '!=', $primary->id)
            ->update(['is_primary' => false]);
    }

    // -------- Документы --------
    public function storeDocument(Request $request): RedirectResponse
    {
        $data = $this->validateDoc($request);
        
        // Обработка файлов
        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('documents', 'public');
                    $files[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType()
                    ];
                }
            }
        }
        $data['files'] = $files;
        
        auth()->user()->documents()->create($data);
        
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Документ добавлен']);
        }
        return Redirect::back()->with('ok', 'Документ добавлен');
    }

    public function updateDocument(Request $request, UserDocument $document): RedirectResponse
    {
        abort_if($document->user_id !== auth()->id(), 403);
        $data = $this->validateDoc($request);
        
        // Обработка файлов
        $files = $document->files ?? [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('documents', 'public');
                    $files[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType()
                    ];
                }
            }
        }
        $data['files'] = $files;
        
        $document->update($data);
        
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Документ обновлен']);
        }
        return Redirect::back()->with('ok', 'Документ обновлен');
    }

    public function destroyDocument(UserDocument $document): RedirectResponse
    {
        abort_if($document->user_id !== auth()->id(), 403);
        
        // Удаляем файлы
        if ($document->files) {
            foreach ($document->files as $file) {
                if (isset($file['path'])) {
                    \Storage::disk('public')->delete($file['path']);
                }
            }
        }
        
        $document->delete();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Документ удален']);
        }
        return Redirect::back()->with('ok', 'Документ удален');
    }

    private function validateDoc(Request $request): array
    {
        $rules = [
            'type' => ['required', 'in:passport,foreign_passport,driver_license'],
            'series' => 'nullable|string|max:32',
            'number' => 'nullable|string|max:64',
            'issued_at' => 'nullable|date',
            'issued_by' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
            'comment' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'string|in:A,B,C,D,E,M',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB
        ];

        // Дополнительная валидация в зависимости от типа
        $type = $request->input('type');
        switch ($type) {
            case 'passport':
                $rules['series'] = 'required|string|size:4|regex:/^\d{4}$/';
                $rules['number'] = 'required|string|size:6|regex:/^\d{6}$/';
                $rules['issued_at'] = 'required|date';
                break;
            case 'foreign_passport':
                $rules['number'] = 'required|string|max:64';
                $rules['issued_at'] = 'required|date';
                $rules['expires_at'] = 'required|date|after:issued_at';
                break;
            case 'driver_license':
                $rules['number'] = 'required|string|size:10|regex:/^\d{10}$/';
                $rules['issued_at'] = 'required|date';
                $rules['expires_at'] = 'required|date|after:issued_at';
                $rules['categories'] = 'required|array|min:1';
                break;
        }

        return $request->validate($rules);
    }

    // -------- Основные контакты --------
    public function updatePrimaryEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email,' . auth()->id()],
        ]);

        $user = auth()->user();
        $user->email = $data['email'];
        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email обновлен',
                'email' => $data['email']
            ]);
        }
        return Redirect::back()->with('ok', 'Email обновлен');
    }

    public function updatePrimaryPhone(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => 'nullable|string|max:20',
        ]);

        $user = auth()->user();
        $user->phone = $data['phone'];
        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Телефон обновлен',
                'phone' => $data['phone']
            ]);
        }
        return Redirect::back()->with('ok', 'Телефон обновлен');
    }


    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.about.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
