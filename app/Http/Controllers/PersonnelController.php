<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\NonWorkingDay;
use App\Models\Project;
use App\Models\WorkInterval;
use App\Models\Comment;
use App\Services\ScheduleService;
use App\Models\Role;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonnelController extends Controller
{
    public function index()
    {
        // Устанавливаем московский часовой пояс
        date_default_timezone_set('Europe/Moscow');

        // Загружаем данные через Eloquent модели
        $employees = User::where('admin_id',auth()->id())->get();
        $specialties = Role::all();
        $projects = Project::where('admin_id',auth()->id())->get();

        // Загружаем существующие назначения для отображения
        $assignments = Assignment::with(['employee', 'project'])->get();
        $nonWorkingDays = NonWorkingDay::with('employee')->get();

        // Генерируем временные слоты от 00:00 до 23:00 с шагом 1 час (24 столбца) - по умолчанию
        $timeSlots = [];
        $startTime = Carbon::createFromTime(0, 0, 0);
        $endTime = Carbon::createFromTime(23, 0, 0);

        while ($startTime->lte($endTime)) {
            $timeSlots[] = $startTime->format('H:i');
            $startTime->addHour();
        }

        return view('personnel.index', compact('employees', 'specialties', 'projects', 'timeSlots', 'assignments', 'nonWorkingDays'));
    }

    public function assign(Request $request, ScheduleService $scheduleService)
    {
        // Валидация данных
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'date' => 'required|date',
            'sum' => 'nullable|numeric|min:0',
            'rate_type' => 'nullable|in:hour,project',
            'rate' => 'nullable|numeric|min:0',
            'comment' => 'nullable|string|max:2000',
        ]);

        // Красим интервал как busy (или work, по бизнес-логике). Здесь считаю project=busy
        $scheduleService->paintInterval(
            (int)$validated['employee_id'],
            $validated['date'],
            $validated['start_time'],
            $validated['end_time'],
            'busy'
        );

        // Запишем проект и финансовые/комментарии в интервалы, которые в итоге пересекаются с исходным диапазоном
        $update = [
            'project_id' => $validated['project_id'],
        ];
        if (($validated['rate_type'] ?? null) === 'hour') {
            $update['hour_rate'] = $validated['rate'] ?? null;
            $update['project_rate'] = null;
        } elseif (($validated['rate_type'] ?? null) === 'project') {
            $update['project_rate'] = $validated['rate'] ?? null;
            $update['hour_rate'] = null;
        } else {
            // оставлено для обратной совместимости, но без поля summ
        }

        $affected = WorkInterval::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->where('type', 'busy')
            ->where(function ($q) use ($validated) {
                $q->where('start_time', '<', $validated['end_time'])
                  ->where('end_time', '>', $validated['start_time']);
            })
            ->update($update);

        // Если пересечений не было (или день пуст), но ставка задана — создаём placeholder 00:00–00:00,
        // чтобы «за проект» фиксировалось даже без интервалов
        if (($validated['rate_type'] ?? null) === 'project' || ($validated['rate_type'] ?? null) === 'hour') {
            $hasAny = WorkInterval::where('employee_id', $validated['employee_id'])
                ->where('date', $validated['date'])
                ->where('type', 'busy')
                ->exists();
            if (!$hasAny) {
                WorkInterval::create([
                    'employee_id' => (int)$validated['employee_id'],
                    'project_id'  => (int)$validated['project_id'],
                    'date'        => $validated['date'],
                    'start_time'  => '00:00',
                    'end_time'    => '00:00',
                    'type'        => 'busy',
                    'hour_rate'   => (($validated['rate_type'] ?? null) === 'hour') ? ($validated['rate'] ?? null) : null,
                    'project_rate'=> (($validated['rate_type'] ?? null) === 'project') ? ($validated['rate'] ?? null) : null,
                ]);
            }
        }

        // Если передали комментарий — сохраняем его как отдельную запись в comments
        if (!empty($validated['comment'])) {
            Comment::create([
                'employee_id' => (int)$validated['employee_id'],
                'project_id'  => (int)$validated['project_id'],
                'date'        => $validated['date'],
                'start_time'  => $validated['start_time'],
                'end_time'    => $validated['end_time'],
                'comment'     => $validated['comment'],
            ]);
        }

        // Гарантируем привязку сотрудника к проекту (для раздела Проект → Сотрудники)
        try {
            $project = Project::find((int)$validated['project_id']);
            if ($project) {
                $project->staff()->syncWithoutDetaching([(int)$validated['employee_id']]);
            }
        } catch (\Exception $e) { /* no-op */ }

        return response()->json(['success' => true, 'message' => 'Назначение сохранено']);
    }

    public function addNonWorkingDay(Request $request, ScheduleService $scheduleService)
    {
        try {
            // Валидация данных
            $validated = $request->validate([
                'employee_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
            ]);

            // Красим интервал как off в новой таблице
            $start = $validated['start_time'];
            $end   = $validated['end_time'];
            if (strtotime($end) <= strtotime($start)) {
                $t = strtotime($start) + 60 * 60; // +60 минут
                $end = date('H:i', min($t, strtotime('23:59')));
            }

            $scheduleService->paintInterval(
                (int)$validated['employee_id'],
                $validated['date'],
                $start,
                $end,
                'off'
            );

            return response()->json(['success' => true, 'message' => 'Нерабочий день добавлен']);
        } catch (\Exception $e) {
            \Log::error('Error in addNonWorkingDay: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Очистка выбранного диапазона (вернуть к "work")
    public function clearInterval(Request $request, ScheduleService $scheduleService)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date'        => 'required|date',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
        ]);

        // Fail-safe: если по какой-то причине конец не больше начала, расширим на 60 минут (и не выйдем за день)
        $start = $validated['start_time'];
        $end   = $validated['end_time'];
        if (strtotime($end) <= strtotime($start)) {
            $t = strtotime($start) + 60 * 60; // +60 минут
            $end = date('H:i', min($t, strtotime('23:59')));
        }

        $scheduleService->paintInterval(
            (int)$validated['employee_id'],
            $validated['date'],
            $start,
            $end,
            'work'
        );

        return response()->json(['success' => true]);
    }

    public function getData(Request $request)
    {
        try {
            $date = $request->query('date');
            $datesParam = $request->query('dates');
            $projectId = $request->query('project_id');
            $dates = [];
            if ($datesParam) {
                $dates = array_filter(array_map('trim', explode(',', $datesParam)));
            } elseif ($date) {
                $dates = [$date];
            } else {
                $dates = [date('Y-m-d')];
            }

            // Не возвращаем нулевые интервалы (start_time == end_time), чтобы точечные записи
            // не подсвечивали агрегированные слоты (4ч/12ч/1д)
            // Разделяем выборку: рабочие интервалы фильтруем по проекту, нерабочие — глобальные
            $busyIntervals = WorkInterval::whereIn('date', $dates)
                ->where('type', 'busy')
                ->whereColumn('start_time', '<>', 'end_time')
                ->when($projectId, function ($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                })
                ->get();

            $offIntervals = WorkInterval::whereIn('date', $dates)
                ->where('type', 'off')
                ->whereColumn('start_time', '<>', 'end_time')
                ->get();

            // Временная совместимость со старым фронтом
            $assignments = $busyIntervals->map(function ($i) {
                return [
                    'employee_id' => (int) $i->employee_id,
                    'project_id'  => $i->project_id ? (int) $i->project_id : null,
                    'date'        => (string) $i->date,
                    'start_time'  => (string) $i->start_time,
                    'end_time'    => (string) $i->end_time,
                ];
            })->values();

            $nonWorkingDays = $offIntervals->map(function ($i) {
                return [
                    'employee_id' => (int) $i->employee_id,
                    'date'        => (string) $i->date,
                    'start_time'  => (string) $i->start_time,
                    'end_time'    => (string) $i->end_time,
                ];
            })->values();

            // Комментарии для выбранных дат
            $comments = Comment::whereIn('date', $dates)
                ->when($projectId, function ($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                })
                ->get()->map(function($c){
                return [
                    'employee_id' => (int)$c->employee_id,
                    'project_id'  => $c->project_id ? (int)$c->project_id : null,
                    'date'        => (string)$c->date,
                    'start_time'  => (string)$c->start_time,
                    'end_time'    => (string)$c->end_time,
                    'comment'     => (string)$c->comment,
                ];
            })->values();

            return response()->json([
                'intervals'      => $busyIntervals->concat($offIntervals)->values(),
                'assignments'    => $assignments,
                'nonWorkingDays' => $nonWorkingDays,
                'comments'       => $comments,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getData: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ===== Комментарии API =====
    public function listComments(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'date'        => 'required|date',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i',
            'project_id'  => 'nullable|integer',
        ]);
        $query = Comment::where('employee_id', $validated['employee_id'])
            ->whereDate('date', $validated['date']);
        if (!empty($validated['project_id'])) {
            $query->where('project_id', (int)$validated['project_id']);
        }
        if (!empty($validated['start_time']) && !empty($validated['end_time'])) {
            $slotStart = $validated['start_time'];
            $slotEnd   = $validated['end_time'];
            // Показываем все комментарии, КОТОРЫЕ ПЕРЕСЕКАЮТСЯ с выбранным интервалом (а не только полностью в него вписываются)
            // Условие пересечения по времени: start_time < slotEnd AND end_time > slotStart
            $query->where(function($q) use ($slotStart, $slotEnd) {
                $q->where('start_time', '<', $slotEnd)
                  ->where('end_time',   '>', $slotStart);
            });
        }
        return response()->json($query->orderBy('start_time')->get());
    }

    // Все комментарии сотрудника в проекте (без фильтра по датам/интервалам)
    public function listAllComments(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'project_id'  => 'required|integer',
            'date'        => 'nullable|date',
        ]);
        $items = Comment::where('employee_id', (int)$validated['employee_id'])
            ->where('project_id', (int)$validated['project_id'])
            ->where(function($q){ $q->where('is_global', false)->orWhereNull('is_global'); })
            ->when(!empty($validated['date']), function($q) use ($validated){
                $q->whereDate('date', $validated['date']);
            })
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
        return response()->json($items);
    }

    // Список комментариев за произвольный период (для «Неделя»/«Месяц»)
    public function listCommentsRange(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'date_from'   => 'required|date',
            'date_to'     => 'required|date',
            'interval'    => 'nullable|string', // например: 5m,10m,15m,30m,60m,4h,1h,12h,1d
            'project_id'  => 'nullable|integer',
        ]);

        $query = Comment::where('employee_id', $validated['employee_id'])
            ->whereBetween('date', [$validated['date_from'], $validated['date_to']]);
        if (!empty($validated['project_id'])) {
            $query->where('project_id', (int)$validated['project_id']);
        }

        // Для недель/месяцев показываем только комментарии, оставленные на выбранном интервале (точное совпадение)
        $interval = $validated['interval'] ?? null;
        if ($interval) {
            // Мэппинг интервалов в минуты; 1d — особый кейс
            $map = [
                '5m'  => 5,
                '10m' => 10,
                '15m' => 15,
                '30m' => 30,
                '60m' => 60,
                '1h'  => 60,
                '12h' => 12 * 60,
                '4h'  => 4 * 60,
            ];
            if ($interval === '1d') {
                // Интервал «1 день»: отбираем только записи, у которых slot = полный день
                $query->where('start_time', '00:00')->where('end_time', '23:59');
            } elseif (isset($map[$interval])) {
                $minutes = $map[$interval];
                // Точное совпадение слота: end_time - start_time == $minutes
                // Для TIME используется TIME_TO_SEC(TIMEDIFF(...))
                $query->whereRaw('TIME_TO_SEC(TIMEDIFF(end_time, start_time)) = ?', [$minutes * 60]);
            }
        }

        return response()->json($query->orderBy('date')->orderBy('start_time')->get());
    }

    public function storeComment(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'project_id'  => 'nullable|exists:projects,id',
            'date'        => 'required|date',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
            'comment'     => 'required|string|max:2000',
        ]);
        $c = Comment::create($validated);
        return response()->json(['success'=>true, 'comment'=>$c]);
    }

    public function updateComment(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);
        $comment->update(['comment' => $validated['comment']]);
        return response()->json(['success'=>true]);
    }

    // ===== Общий комментарий проекта =====
    public function getProjectComment(Project $project)
    {
        $c = Comment::where('project_id', $project->id)
            ->where('is_global', true)
            ->orderByDesc('id')
            ->first();
        return response()->json([
            'id' => $c?->id,
            'comment' => $c ? (string)$c->comment : ''
        ]);
    }

    public function saveProjectComment(Request $request, Project $project)
    {
        $validated = $request->validate([
            'comment' => 'nullable|string|max:2000',
        ]);
        $text = (string)($validated['comment'] ?? '');
        // Апсертом: если есть — обновим; если нет — создадим, привязав к дате начала проекта в 00:00
        $existing = Comment::where('project_id', $project->id)->where('is_global', true)->first();
        if ($existing) {
            $existing->update(['comment' => $text]);
            $id = $existing->id;
        } else {
            $date = $project->start_date ? date('Y-m-d', strtotime($project->start_date)) : date('Y-m-d');
            $created = Comment::create([
                'employee_id' => auth()->id(), // технический автор; не используется в выборках общего
                'project_id'  => $project->id,
                'date'        => $date,
                'start_time'  => '00:00',
                'end_time'    => '00:00',
                'comment'     => $text,
                'is_global'   => true,
            ]);
            $id = $created->id;
        }
        return response()->json(['success'=>true, 'id'=>$id]);
    }

    public function deleteComments(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date'        => 'required|date',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
            'project_id'  => 'nullable|exists:projects,id',
        ]);
        // Удаляем все комментарии, ПЕРЕСЕКАЮЩИЕСЯ с выбранным интервалом
        $slotStart = $validated['start_time'];
        $slotEnd   = $validated['end_time'];
        Comment::where('employee_id', $validated['employee_id'])
            ->whereDate('date', $validated['date'])
            ->where(function($q){ $q->where('is_global', false)->orWhereNull('is_global'); })
            ->when(!empty($validated['project_id']), function($q) use ($validated){
                $q->where('project_id', (int)$validated['project_id']);
            })
            ->where(function($q) use ($slotStart, $slotEnd){
                $q->where('start_time', '<', $slotEnd)
                  ->where('end_time',   '>', $slotStart);
            })
            ->delete();
        return response()->json(['success'=>true]);
    }

    public function destroyComment(Comment $comment)
    {
        $comment->delete();
        return response()->json(['success'=>true]);
    }

    public function getTimeSlots(Request $request)
    {
        try {
            $view = $request->query('view', 'day');
            $interval = $request->query('interval', '60m');
            $selectedDate = $request->query('date', date('Y-m-d')); // Получаем выбранную дату

            \Log::info("getTimeSlots called with view: $view, interval: $interval, date: $selectedDate");

            $timeSlots = [];

            // Устанавливаем московский часовой пояс
            date_default_timezone_set('Europe/Moscow');

        switch ($view) {
            case 'day':
                switch ($interval) {
                    case '30m':
                        // 48 колонок: 00:00, 00:30, 01:00, ..., 23:30
                        for ($hour = 0; $hour < 24; $hour++) {
                            for ($minute = 0; $minute < 60; $minute += 30) {
                                $timeSlots[] = sprintf('%02d:%02d', $hour, $minute);
                            }
                        }
                        break;
                    case '60m':
                        // 24 колонки: 00:00, 01:00, 02:00, ..., 23:00
                        for ($hour = 0; $hour < 24; $hour++) {
                            $timeSlots[] = sprintf('%02d:00', $hour);
                        }
                        break;
                    case '4h':
                    default:
                        // 6 колонок: 00:00, 04:00, 08:00, 12:00, 16:00, 20:00
                        for ($hour = 0; $hour < 24; $hour += 4) {
                            $timeSlots[] = sprintf('%02d:00', $hour);
                        }
                        break;
                }
                break;

            case 'week':
                switch ($interval) {
                    case '1h':
                        for ($day = 0; $day < 7; $day++) {
                            for ($hour = 0; $hour < 24; $hour++) {
                                $timeSlots[] = sprintf('%02d-%02d', $day, $hour);
                            }
                        }
                        break;
                    case '12h':
                        $days = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
                        $months = [
                            1 => 'янв', 2 => 'фев', 3 => 'мар', 4 => 'апр',
                            5 => 'май', 6 => 'июн', 7 => 'июл', 8 => 'авг',
                            9 => 'сен', 10 => 'окт', 11 => 'ноя', 12 => 'дек'
                        ];
                        $currentDate = Carbon::parse($selectedDate);

                        for ($day = 0; $day < 7; $day++) {
                            $dayDate = $currentDate->copy()->addDays($day);
                            // Carbon использует 0-6 для дней недели (0 = воскресенье, 1 = понедельник)
                            $dayOfWeek = $dayDate->dayOfWeek;
                            $dayIndex = $dayOfWeek == 0 ? 6 : $dayOfWeek - 1; // Преобразуем в наш формат (0 = понедельник)
                            $dayName = $days[$dayIndex];
                            $month = $months[$dayDate->month];
                            $dateStr = $dayDate->format('d') . ' ' . $month;

                            // 00:00
                            $timeSlots[] = sprintf('%s %s, 00:00', $dayName, $dateStr);
                            // 12:00
                            $timeSlots[] = sprintf('%s %s, 12:00', $dayName, $dateStr);
                        }
                        break;
                    case '1d':
                        $days = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
                        $months = [
                            1 => 'янв', 2 => 'фев', 3 => 'мар', 4 => 'апр',
                            5 => 'май', 6 => 'июн', 7 => 'июл', 8 => 'авг',
                            9 => 'сен', 10 => 'окт', 11 => 'ноя', 12 => 'дек'
                        ];
                        $currentDate = Carbon::parse($selectedDate);

                        for ($day = 0; $day < 7; $day++) {
                            $dayDate = $currentDate->copy()->addDays($day);
                            // Carbon использует 0-6 для дней недели (0 = воскресенье, 1 = понедельник)
                            $dayOfWeek = $dayDate->dayOfWeek;
                            $dayIndex = $dayOfWeek == 0 ? 6 : $dayOfWeek - 1; // Преобразуем в наш формат (0 = понедельник)
                            $dayName = $days[$dayIndex];
                            $month = $months[$dayDate->month];
                            $dateStr = $dayDate->format('d') . ' ' . $month;

                            $timeSlots[] = sprintf('%s %s', $dayName, $dateStr);
                        }
                        break;
                }
                break;

                                                   case 'month':
                  switch ($interval) {
                      case '1d':
                          $days = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
                          $months = [
                              1 => 'янв', 2 => 'фев', 3 => 'мар', 4 => 'апр',
                              5 => 'май', 6 => 'июн', 7 => 'июл', 8 => 'авг',
                              9 => 'сен', 10 => 'окт', 11 => 'ноя', 12 => 'дек'
                          ];
                          $currentDate = Carbon::parse($selectedDate);

                          // Определяем количество дней в месяце (30 или 31)
                          $daysInMonth = 30; // По умолчанию 30 дней

                          // Проверяем, нужно ли показать 31 день
                          // Если выбранная дата + 30 дней попадает в следующий месяц, то показываем 31 день
                          $testDate = $currentDate->copy()->addDays(30);
                          if ($testDate->month !== $currentDate->month) {
                              $daysInMonth = 31;
                          }

                          // Показываем дни от выбранной даты на 30 или 31 день вперед
                          for ($day = 0; $day < $daysInMonth; $day++) {
                              $dayDate = $currentDate->copy()->addDays($day);
                              // Carbon использует 0-6 для дней недели (0 = воскресенье, 1 = понедельник)
                              $dayOfWeek = $dayDate->dayOfWeek;
                              $dayIndex = $dayOfWeek == 0 ? 6 : $dayOfWeek - 1; // Преобразуем в наш формат (0 = понедельник)
                              $dayName = $days[$dayIndex];
                              $month = $months[$dayDate->month];
                              $timeSlots[] = sprintf('%s %d %s', $dayName, $dayDate->day, $month);
                          }
                          break;
                  }
                  break;
        }

        \Log::info("Generated timeSlots: " . count($timeSlots));
        return response()->json(['timeSlots' => $timeSlots]);
        } catch (\Exception $e) {
            \Log::error("Error in getTimeSlots: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
