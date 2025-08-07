<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\NonWorkingDay;
use App\Models\Project;
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

    public function assign(Request $request)
    {
        // Валидация данных
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'date' => 'required|date',
        ]);

                    // Создаем назначение
            Assignment::create([
                'employee_id' => $validated['employee_id'],
                'project_id' => $validated['project_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'date' => $validated['date'],
            ]);

        return response()->json(['success' => true, 'message' => 'Назначение успешно создано']);
    }

    public function addNonWorkingDay(Request $request)
    {
        try {
            // Валидация данных
            $validated = $request->validate([
                'employee_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
            ]);

            // Создаем нерабочий день
            NonWorkingDay::create([
                'employee_id' => $validated['employee_id'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ]);

            return response()->json(['success' => true, 'message' => 'Нерабочий день добавлен']);
        } catch (\Exception $e) {
            \Log::error('Error in addNonWorkingDay: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        try {
            $date = $request->query('date', date('Y-m-d'));
            
            // Получаем назначения для указанной даты
            $assignments = Assignment::where('date', $date)->get();
            
            // Получаем нерабочие дни для указанной даты
            $nonWorkingDays = NonWorkingDay::where('date', $date)->get();
            
            return response()->json([
                'assignments' => $assignments,
                'nonWorkingDays' => $nonWorkingDays
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
