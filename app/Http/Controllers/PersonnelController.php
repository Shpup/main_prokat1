<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\NonWorkingDay;
use App\Models\Specialty;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PersonnelController extends Controller
{
    public function index()
    {
        // Устанавливаем московский часовой пояс
        date_default_timezone_set('Europe/Moscow');

        // Тестовые данные вместо загрузки из БД
        $employees = [
            ['id' => 1, 'name' => 'Иван Иванов', 'specialty' => ['name' => 'Администратор']],
            ['id' => 2, 'name' => 'Петр Петров', 'specialty' => ['name' => 'Менеджер']],
            ['id' => 3, 'name' => 'Анна Сидорова', 'specialty' => ['name' => 'Администратор']],
        ];

        $specialties = [
            ['id' => 1, 'name' => 'Администратор'],
            ['id' => 2, 'name' => 'Менеджер'],
        ];

        $projects = [
            ['id' => 1, 'name' => 'Веб-сайт компании'],
            ['id' => 2, 'name' => 'Мобильное приложение'],
            ['id' => 3, 'name' => 'Система учета'],
        ];

        // Генерируем временные слоты от 00:00 до 23:00 с шагом 1 час (24 столбца) - по умолчанию
        $timeSlots = [];
        $startTime = Carbon::createFromTime(0, 0, 0);
        $endTime = Carbon::createFromTime(23, 0, 0);

        while ($startTime->lte($endTime)) {
            $timeSlots[] = $startTime->format('H:i');
            $startTime->addHour();
        }

        return view('personnel.index', compact('employees', 'specialties', 'projects', 'timeSlots'));
    }

    public function assign(Request $request)
    {
        // Просто возвращаем успех без сохранения в БД
        return redirect()->route('personnel.index')->with('success', 'Назначение успешно создано (тестовый режим)');
    }

    public function addNonWorkingDay(Request $request)
    {
        // Просто возвращаем успех без сохранения в БД
        return redirect()->route('personnel.index')->with('success', 'Нерабочий день добавлен (тестовый режим)');
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
