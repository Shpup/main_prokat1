<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\UserContact;
use App\Models\UserDocument;
use App\Models\Project;
use App\Models\WorkInterval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    /**
     * Главная ЛК: ближайшие проекты и задачи.
     */
    public function index(Request $request)
    {
        try {
            // Если это AJAX запрос для Alpine.js, обрабатываем отдельно
            if ($request->wantsJson()) {
                return $this->handleAjaxRequest($request);
            }

            // Обычный запрос - показываем основную страницу профиля
        $user = auth()->user();
            
            // Получаем проекты для отображения
            $projects = $this->getUserProjects($user);
            $tasks = []; // Пока пустой массив задач
            
            // Группируем проекты по датам
            $groupedProjects = collect($projects)->groupBy(function($i) {
                if (empty($i['date'])) return 'no_date';
                try {
                    return Carbon::parse($i['date'])->translatedFormat('j F Y');
                } catch (\Exception $e) {
                    return 'no_date';
                }
            });
            
            $groupedTasks = collect($tasks)->groupBy(function($i) {
                if (empty($i['date'])) return 'no_date';
                try {
                    return Carbon::parse($i['date'])->translatedFormat('j F Y');
                } catch (\Exception $e) {
                    return 'no_date';
                }
            });

            // Строим общий фид для отображения
            $feed = collect($projects)->map(function ($p) {
                $date = $p['date'] ?? null;
                $id = $p['id'] ?? null;
                return [
                    'type' => 'project',
                    'id' => $id,
                    'title' => $p['project_title'] ?? $p['title'] ?? 'Проект',
                    'deadline' => $date ? (function() use ($date) {
                        try {
                            return Carbon::parse($date);
                        } catch (\Exception $e) {
                            return null;
                        }
                    })() : null,
                    'description' => $p['description'] ?? '',
                    'status' => $p['status'] ?? null,
                    'priority' => $p['priority'] ?? 'medium',
                    'url' => $id ? url("/projects/{$id}") : ($p['url'] ?? '#'),
                ];
            })->merge(collect($tasks)->map(function ($t) {
                $deadline = $t['deadline'] ?? ($t['date'] ?? null);
                $id = $t['id'] ?? null;
                return [
                    'type' => 'task',
                    'id' => $id,
                    'title' => $t['title'] ?? 'Задача',
                    'deadline' => $deadline ? (function() use ($deadline) {
                        try {
                            return Carbon::parse($deadline);
                        } catch (\Exception $e) {
                            return null;
                        }
                    })() : null,
                    'description' => $t['body'] ?? $t['description'] ?? '',
                    'status' => $t['status'] ?? null,
                    'priority' => $t['priority'] ?? 'medium',
                    'url' => $t['url'] ?? '#',
                ];
            }));

            // Фильтруем по умолчанию на +7 дней
            $startDate = now()->startOfDay();
            $endDate = now()->addDays(7)->endOfDay();
            
            $sorted = $feed->filter(function($i) use ($startDate, $endDate) {
                if (!$i['deadline'] instanceof Carbon) return false;
                return $i['deadline']->gte($startDate) && $i['deadline']->lte($endDate);
            })->sortBy(fn($i) => $i['deadline'] ?? Carbon::maxValue())->values();

            $filters = [
                'q' => '',
                'type' => 'all',
                'status' => '',
                'range' => 7
            ];

            return view('profile.index', compact(
                'groupedProjects', 'groupedTasks', 'sorted', 'filters'
            ));

        } catch (\Exception $e) {
            \Log::error('ProfileController: Error in index method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Redirect::back()->withErrors(['error' => 'Ошибка при загрузке профиля: ' . $e->getMessage()]);
        }
    }

    /**
     * Обработка AJAX запросов для Alpine.js
     */
    private function handleAjaxRequest(Request $request)
    {
        try {
            $view = $request->query('view');
            
            if ($view === 'upcoming') {
                return $this->handleUpcomingRequest($request);
            }
            
            if ($view === 'projects') {
                return $this->handleProjectsRequest($request);
            }
            
            return response()->json(['error' => 'Неизвестный тип запроса'], 400);
            
        } catch (\Exception $e) {
            \Log::error('ProfileController: Error in AJAX request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка обработки запроса'], 500);
        }
    }

    /**
     * Получение проектов пользователя
     */
    private function getUserProjects($user)
    {
        // Логика загрузки проектов в зависимости от роли
        if ($user->hasRole('admin')) {
        $projectsQuery = Project::query()
                ->where('admin_id', $user->id)
                ->where('status', '!=', 'cancelled')
            ->with(['manager', 'staff']);
        } else {
            $projectsQuery = Project::query()
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($user) {
                    $query->where('manager_id', $user->id)
                          ->orWhereHas('staff', function ($staffQuery) use ($user) {
                              $staffQuery->where('user_id', $user->id);
                          });
                })
                ->with(['manager', 'staff']);
        }

        return $projectsQuery->get()->map(function ($project) use ($user) {
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
                'time_range' => $timeRange,
                'payment' => $payment,
                'status' => $project->status,
                'date' => $project->start_date,
                'deadline' => $project->end_date,
                'description' => $project->description,
                'url' => route('projects.show', $project->id),
            ];
        })->toArray();
    }

    /**
     * Обработка запроса для upcoming (ближайшие проекты и задачи)
     */
    private function handleUpcomingRequest(Request $request)
    {
        $user = auth()->user();
        $projects = $this->getUserProjects($user);
        $tasks = [];

        // Строим общий фид
        $feed = collect($projects)->map(function ($p) {
            $date = $p['date'] ?? null;
            $id = $p['id'] ?? null;
            return [
                'type' => 'project',
                'id' => $id,
                'title' => $p['project_title'] ?? $p['title'] ?? 'Проект',
                'deadline' => $date ? (function() use ($date) {
                    try {
                        return Carbon::parse($date);
                    } catch (\Exception $e) {
                        return null;
                    }
                })() : null,
                'description' => $p['description'] ?? '',
                'status' => $p['status'] ?? null,
                'priority' => $p['priority'] ?? 'medium',
                'url' => $id ? url("/projects/{$id}") : ($p['url'] ?? '#'),
            ];
        })->merge(collect($tasks)->map(function ($t) {
                $deadline = $t['deadline'] ?? ($t['date'] ?? null);
                $id = $t['id'] ?? null;
                return [
                    'type' => 'task',
                    'id' => $id,
                    'title' => $t['title'] ?? 'Задача',
                'deadline' => $deadline ? (function() use ($deadline) {
                    try {
                        return Carbon::parse($deadline);
                    } catch (\Exception $e) {
                        return null;
                    }
                })() : null,
                    'description' => $t['body'] ?? $t['description'] ?? '',
                    'status' => $t['status'] ?? null,
                    'priority' => $t['priority'] ?? 'medium',
                    'url' => $t['url'] ?? '#',
                ];
        }));

        // Применяем фильтры
        $q = (string) $request->query('u_q', '');
        $type = (string) $request->query('u_type', 'all');
        $status = (string) $request->query('u_status', '');
        $range = (int) $request->query('u_range', 7);

        $filtered = $feed
            // Исключаем завершённые проекты из "Ближайших проектов и задач"
            ->filter(function($i) {
                if ($i['type'] === 'project') {
                    return ($i['status'] ?? null) !== 'completed';
                }
                return true; // Задачи не фильтруем по статусу
            })
            ->when($q !== '', fn($c) => $c->filter(fn($i) => mb_stripos($i['title'], $q) !== false || mb_stripos($i['description'], $q) !== false))
            ->when(in_array($type, ['project','task'], true), fn($c) => $c->where('type', $type))
            ->when($status !== '', function ($c) use ($status) {
                if ($status === 'overdue') {
                    return $c->filter(fn($i) => $i['deadline'] instanceof Carbon && $i['deadline']->isPast());
                }
                return $c->filter(fn($i) => ($i['status'] ?? null) === $status);
            })
            ->when($range > 0, function ($c) use ($range) {
                $startDate = now()->startOfDay();
                $endDate = now()->addDays($range)->endOfDay();
                return $c->filter(function($i) use ($startDate, $endDate) {
                    if (!$i['deadline'] instanceof Carbon) return false;
                    return $i['deadline']->gte($startDate) && $i['deadline']->lte($endDate);
                });
            });

        $sorted = $filtered->sortBy(fn($i) => $i['deadline'] ?? Carbon::maxValue())->values();

        $formattedItems = $sorted->map(function($item) {
            $deadline = null;
            if ($item['deadline'] instanceof Carbon) {
                $deadline = $item['deadline']->format('d.m.Y');
            }
            return [
                'id' => $item['id'],
                'title' => $item['title'],
                'deadline' => $deadline,
                'description' => $item['description'],
                'status' => $item['status'],
                'url' => $item['url']
            ];
        });

        return response()->json([
            'items' => $formattedItems,
            'currentPage' => 1,
            'totalPages' => 1
        ]);
    }

    /**
     * Обработка запроса для projects (список проектов)
     */
    private function handleProjectsRequest(Request $request)
    {
        $user = auth()->user();
        $projects = $this->getUserProjects($user);

        $projectsList = collect($projects)->map(function ($p) {
            return [
                'id' => $p['id'] ?? null,
                'title' => $p['project_title'] ?? $p['title'] ?? 'Проект',
                'location' => $p['location'] ?? '',
                'time_range' => $p['time_range'] ?? null,
                'payment' => $p['payment'] ?? null,
                'status' => $p['status'] ?? 'new',
                'date' => $p['date'] ?? null,
                'url' => isset($p['id']) ? url("/projects/{$p['id']}") : ($p['url'] ?? '#'),
            ];
        });

        // Применяем фильтры
        $pSearch = (string) $request->query('p_q', '');
        $pStatus = (string) $request->query('p_status', '');
        $pStartDate = $request->query('p_start_date', now()->format('Y-m-d'));
        $pEndDate = $request->query('p_end_date', now()->addDays(7)->format('Y-m-d'));

        $projectsFiltered = $projectsList
            ->when($pSearch !== '', function($c) use ($pSearch) {
                return $c->filter(function($i) use ($pSearch) {
                    $search = mb_strtolower(trim($pSearch));
                    $title = mb_strtolower($i['title'] ?? '');
                    $location = mb_strtolower($i['location'] ?? '');
                    
                    if ($title === $search) return true;
                    if (mb_strpos($title, $search) === 0) return true;
                    if (mb_strpos($location, $search) === 0) return true;
                    if (mb_strpos($title, $search) !== false) return true;
                    if (mb_strpos($location, $search) !== false) return true;
                    
                    return false;
                });
            })
            ->when($pStatus !== '', fn($c) => $c->filter(fn($i) => ($i['status'] ?? '') === $pStatus))
            ->when(!empty($pStartDate), function($c) use ($pStartDate) {
                return $c->filter(function($i) use ($pStartDate) {
                    if (empty($i['date'])) return false;
                    try {
                        return Carbon::parse($i['date'])->format('Y-m-d') >= $pStartDate;
                    } catch (\Exception $e) {
                        return false;
                    }
                });
            })
            ->when(!empty($pEndDate), function($c) use ($pEndDate) {
                return $c->filter(function($i) use ($pEndDate) {
                    if (empty($i['date'])) return false;
                    try {
                        return Carbon::parse($i['date'])->format('Y-m-d') <= $pEndDate;
                    } catch (\Exception $e) {
                        return false;
                    }
                });
            });

        $groupedProjects = $projectsFiltered->groupBy(function($i) {
            if (empty($i['date'])) return 'no_date';
            try {
            return Carbon::parse($i['date'])->translatedFormat('j F Y');
            } catch (\Exception $e) {
                return 'no_date';
            }
        });

        return response()->json([
            'projects' => $projectsFiltered->values(),
            'groupedProjects' => $groupedProjects,
            'currentPage' => 1,
            'totalPages' => 1
        ]);
    }

    /**
     * API для автодополнения проектов
     */
    public function autocompleteProjects(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q', '');
            $section = $request->query('section', 'projects'); // projects или upcoming
            
            if (empty($query) || mb_strlen($query) < 1) {
                return response()->json(['suggestions' => []]);
            }

            $user = auth()->user();
            
            // Логика загрузки проектов в зависимости от роли
            if ($user->hasRole('admin')) {
                $projectsQuery = Project::query()
                    ->where('admin_id', $user->id)
                    ->where('status', '!=', 'cancelled');
            } else {
                $projectsQuery = Project::query()
                    ->where('status', '!=', 'cancelled')
                    ->where(function ($q) use ($user) {
                        $q->where('manager_id', $user->id)
                          ->orWhereHas('staff', function ($staffQuery) use ($user) {
                              $staffQuery->where('user_id', $user->id);
                          });
                    });
            }

            $search = mb_strtolower(trim($query));
            
            $projects = $projectsQuery->select(['id','name','description','status','start_date'])->get();

            $suggestions = $projects
                ->filter(function($project) use ($search, $section) {
                    // Исключаем завершённые проекты для раздела "upcoming"
                    if ($section === 'upcoming' && ($project->status ?? null) === 'completed') {
                        return false;
                    }
                    
                    // Фильтруем по дате только для раздела "upcoming" (Ближайшие проекты и задачи)
                    if ($section === 'upcoming' && !empty($project->start_date)) {
                        try {
                            $startDate = Carbon::parse($project->start_date);
                            if ($startDate->isPast() && !$startDate->isToday()) {
                                return false; // Исключаем прошлые проекты только для upcoming
                            }
                        } catch (\Exception $e) {
                            return false; // Если не удалось распарсить дату, исключаем проект
                        }
                    }
                    
                    $title = mb_strtolower($project->name ?? '');
                    $location = mb_strtolower($project->description ?? '');
                    
                    // Поиск по первым буквам названия
                    if ($search !== '' && mb_strpos($title, $search) === 0) {
                        return true;
                    }
                    
                    // Поиск по первым буквам места
                    if ($search !== '' && mb_strpos($location, $search) === 0) {
                        return true;
                    }
                    
                    return false;
                })
                ->take(10)
                ->map(function($project) {
                    $dateFormatted = null;
                    if (!empty($project->start_date)) {
                        try {
                            $dateFormatted = Carbon::parse($project->start_date)->format('d.m.Y');
                        } catch (\Exception $e) {
                            $dateFormatted = null;
                        }
                    }
                    return [
                        'id' => $project->id,
                        'title' => (string) $project->name,
                        'location' => $project->description ? (string) $project->description : 'Место не указано',
                        'status' => (string) $project->status,
                        'date' => $dateFormatted,
                    ];
                })
                ->values();

            return response()->json(['suggestions' => $suggestions]);
        } catch (\Throwable $e) {
            \Log::error('ProfileController: autocompleteProjects error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['suggestions' => [], 'message' => 'Server error'], 500);
        }
    }

    // -------- О себе --------
    public function aboutEdit(): View
    {
        $u = auth()->user()->load(['contacts','phones','emails','documents','profile']);
        
        // Если профиль не существует, создаем его
        if (!$u->profile) {
            $u->profile()->create([
                'user_id' => $u->id
            ]);
            $u->load('profile');
        }
        
        return view('profile.about', compact('u'));
    }

    public function aboutUpdateInfo(Request $request)
    {
        try {
            $data = $request->validate([
                'last_name' => 'nullable|string|max:100',
                'first_name' => 'nullable|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'birth_date' => 'nullable|date',
                'city' => 'nullable|string|max:100',
            ]);
            
            // Фильтруем пустые строки, заменяя их на null
            $data = array_map(function($value) {
                return ($value === '' || $value === null) ? null : $value;
            }, $data);
            
            $user = auth()->user();
            
            // Обновляем или создаем профиль
            if ($user->profile) {
                $user->profile->update($data);
            } else {
                $profileData = array_merge($data, ['user_id' => $user->id]);
                // Убеждаемся, что все поля имеют значения (хотя бы null)
                $profileData = array_merge([
                    'last_name' => null,
                    'first_name' => null,
                    'middle_name' => null,
                    'birth_date' => null,
                    'city' => null,
                ], $profileData);
                $user->profile()->create($profileData);
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
                ])->header('Content-Type', 'application/json');
            }
            return Redirect::back()->with('ok', 'Сохранено');
        } catch (\Exception $e) {
            \Log::error('ProfileController: Error in aboutUpdateInfo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при обновлении профиля: ' . $e->getMessage()
                ], 500)->header('Content-Type', 'application/json');
            }
            
            return Redirect::back()->withErrors(['error' => 'Ошибка при обновлении профиля']);
        }
    }

    public function aboutUpdatePassword(Request $request)
    {
        // Проверяем, что пользователь является админом
        if (!auth()->user()->hasRole('admin')) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен. Только администраторы могут изменять пароли.'
                ], 403);
            }
            return Redirect::back()->withErrors(['error' => 'Доступ запрещен. Только администраторы могут изменять пароли.']);
        }

        try {
            // Для администратора не требуем текущий пароль — он может сбросить пароль пользователю
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ], [
                'password.required' => 'Необходимо указать новый пароль',
                'password.confirmed' => 'Пароли не совпадают',
                'password.min' => 'Пароль должен содержать минимум 8 символов',
        ]);
            
        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                    'message' => 'Пароль успешно обновлён'
                ]);
            }
            return Redirect::back()->with('ok', 'Пароль успешно обновлён');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при обновлении пароля: ' . $e->getMessage()
                ], 500);
            }
            return Redirect::back()->withErrors(['error' => 'Ошибка при обновлении пароля']);
        }
    }

    public function aboutUpdateLogin(Request $request)
    {
        // Проверяем, что пользователь является админом
        if (!auth()->user()->hasRole('admin')) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен. Только администраторы могут изменять логины.'
                ], 403);
            }
            return Redirect::back()->withErrors(['error' => 'Доступ запрещен. Только администраторы могут изменять логины.']);
        }

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

    public function aboutUpdatePhoto(Request $request)
    {
        try {
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            ]);

            $user = auth()->user();
            
            // Удаляем старое фото если есть
            if ($user->profile && $user->profile->photo_path) {
                $oldPath = storage_path('app/public/' . $user->profile->photo_path);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Сохраняем новое фото
            $path = $request->file('photo')->store('profile-photos', 'public');
            
            // Обновляем или создаем профиль
            if ($user->profile) {
                $user->profile->update(['photo_path' => $path]);
            } else {
                $user->profile()->create([
                    'user_id' => $user->id,
                    'photo_path' => $path,
                    'last_name' => null,
                    'first_name' => null,
                    'middle_name' => null,
                    'birth_date' => null,
                    'city' => null,
                ]);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Фото успешно загружено',
                    'photo_path' => $path
                ])->header('Content-Type', 'application/json');
            }
            return Redirect::back()->with('ok', 'Фото загружено');
        } catch (\Exception $e) {
            \Log::error('ProfileController: Error in aboutUpdatePhoto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при загрузке фото: ' . $e->getMessage()
                ], 500)->header('Content-Type', 'application/json');
            }
            return Redirect::back()->withErrors(['error' => 'Ошибка при загрузке фото']);
        }
    }



    // -------- Контакты: телефоны --------
    public function storePhone(Request $request)
    {
        try {
            $data = $request->validate([
                'value' => 'required|string|regex:/^[\d+\-()\s]+$/|max:191',
                'comment' => 'nullable|string|max:255',
            ]);
            
            $user = auth()->user();
            
                // Создаем новую запись
                $created = $user->contacts()->create([
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
                ])->header('Content-Type', 'application/json');
            }
            
            return Redirect::back()->with('ok', 'Телефон добавлен');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $e->errors()
                ], 422)->header('Content-Type', 'application/json');
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('ProfileController: Error in storePhone', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при сохранении телефона: ' . $e->getMessage()
                ], 500)->header('Content-Type', 'application/json');
            }
            return Redirect::back()->withErrors(['error' => 'Ошибка при сохранении телефона']);
        }
    }

    public function updatePhone(Request $request, UserContact $phone)
    {
        abort_if($phone->user_id !== auth()->id(), 403);
        abort_if($phone->type !== 'phone', 404);
        $data = $request->validate([
            'value' => 'required|string|regex:/^[\d+\-()\s]+$/|max:191',
            'comment' => 'nullable|string|max:255',
        ]);
        $phone->update($data);
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Телефон обновлен',
                'value' => $phone->value,
                'comment' => $phone->comment,
            ])->header('Content-Type', 'application/json');
        }
        return Redirect::back()->with('ok', 'Телефон обновлен');
    }

    public function destroyPhone(Request $request, UserContact $phone)
    {
        abort_if($phone->user_id !== auth()->id(), 403);
        abort_if($phone->type !== 'phone', 404);
        
        // Удаляем запись вместо установки NULL
        $phone->delete();
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Телефон удален',
            ])->header('Content-Type', 'application/json');
        }
        return Redirect::back()->with('ok', 'Телефон удален');
    }

    // -------- Контакты: email --------
    public function storeEmail(Request $request)
    {
        try {
            $data = $request->validate([
                'value' => 'required|email|max:191',
                'comment' => 'nullable|string|max:255',
                'is_primary' => 'nullable|boolean',
            ]);
            
            $user = auth()->user();
            
                // Создаем новую запись
                $email = $user->contacts()->create([
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
                ])->header('Content-Type', 'application/json');
            }
            return Redirect::back()->with('ok', 'Email добавлен');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $e->errors()
                ], 422)->header('Content-Type', 'application/json');
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('ProfileController: Error in storeEmail', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при сохранении email: ' . $e->getMessage()
                ], 500)->header('Content-Type', 'application/json');
            }
            return Redirect::back()->withErrors(['error' => 'Ошибка при сохранении email']);
        }
    }

    public function updateEmail(Request $request, UserContact $email)
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
            ])->header('Content-Type', 'application/json');
        }
        return Redirect::back()->with('ok', 'Email обновлен');
    }

    public function destroyEmail(Request $request, UserContact $email)
    {
        abort_if($email->user_id !== auth()->id(), 403);
        abort_if($email->type !== 'email', 404);
        
        // Удаляем запись вместо установки NULL
        $email->delete();
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email удален',
            ])->header('Content-Type', 'application/json');
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
    public function storeDocument(Request $request)
    {
        try {
            $data = $this->validateDoc($request);
            
            // Проверяем, существует ли уже документ такого типа у пользователя
            $existingDocument = auth()->user()->documents()
                ->where('type', $data['type'])
                ->first();
            
            // Если документ существует, добавляем фотки к существующему документу
            if ($existingDocument) {
                
                // Обработка файлов - добавляем к существующим
                $files = $existingDocument->files ?? [];
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
                
                // Обновляем существующий документ
                $existingDocument->update(['files' => $files]);
                $existingDocument->refresh();
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true, 
                        'message' => 'Фотографии добавлены к существующему документу',
                        'document' => [
                            'id' => $existingDocument->id,
                            'type' => $existingDocument->type,
                            'files' => $existingDocument->files
                        ]
                    ]);
                }
                return Redirect::back()->with('ok', 'Фотографии добавлены к существующему документу');
            }
            
            // Если документа нет, создаем новый
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
                
            // Создаем документ только с нужными полями
            $documentData = [
                'user_id' => auth()->id(),
                'type' => $data['type'],
                'files' => $files
            ];
            
            $document = auth()->user()->documents()->create($documentData);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Документ добавлен',
                    'document' => [
                        'id' => $document->id,
                        'type' => $document->type,
                        'files' => $document->files
                    ]
                ]);
            }
            return Redirect::back()->with('ok', 'Документ добавлен');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('ProfileController: Validation error in storeDocument', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Ошибка валидации',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('ProfileController: Error in storeDocument', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Ошибка при сохранении документа: ' . $e->getMessage()
                ], 500);
            }
            return Redirect::back()->withErrors(['error' => 'Ошибка при сохранении документа: ' . $e->getMessage()]);
        }
    }

    public function updateDocument(Request $request, UserDocument $document)
    {
        try {
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
            
            // Обновляем документ только с нужными полями
            $documentData = [
                'type' => $data['type'],
                'files' => $files
            ];
            
            $document->update($documentData);
            
            // Обновляем документ из базы данных, чтобы получить актуальные данные
            $document->refresh();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Документ обновлен',
                    'document' => [
                        'id' => $document->id,
                        'type' => $document->type,
                        'files' => $document->files
                    ]
                ]);
        }
        return Redirect::back()->with('ok', 'Документ обновлен');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('ProfileController: Validation error in updateDocument', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'document_id' => $document->id
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Ошибка валидации',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('ProfileController: Error in updateDocument', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'document_id' => $document->id
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Ошибка при обновлении документа: ' . $e->getMessage()
                ], 500);
            }
            return Redirect::back()->withErrors(['error' => 'Ошибка при обновлении документа: ' . $e->getMessage()]);
        }
    }

    public function destroyDocument(UserDocument $document)
    {
        try {
        abort_if($document->user_id !== auth()->id(), 403);
        
        // Удаляем файлы
        if ($document->files) {
            foreach ($document->files as $file) {
                    if (is_string($file)) {
                        // Если файл сохранен как строка
                        \Storage::disk('public')->delete($file);
                    } elseif (isset($file['path'])) {
                        // Если файл сохранен как объект
                    \Storage::disk('public')->delete($file['path']);
                }
            }
        }
        
        $document->delete();
        
            if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Документ удален']);
        }
            
        return Redirect::back()->with('ok', 'Документ удален');
        } catch (\Exception $e) {
            \Log::error('Error deleting document: ' . $e->getMessage());
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Ошибка при удалении: ' . $e->getMessage()], 500);
            }
            
            return Redirect::back()->with('error', 'Ошибка при удалении документа');
        }
    }

    public function destroyDocumentPhoto(UserDocument $document, $photoIndex)
    {
        try {
            abort_if($document->user_id !== auth()->id(), 403);
            
            $files = $document->files ?? [];
            
            if (!isset($files[$photoIndex])) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Фотография не найдена'], 404);
                }
                return Redirect::back()->with('error', 'Фотография не найдена');
            }
            
            $fileToDelete = $files[$photoIndex];
            
            // Удаляем файл из хранилища
            if (is_string($fileToDelete)) {
                \Storage::disk('public')->delete($fileToDelete);
            } elseif (isset($fileToDelete['path'])) {
                \Storage::disk('public')->delete($fileToDelete['path']);
            }
            
            // Удаляем элемент из массива
            unset($files[$photoIndex]);
            // Переиндексируем массив
            $files = array_values($files);
            
            // Обновляем документ
            $document->update(['files' => $files]);
            $document->refresh();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Фотография удалена',
                    'document' => [
                        'id' => $document->id,
                        'type' => $document->type,
                        'files' => $document->files
                    ]
                ]);
            }
            
            return Redirect::back()->with('ok', 'Фотография удалена');
        } catch (\Exception $e) {
            \Log::error('Error deleting document photo: ' . $e->getMessage());
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Ошибка при удалении фотографии: ' . $e->getMessage()], 500);
            }
            
            return Redirect::back()->with('error', 'Ошибка при удалении фотографии');
        }
    }

    private function validateDoc(Request $request): array
    {
        $rules = [
            'type' => ['required', 'in:passport,foreign_passport,driver_license'],
            'files.*' => 'required|file|mimes:jpg,jpeg,png|max:10240', // 10MB, только изображения
        ];

        return $request->validate($rules);
    }

    // -------- Основные контакты --------
    public function updatePrimaryEmail(Request $request)
    {
        // Проверяем, что пользователь является админом
        if (!auth()->user()->hasRole('admin')) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен. Только администраторы могут изменять основные контакты.'
                ], 403);
            }
            return Redirect::back()->withErrors(['error' => 'Доступ запрещен. Только администраторы могут изменять основные контакты.']);
        }

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

    public function updatePrimaryPhone(Request $request)
    {
        // Проверяем, что пользователь является админом
        if (!auth()->user()->hasRole('admin')) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен. Только администраторы могут изменять основные контакты.'
                ], 403);
            }
            return Redirect::back()->withErrors(['error' => 'Доступ запрещен. Только администраторы могут изменять основные контакты.']);
        }

        $data = $request->validate([
            'phone' => 'nullable|string|regex:/^[\d+\-()\s]*$/|max:191',
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
