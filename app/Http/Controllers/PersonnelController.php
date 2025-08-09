<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\NonWorkingDay;
use App\Models\Project;
use App\Models\WorkInterval;
use App\Services\ScheduleService;
use App\Models\Role;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        ]);

        // Красим интервал как busy (или work, по бизнес-логике). Здесь считаю project=busy
        $scheduleService->paintInterval(
            (int)$validated['employee_id'],
            $validated['date'],
            $validated['start_time'],
            $validated['end_time'],
            'busy'
        );

        // Можно дополнительно связать project_id в work_intervals при необходимости
        WorkInterval::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->where('start_time', $validated['start_time'])
            ->where('end_time', $validated['end_time'])
            ->update(['project_id' => $validated['project_id']]);

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
            $intervals = WorkInterval::whereIn('date', $dates)
                ->whereColumn('start_time', '<>', 'end_time')
                ->get();

            // Временная совместимость со старым фронтом
            $assignments = $intervals->where('type', 'busy')->map(function ($i) {
                return [
                    'employee_id' => (int) $i->employee_id,
                    'project_id'  => $i->project_id ? (int) $i->project_id : null,
                    'date'        => (string) $i->date,
                    'start_time'  => (string) $i->start_time,
                    'end_time'    => (string) $i->end_time,
                ];
            })->values();

            $nonWorkingDays = $intervals->where('type', 'off')->map(function ($i) {
                return [
                    'employee_id' => (int) $i->employee_id,
                    'date'        => (string) $i->date,
                    'start_time'  => (string) $i->start_time,
                    'end_time'    => (string) $i->end_time,
                ];
            })->values();

            return response()->json([
                'intervals'      => $intervals,
                'assignments'    => $assignments,
                'nonWorkingDays' => $nonWorkingDays,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getData: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
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
