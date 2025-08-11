<!--as-->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Склад оборудования</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<style>
    .selectable-cell {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        position: relative;
        z-index: 1;
    }

    .selectable-cell.selected {
        background-color: #3b82f6 !important;
        border-color: #1d4ed8 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5) !important;
        color: white !important;
    }

    .selectable-cell.selected:hover {
        background-color: #2563eb !important;
    }

    .calendar-block {
        position: relative;
        z-index: 10;
    }

    .calendar-block.merged {
        position: absolute !important;
        z-index: 20 !important;
        border-radius: 4px !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
    }

    .delete-block-btn {
        font-size: 10px;
        padding: 1px 3px;
        border-radius: 3px;
        cursor: pointer;
    }

    .delete-block-btn:hover {
        background-color: rgba(239, 68, 68, 0.1);
    }

    #contextMenu {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        min-width: 200px;
        background: white;
        border: 1px solid #e5e7eb;
        padding: 8px 0;
        display: none;
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    #contextMenu:not(.hidden) {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    #contextMenu button {
        transition: background-color 0.15s ease;
        padding: 8px 16px;
        width: 100%;
        text-align: left;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 14px;
    }

    #contextMenu button:hover {
        background-color: #f3f4f6;
    }

    #contextMenu button.hidden {
        display: none;
    }

    #toast {
        z-index: 9999;
    }

    /* Отключаем горизонтальный скролл всей страницы */
    html, body {
        overflow-x: hidden;
    }

    /* Горизонтальный скролл только внутри контейнера таблицы */
    .table-scroll {
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
    }
</style>

<style>
    /* Убираем спиннеры у input[type=number] */
    .no-spin::-webkit-outer-spin-button,
    .no-spin::-webkit-inner-spin-button{ -webkit-appearance: none; margin: 0; }
    .no-spin{ -moz-appearance: textfield; }
    /* БАЗОВЫЕ СТИЛИ ДЛЯ ЯЧЕЕК - КОМПАКТНЫЕ */
    .time-slot-header,
    .time-slot-cell {
        width: 50px !important;
        min-width: 50px !important;
        max-width: 50px !important;
        flex: 0 0 50px !important;
        box-sizing: border-box !important;
    }

    /* Не даём заголовкам времени наезжать друг на друга при сужении */
    .time-slot-header-4h,
    .time-slot-header-12h,
    .time-slot-header-1d {
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    /* Принудительно устанавливаем ширину для всех ячеек времени */
    #calendarTable th.time-slot-header,
    #calendarTable td.time-slot-cell {
        width: 50px !important;
        min-width: 50px !important;
        max-width: 50px !important;
        flex: 0 0 50px !important;
    }

    /* Специальные стили для интервала 4 часа */
    .time-slot-header-4h,
    .time-slot-cell-4h {
        width: 100px !important;
        min-width: 100px !important;
        max-width: 100px !important;
        flex: 0 0 100px !important;
        box-sizing: border-box !important;
    }

    #calendarTable th.time-slot-header-4h,
    #calendarTable td.time-slot-cell-4h {
        width: 100px !important;
        min-width: 100px !important;
        max-width: 100px !important;
        flex: 0 0 100px !important;
    }

    /* Специальные стили для интервалов недели */
    .time-slot-header-12h,
    .time-slot-cell-12h {
        width: 100px !important;
        min-width: 100px !important;
        max-width: 100px !important;
        flex: 0 0 100px !important;
        box-sizing: border-box !important;
    }

    #calendarTable th.time-slot-header-12h,
    #calendarTable td.time-slot-cell-12h {
        width: 100px !important;
        min-width: 100px !important;
        max-width: 100px !important;
        flex: 0 0 100px !important;
    }

    /* Чуть меньший шрифт для недельного вида с 12ч */
    .time-slot-header-12h { font-size: 10px !important; }

    .time-slot-header-1d,
    .time-slot-cell-1d {
        width: 100px !important;
        min-width: 100px !important;
        max-width: 100px !important;
        flex: 0 0 100px !important;
        box-sizing: border-box !important;
    }

    #calendarTable th.time-slot-header-1d,
    #calendarTable td.time-slot-cell-1d {
        width: 100px !important;
        min-width: 100px !important;
        max-width: 100px !important;
        flex: 0 0 100px !important;
    }

    /* Адаптивность */
    @media (max-width: 768px) {
        .table-scroll {
            overflow-x: auto;
        }

        #calendarTable {
            min-width: max-content;
        }

        .time-slot-header,
        .time-slot-cell {
            width: 40px !important;
            min-width: 40px !important;
            max-width: 40px !important;
            flex: 0 0 40px !important;
        }

        #calendarTable th.time-slot-header,
        #calendarTable td.time-slot-cell {
            width: 40px !important;
            min-width: 40px !important;
            max-width: 40px !important;
            flex: 0 0 40px !important;
        }
    }

    @media (max-width: 480px) {
        .time-slot-header,
        .time-slot-cell {
            width: 35px !important;
            min-width: 35px !important;
            max-width: 35px !important;
            flex: 0 0 35px !important;
        }

        #calendarTable th.time-slot-header,
        #calendarTable td.time-slot-cell {
            width: 35px !important;
            min-width: 35px !important;
            max-width: 35px !important;
            flex: 0 0 35px !important;
        }
    }
</style>
<body>
<div class="bg-white shadow rounded-lg">
    @include('layouts.navigation')
     <!-- Заголовок -->


     <!-- Фильтры и управление -->
     <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
         <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
             <!-- Блок фильтров -->
             <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                 <select id="employeeFilter" class="w-full sm:w-auto rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary pl-3 pr-10 py-2 text-sm">
                     <option value="">Все сотрудники</option>
                     @foreach($employees as $employee)
                         <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                     @endforeach
                 </select>

                 @php
                     $personnelRoles = \App\Models\User::where('admin_id', auth()->id())
                         ->distinct()
                         ->pluck('role')
                         ->filter()
                         ->merge(['нет специальности','manager','admin'])
                         ->unique()
                         ->values();
                 @endphp
                 <select id="specialtyFilter" class="w-full sm:w-auto rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary pl-3 pr-10 py-2 text-sm">
                     <option value="">Все специальности</option>
                     @foreach($personnelRoles as $role)
                         <option value="{{ $role }}">{{ $role }}</option>
                     @endforeach
                 </select>

                 <div class="flex items-center space-x-2 w-full sm:w-auto">
                     <span class="text-sm text-gray-600">Дата:</span>
                     <input type="date" id="calendarDate" class="flex-1 sm:flex-none rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary pl-3 pr-10 py-2 text-sm" value="{{ date('Y-m-d') }}">
                 </div>
             </div>

             <!-- Блок управления -->
             <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                 <div class="flex items-center space-x-1 w-full sm:w-auto">
                     <span class="text-sm text-gray-600 mr-2">Вид:</span>
                     <div class="flex space-x-1">
                         <button class="view-btn px-2 sm:px-3 py-2 text-xs sm:text-sm rounded-lg border bg-blue-600 text-white" data-view="day">День</button>
                         <button class="view-btn px-2 sm:px-3 py-2 text-xs sm:text-sm rounded-lg border bg-white text-gray-700" data-view="week">Неделя</button>
                         <button class="view-btn px-2 sm:px-3 py-2 text-xs sm:text-sm rounded-lg border bg-white text-gray-700" data-view="month">Месяц</button>
                     </div>
                 </div>

                 <div class="flex items-center space-x-2 w-full sm:w-auto">
                     <span class="text-sm text-gray-600">Интервал:</span>
                     <select id="timeInterval" class="flex-1 sm:flex-none rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary pl-3 pr-10 py-2 text-sm">
                         <option value="30m">30 минут</option>
                         <option value="60m" selected>1 час</option>
                         <option value="4h">4 часа</option>
                     </select>
                 </div>
             </div>
         </div>
     </div>

     <!-- Таблица календаря -->
     <div class="px-2 sm:px-8 py-4 sm:py-6">
         <div class="table-scroll overflow-x-auto whitespace-nowrap">
            <table class="divide-y divide-gray-200" id="calendarTable" style="width: 100%; min-width: max-content;">
                 <thead class="bg-gray-50">
                     <tr>
                         <th class="px-4 sm:px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-48 sm:w-64 sticky left-0 bg-gray-50 z-10">
                             Сотрудник
                         </th>
                          @foreach($timeSlots as $slot)
                              <th class="px-1 sm:px-2 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-normal time-slot-header" style="width: 50px !important; min-width: 50px !important;">
                                  {{ $slot }}
                              </th>
                          @endforeach
                     </tr>
                 </thead>
                 <tbody class="bg-white divide-y divide-gray-200">
                     @foreach($employees as $employee)
                         <tr class="employee-row" data-employee-id="{{ $employee->id }}" data-specialty="{{ $employee->role }}">
                             <td class="px-4 sm:px-8 py-4 sm:py-6 whitespace-nowrap sticky left-0 bg-white z-10">
                                 <div class="flex items-center">
                                     <div class="text-sm sm:text-base font-medium text-gray-900">{{ $employee->name }}</div>
                                     <div class="ml-2 text-xs sm:text-base text-gray-500">({{ $employee->role }})</div>
                                 </div>
                             </td>
                             @foreach($timeSlots as $slot)
                                 <td class="px-1 sm:px-2 py-4 sm:py-6 whitespace-nowrap time-slot-cell" style="width: 50px !important; min-width: 50px !important;">
                                     <div class="calendar-cell h-6 sm:h-8 border border-gray-200 hover:bg-blue-50 cursor-pointer transition-colors selectable-cell"
                                          data-employee-id="{{ $employee->id }}"
                                          data-time-slot="{{ $slot }}"
                                          data-date="{{ date('Y-m-d') }}"
                                          data-cell-id="{{ $employee->id }}-{{ $slot }}">
                                         @php
                                             // Проверяем, есть ли назначение для этой ячейки
                                             $assignment = $assignments->where('employee_id', $employee->id)
                                                                      ->where('start', $slot)
                                                                      ->first();
                                             $nonWorkingDay = $nonWorkingDays->where('employee_id', $employee->id)
                                                                           ->where('date', date('Y-m-d'))
                                                                           ->first();
                                         @endphp

                                         @if($assignment)
                                             <div class="calendar-block bg-green-500 border border-green-600 rounded p-1 h-full flex items-center justify-center relative group" data-project-id="{{ $assignment->project_id }}">
                                                 <div class="absolute top-0 right-0 hidden group-hover:block">
                                                     <button class="delete-block-btn bg-red-500 text-white text-xs px-1 py-0.5 rounded"
                                                             onclick="deleteAssignment({{ $assignment->id }})">×</button>
                                                 </div>
                                                 <div class="absolute bottom-0 right-0 hidden group-hover:block">
                                                      <a href="/projects/{{ $assignment->project_id }}"
                                                        class="text-white text-xs underline">Перейти в проект</a>
                                                 </div>
                                             </div>
                                         @elseif($nonWorkingDay)
                                             <div class="calendar-block bg-red-500 border border-red-600 rounded p-1 h-full flex items-center justify-center relative group">
                                                 <div class="absolute top-0 right-0 hidden group-hover:block">
                                                     <button class="delete-block-btn bg-red-500 text-white text-xs px-1 py-0.5 rounded"
                                                             onclick="deleteNonWorkingDay({{ $nonWorkingDay->id }})">×</button>
                                                 </div>
                                             </div>
                                         @endif
                                     </div>
                                 </td>
                             @endforeach
                         </tr>
                     @endforeach
                 </tbody>
             </table>
         </div>
     </div>
</div>

<!-- Контекстное меню -->
<div id="contextMenu" class="fixed bg-white border border-gray-300 rounded-lg shadow-lg z-50 hidden">
    <div class="py-1">
        <button id="assignProjectBtn" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center">
            <span class="mr-2">✅</span> Назначить на проект
        </button>
        <button id="markNonWorkingBtn" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center">
            <span class="mr-2">🟥</span> Отметить как нерабочее время
        </button>
        <button id="deleteBlockBtn" class="w-full text-left px-4 py-2 text-sm hover:bg-red-100 text-red-600 flex items-center hidden">
            Удалить
        </button>
    </div>
</div>

<!-- Toast уведомления отключены -->



<!-- Модалка назначения на проект -->
<div id="assignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center h-full w-full hidden z-50">
    <div class="bg-white rounded-lg shadow-lg border w-11/12 max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Добавление персонала на мероприятие</h3>
                <button id="closeAssignmentModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="assignmentForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Проект</label>
                    <select id="projectSelect" class="w-full rounded-md border border-gray-300 shadow-sm focus:outline-none" required>
                        <option value="">Выберите проект</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Лист</label>
                    <select id="sheetSelect" class="w-full rounded-md border border-gray-300 shadow-sm focus:outline-none" disabled>
                        <option value="">Недоступно</option>
                    </select>
                </div>


                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Сотрудник</label>
                    <input type="text" id="employeeInput" class="w-full rounded-md border border-gray-300 shadow-sm focus:outline-none" placeholder="Иван Иванов" readonly>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Сумма</label>
                    <input type="number" id="sumInput" class="w-full rounded-md border border-gray-300 shadow-sm focus:outline-none no-spin" placeholder="Введите сумму" min="1" step="1" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Комментарий</label>
                    <textarea id="commentInput" class="w-full rounded-md border border-gray-300 shadow-sm focus:outline-none" rows="3" placeholder="Добавьте комментарий"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" id="cancelAssignment" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                        Закрыть
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md hover:bg-blue-600">
                        Добавить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модалка добавления нерабочего дня -->
<div id="nonWorkingDayModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Добавить нерабочий день</h3>
                <button id="closeNonWorkingDayModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('personnel.non-working') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Сотрудник</label>
                    <select name="employee_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary" required>
                        <option value="">Выберите сотрудника</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Дата</label>
                    <input type="date" name="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary" required>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" id="cancelNonWorkingDay" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                        Отмена
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700">
                        Добавить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>







<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentView = 'day';
    let currentInterval = '60m';
    let selectedCells = new Set();
    let isSelecting = false;
    let selectionStart = null;
    let lastSelectedCell = null;
    let lastMouseX = 0; // Для отслеживания направления движения
    let isMovingRight = true; // Направление движения
    let previousCell = null; // Предыдущая ячейка для определения направления
    let mouseStartX = 0; // Начальная позиция мыши
    let currentMouseX = 0; // Текущая позиция мыши
    let lastDirection = null; // Последнее направление движения
    // Роли текущего админа (динамически из БД)
    const rolesPersonnel = @json($personnelRoles);

    // Вспомогательные функции для расчёта конца интервала
    function getStepMinutes() {
        switch (currentInterval) {
            case '30m': return 30;
            case '60m': return 60;
            case '4h': return 240;
            case '12h': return 720;
            case '1d': return 1440;
            default: return 60;
        }
    }

    function timeToMinutes(hhmm) {
        const m = String(hhmm).match(/^(\d{1,2}):(\d{2})/);
        if (!m) return NaN;
        return parseInt(m[1], 10) * 60 + parseInt(m[2], 10);
    }

    function minutesToHHMM(total) {
        const h = Math.floor(total / 60);
        const m = total % 60;
        return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
    }

    function addMinutesToHHMM(hhmm, minutes) {
        if (!/^[0-2]?\d:\d{2}$/.test(String(hhmm))) return hhmm; // для подписей типа "Пт 08 авг" оставляем как есть
        const [h, m] = String(hhmm).split(':').map(Number);
        let total = h * 60 + m + minutes;
        total = ((total % (24*60)) + (24*60)) % (24*60); // по кругу суток
        const nh = String(Math.floor(total / 60)).padStart(2, '0');
        const nm = String(total % 60).padStart(2, '0');
        return `${nh}:${nm}`;
    }

    function addMinutesClampToDay(hhmm, minutes) {
        const base = timeToMinutes(hhmm);
        if (isNaN(base)) return hhmm;
        let total = base + minutes;
        if (total >= 24*60) total = 24*60 - 1; // 23:59 включительно
        return minutesToHHMM(total);
    }

    function getSortedCellsByTime(cells) {
        return [...cells].sort((a, b) => timeToMinutes(a.dataset.timeSlot) - timeToMinutes(b.dataset.timeSlot));
    }

    function computeRangeInclusive(cells, clampLast = false) {
        const sorted = getSortedCellsByTime(cells);
        const start = sorted[0].dataset.timeSlot;
        const last  = sorted[sorted.length - 1].dataset.timeSlot;
        const step  = getStepMinutes();
        // Базовый конец
        let end   = clampLast ? addMinutesClampToDay(last, step) : addMinutesToHHMM(last, step);
        // Если вышли за предел суток (перешли на 00:00), то ограничиваем 23:59
        const lastMin = timeToMinutes(last);
        const endMin  = timeToMinutes(end);
        if (!isNaN(lastMin) && !isNaN(endMin) && endMin <= lastMin) {
            end = '23:59';
        }
        return { start, end };
    }

    // Парсинг метки недели 12ч вида: "Сб 09 авг, 12:00" -> {date: YYYY-MM-DD, time: HH:MM}
    function parseWeek12hLabel(label, baseDateStr) {
        const months = { 'янв':1,'фев':2,'мар':3,'апр':4,'май':5,'июн':6,'июл':7,'авг':8,'сен':9,'окт':10,'ноя':11,'дек':12 };
        const m = String(label).match(/(\d{1,2})\s+([а-я]{3}),\s*(\d{2}:\d{2})$/i);
        const base = new Date(baseDateStr);
        if (!m) return { date: baseDateStr, time: '00:00' };
        const d = parseInt(m[1],10);
        const mon = months[m[2].toLowerCase()] || (base.getMonth()+1);
        let y = base.getFullYear();
        if (mon === 1 && (base.getMonth()+1) === 12) y += 1; // янв после дек
        if (mon === 12 && (base.getMonth()+1) === 1) y -= 1; // дек перед янв
        const mm = String(mon).padStart(2,'0');
        const dd = String(d).padStart(2,'0');
        return { date: `${y}-${mm}-${dd}`, time: m[3] };
    }

    // Парсинг метки недели 1д: "Сб 09 авг" -> YYYY-MM-DD (используем год от выбранной даты)
    function parseWeek1dDate(label, baseDateStr) {
        const months = { 'янв':1,'фев':2,'мар':3,'апр':4,'май':5,'июн':6,'июл':7,'авг':8,'сен':9,'окт':10,'ноя':11,'дек':12 };
        const m = String(label).match(/\b(\d{1,2})\s+([а-я]{3})$/i);
        const base = new Date(baseDateStr);
        if (!m) return baseDateStr;
        const d = parseInt(m[1],10);
        const mon = months[m[2].toLowerCase()] || (base.getMonth()+1);
        let y = base.getFullYear();
        if (mon === 1 && (base.getMonth()+1) === 12) y += 1;
        if (mon === 12 && (base.getMonth()+1) === 1) y -= 1;
        const mm = String(mon).padStart(2,'0');
        const dd = String(d).padStart(2,'0');
        return `${y}-${mm}-${dd}`;
    }
    let isContextMenuOpen = false; // Флаг открытого контекстного меню

    // Функция восстановления блоков из БД
    function restoreBlocks() {
        const date = document.getElementById('calendarDate').value;
        const cells = document.querySelectorAll('.calendar-cell');

        console.log('Загружаем блоки из БД для даты:', date);

        // Загружаем данные из БД
        // Собираем все даты, присутствующие в текущей сетке (для недели/месяца)
        const gridDates = new Set();
        document.querySelectorAll('#calendarTable thead th').forEach((th, idx) => {
            if (idx === 0) return; // пропустить первый столбец "Сотрудник"
            const label = th.textContent.trim();
            if (currentView === 'week') {
                if (currentInterval === '12h') {
                    gridDates.add(parseWeek12hLabel(label, date).date);
                } else if (currentInterval === '1d') {
                    gridDates.add(parseWeek1dDate(label, date));
                }
            } else if (currentView === 'month' && currentInterval === '1d') {
                gridDates.add(parseWeek1dDate(label, date));
            } else {
                gridDates.add(date);
            }
        });

        const datesParam = Array.from(gridDates).join(',');

        fetch(`/personnel/data?dates=${encodeURIComponent(datesParam)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Получены данные из БД:', data);

            // Вспомогательная: переводит 'HH:MM' или 'HH:MM:SS' в минуты
            const toMinutes = (t) => {
                if (!t) return NaN;
                const parts = String(t).split(':');
                const h = parseInt(parts[0] || '0', 10);
                const m = parseInt(parts[1] || '0', 10);
                return h * 60 + m;
            };

            cells.forEach(cell => {
                // Очищаем существующие блоки
                const existingBlocks = cell.querySelectorAll('.calendar-block');
                existingBlocks.forEach(block => block.remove());

                const cellId = cell.dataset.cellId;
                const employeeId = cell.dataset.employeeId;
                const timeSlot = cell.dataset.timeSlot;
                const stepMins = getStepMinutes();
                const slotStartMinutes = /^[0-2]?\d:\d{2}$/.test(timeSlot)
                    ? toMinutes(timeSlot)
                    : toMinutes(parseWeek12hLabel(timeSlot, date).time);
                const slotEndMinutes = Math.min(slotStartMinutes + stepMins, 24*60);

                // Проверяем нерабочие блоки из БД
                const nonWorkingDay = data.nonWorkingDays.find(nwd => {
                    // Отбрасываем нулевые/вырожденные интервалы
                    if (nwd.start_time === nwd.end_time) return false;
                    if (currentView === 'week' && currentInterval === '12h') {
                        // Для недели сравниваем по дате из метки
                        return (
                            nwd.employee_id == employeeId &&
                            nwd.date === parseWeek12hLabel(timeSlot, date).date &&
                            toMinutes(nwd.start_time) < slotEndMinutes &&
                            toMinutes(nwd.end_time) > slotStartMinutes
                        );
                    } else if (currentView === 'week' && currentInterval === '1d') {
                        const d = parseWeek1dDate(timeSlot, date);
                        return nwd.employee_id == employeeId && nwd.date === d;
                    } else if (currentView === 'month' && currentInterval === '1d') {
                        const d = parseWeek1dDate(timeSlot, date);
                        return nwd.employee_id == employeeId && nwd.date === d;
                    } else {
                        // Если на стороне дня нет ни одного "мелкого" слота, то суточные/крупные слоты не должны рисоваться
                        // Проверяем строгую привязку к дате
                        const sameDate = nwd.date === date;
                        return sameDate && (
                            nwd.employee_id == employeeId &&
                            toMinutes(nwd.start_time) < slotEndMinutes &&
                            toMinutes(nwd.end_time) > slotStartMinutes
                        );
                    }
                });

                // Не добавляем блок прямо сейчас — дождёмся проверки пересечений с проектами, чтобы
                // в агрегированных слотах (4ч/12ч/1д) отрисовать смешанный блок при конфликте

                // Проверяем блоки проектов из БД
                const assignment = data.assignments.find(ass => {
                    // Отбрасываем нулевые/вырожденные интервалы
                    if (ass.start_time === ass.end_time) return false;
                    if (currentView === 'week' && currentInterval === '12h') {
                        return (
                            ass.employee_id == employeeId &&
                            ass.date === parseWeek12hLabel(timeSlot, date).date &&
                            toMinutes(ass.start_time) < slotEndMinutes &&
                            toMinutes(ass.end_time) > slotStartMinutes
                        );
                    } else if (currentView === 'week' && currentInterval === '1d') {
                        // Для недели/день каждая ячейка — день; отображаем, если есть любой интервал в этот день
                        const d = parseWeek1dDate(timeSlot, date);
                        return ass.employee_id == employeeId && ass.date === d;
                    } else if (currentView === 'month' && currentInterval === '1d') {
                        // Для месяца/день аналогично: берем день из метки "Пн 18 авг"
                        const d = parseWeek1dDate(timeSlot, date);
                        return ass.employee_id == employeeId && ass.date === d;
                    } else {
                        const sameDate = ass.date === date;
                        return sameDate && (
                            ass.employee_id == employeeId &&
                            toMinutes(ass.start_time) < slotEndMinutes &&
                            toMinutes(ass.end_time) > slotStartMinutes
                        );
                    }
                });

                // Рендер: смешанный блок для агрегированных слотов, иначе одиночный
                const isAggregatedSlot = (currentView === 'day' && currentInterval === '4h') ||
                    (currentView === 'week' && (currentInterval === '12h' || currentInterval === '1d')) ||
                    (currentView === 'month' && currentInterval === '1d');

                if (isAggregatedSlot && nonWorkingDay && assignment) {
                    // Контейнер без отступов, заполняет всю ячейку
                    const block = document.createElement('div');
                    block.className = 'calendar-block mixed-block rounded h-full w-full flex overflow-hidden relative group';
                    // Сохраняем project_id, чтобы работать через контекстное меню
                    block.setAttribute('data-project-id', assignment.project_id);
                    block.style.margin = '0';
                    block.style.padding = '0';
                    block.style.width = '100%';
                    block.style.height = '100%';
                    // Левая половина (off)
                    const left = document.createElement('div');
                    left.style.width = '50%';
                    left.style.height = '100%';
                    left.style.backgroundColor = '#ef4444';
                    // Правая половина (busy)
                    const right = document.createElement('div');
                    right.style.width = '50%';
                    right.style.height = '100%';
                    right.style.backgroundColor = '#22c55e';
                    block.appendChild(left);
                    block.appendChild(right);
                    cell.appendChild(block);
                } else if (nonWorkingDay) {
                    const block = document.createElement('div');
                    block.className = 'calendar-block bg-red-500 border border-red-600 rounded p-1 h-full flex items-center justify-center relative group';
                    cell.appendChild(block);
                } else if (assignment) {
                    const block = document.createElement('div');
                    block.className = 'calendar-block bg-green-500 border border-green-600 rounded p-1 h-full flex items-center justify-center relative group';
                    block.setAttribute('data-project-id', assignment.project_id);
                    cell.appendChild(block);
                }
            });

            console.log('Восстановление блоков завершено для даты:', date);
        })
        .catch(error => {
            console.error('Ошибка при загрузке данных из БД:', error);
        });
    }

    // Вызываем восстановление блоков
    setTimeout(restoreBlocks, 100);

    // Обработчик для кнопки "Назад" в браузере
    window.addEventListener('beforeunload', function() {
        // Сохраняем текущее состояние перед уходом со страницы
        console.log('Сохранение состояния перед уходом со страницы');
    });

    // Обработчик для восстановления при возврате на страницу
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Страница была восстановлена из кэша браузера
            console.log('Страница восстановлена из кэша, восстанавливаем блоки');
            setTimeout(restoreBlocks, 200);
        }
    });

    // Обработчик для восстановления при возврате на страницу (всегда)
    window.addEventListener('focus', function() {
        // Восстанавливаем блоки при возврате фокуса на страницу
        console.log('Возврат фокуса на страницу, восстанавливаем блоки');
        setTimeout(restoreBlocks, 100);
    });

    // Переменные для контекстного меню
    const contextMenu = document.getElementById('contextMenu');
    const assignProjectBtn = document.getElementById('assignProjectBtn');
    const markNonWorkingBtn = document.getElementById('markNonWorkingBtn');
    const deleteBlockBtn = document.getElementById('deleteBlockBtn');

    // Toast уведомления отключены
    function showToast(message) { return; }

    // Функция для обновления доступных интервалов
    function updateIntervalOptions() {
        const intervalSelect = document.getElementById('timeInterval');
        intervalSelect.innerHTML = '';

        let options = [];
        switch (currentView) {
            case 'day':
                options = [
                    { value: '30m', text: '30 минут' },
                    { value: '60m', text: '1 час' },
                    { value: '4h', text: '4 часа' }
                ];
                break;
            case 'week':
                options = [
                    { value: '12h', text: '12 часов' },
                    { value: '1d', text: '1 день' }
                ];
                break;
            case 'month':
                options = [
                    { value: '1d', text: '1 день' }
                ];
                break;
        }

        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option.value;
            optionElement.textContent = option.text;
            if (option.value === currentInterval) {
                optionElement.selected = true;
            }
            intervalSelect.appendChild(optionElement);
        });
    }

    // Функция для обновления таблицы
    async function updateTable() {
        try {
            const selectedDate = document.getElementById('calendarDate').value;
            console.log('Отправляем запрос:', `view=${currentView}&interval=${currentInterval}&date=${selectedDate}`);
            const response = await fetch(`/personnel/time-slots?view=${currentView}&interval=${currentInterval}&date=${selectedDate}`);
            const data = await response.json();
            console.log('Получены данные:', data);

            const table = document.querySelector('#calendarTable');
            const tableScroll = document.querySelector('.table-scroll');
            const thead = table.querySelector('thead tr');
            const tbody = table.querySelector('tbody');

            // Всегда включаем горизонтальный скролл; таблица занимает всю ширину, но может расширяться
            tableScroll.classList.add('overflow-x-auto', 'whitespace-nowrap');
            table.style.width = '100%';
            table.style.minWidth = 'max-content';

            // Обновляем заголовки
            const timeHeader = thead.querySelector('th:first-child');
            thead.innerHTML = '';
            thead.appendChild(timeHeader);

            data.timeSlots.forEach(slot => {
                const th = document.createElement('th');
                let className = 'px-2 py-4 text-center text-xs font-medium text-gray-500 tracking-normal time-slot-header';
                let width = '50px';

                if (currentInterval === '4h') {
                    className = className.replace('time-slot-header', 'time-slot-header-4h');
                    width = '100px';
                } else if (currentInterval === '12h') {
                    className = className.replace('time-slot-header', 'time-slot-header-12h');
                    width = '100px';
                } else if (currentInterval === '1d') {
                    className = className.replace('time-slot-header', 'time-slot-header-1d');
                    width = '100px';
                }

                th.className = className;
                th.style.width = width;
                th.style.minWidth = width;

                // Формат заголовка: время HH:MM, а для 4ч (день) показываем диапазон "HH:MM - HH:MM"
                const slotStr = String(slot);
                const isTimeOnly = /^\d{1,2}(:\d{2})?$/.test(slotStr);
                let startLabel = slotStr;
                if (isTimeOnly) {
                    const parts = slotStr.split(':');
                    const hh = String(parts[0] ?? '').padStart(2, '0');
                    const mm = String(parts[1] ?? '00').padStart(2, '0');
                    startLabel = `${hh}:${mm}`;
                }
                let headerLabel = startLabel;
                if (currentView === 'day' && currentInterval === '4h' && isTimeOnly) {
                    headerLabel = `${startLabel} - ${addMinutesToHHMM(startLabel, 240)}`;
                }
                th.textContent = headerLabel;
                thead.appendChild(th);
            });

            // Обновляем ячейки
            const employees = @json($employees);
            tbody.innerHTML = '';

            employees.forEach(employee => {
                const row = document.createElement('tr');
                row.className = 'employee-row';
                row.dataset.employeeId = employee.id;
                row.dataset.specialty = employee.role;

                // Ячейка с именем сотрудника (sticky)
                const nameCell = document.createElement('td');
                nameCell.className = 'px-4 sm:px-8 py-4 sm:py-6 whitespace-nowrap sticky left-0 bg-white z-10';
                nameCell.innerHTML = `
                    <div class="flex items-center">
                        <div class="text-sm sm:text-base font-medium text-gray-900">${employee.name}</div>
                        <div class="ml-2 text-xs sm:text-base text-gray-500">(${employee.role})</div>
                    </div>
                `;
                row.appendChild(nameCell);

                // Ячейки времени
                data.timeSlots.forEach(slot => {
                    const cell = document.createElement('td');
                    let className = 'px-1 sm:px-2 py-4 sm:py-6 whitespace-nowrap time-slot-cell';
                    let width = '50px';

                    if (currentInterval === '4h') {
                        className = className.replace('time-slot-cell', 'time-slot-cell-4h');
                        width = '100px';
                    } else if (currentInterval === '12h') {
                        className = className.replace('time-slot-cell', 'time-slot-cell-12h');
                        width = '80px';
                    } else if (currentInterval === '1d') {
                        className = className.replace('time-slot-cell', 'time-slot-cell-1d');
                        width = '100px';
                    }

                    cell.className = className;
                    cell.style.width = width;
                    cell.style.minWidth = width;

                    cell.innerHTML = `
                        <div class="calendar-cell h-6 sm:h-8 border border-gray-200 hover:bg-blue-50 cursor-pointer transition-colors selectable-cell"
                            data-employee-id="${employee.id}"
                            data-time-slot="${slot}"
                            data-date="${document.getElementById('calendarDate').value}"
                            data-cell-id="${employee.id}-${slot}">
                        </div>
                    `;
                    row.appendChild(cell);
                });

                tbody.appendChild(row);
            });

            // Переподключаем обработчики событий
            attachCellEventHandlers();

            // Восстанавливаем блоки после обновления таблицы
            setTimeout(restoreBlocks, 100);

        } catch (error) {
            console.error('Ошибка при обновлении таблицы:', error);
        }
    }

    // Функция для подключения обработчиков событий ячеек
    function attachCellEventHandlers() {
        const cells = document.querySelectorAll('.selectable-cell');

        cells.forEach(cell => {
            // Обработчик mousedown для начала выделения
            cell.addEventListener('mousedown', function(e) {
                if (isContextMenuOpen) {
                    // пока открыто меню — не начинаем новое выделение
                    return;
                }
                if (e.button === 0) { // Левая кнопка мыши
                    e.preventDefault();
                    e.stopPropagation();

                    isSelecting = true;
                    selectionStart = this;
                    lastSelectedCell = this;
                    mouseStartX = e.clientX; // Запоминаем начальную позицию мыши
                    currentMouseX = e.clientX;
                    lastDirection = null; // Сбрасываем направление

                    // Очищаем предыдущий выбор
                    clearSelection();

                    // Добавляем текущую ячейку в выбор
                    selectedCells.add(this);
                    this.classList.add('selected');

                    // Принудительно обновляем стили
                    this.style.backgroundColor = '#3b82f6';
                    this.style.borderColor = '#1d4ed8';
                    this.style.color = 'white';

                    console.log('Начато выделение:', this.dataset.cellId, 'Позиция мыши:', mouseStartX);
                }
            });

            // Обработчик mouseenter для выделения при перетаскивании
            cell.addEventListener('mouseenter', function(e) {
                if (isContextMenuOpen) return; // при открытом меню не расширяем выделение
                if (isSelecting && selectionStart) {
                    // Обновляем текущую позицию мыши
                    const newMouseX = e.clientX;
                    const mouseDelta = newMouseX - currentMouseX;
                    currentMouseX = newMouseX;

                    // Определяем направление движения
                    const currentDirection = mouseDelta > 0 ? 'right' : mouseDelta < 0 ? 'left' : lastDirection;

                    console.log('Мышь движется:', currentDirection, 'Дельта:', mouseDelta, 'Текущая позиция:', newMouseX);

                    // Проверяем, что ячейка находится в той же строке
                    const startEmployeeId = selectionStart.dataset.employeeId;
                    const currentEmployeeId = this.dataset.employeeId;

                    if (startEmployeeId === currentEmployeeId) {
                        // Получаем все ячейки в строке
                        const currentRow = this.closest('.employee-row');
                        const rowCells = Array.from(currentRow.querySelectorAll('.selectable-cell'));
                        const currentCellIndex = rowCells.indexOf(this);
                        const startCellIndex = rowCells.indexOf(selectionStart);

                        // Если направление изменилось, обрабатываем это
                        if (currentDirection && currentDirection !== lastDirection) {
                            console.log('Направление изменилось с', lastDirection, 'на', currentDirection);
                            lastDirection = currentDirection;
                        }

                        // Добавляем ячейку в выбор только если она в той же строке
                        selectedCells.add(this);
                        this.classList.add('selected');
                        lastSelectedCell = this;

                        // Принудительно обновляем стили
                        this.style.backgroundColor = '#3b82f6';
                        this.style.borderColor = '#1d4ed8';
                        this.style.color = 'white';

                        console.log('Добавлена ячейка в выделение:', this.dataset.cellId);
                    } else {
                        console.log('Ячейка в другой строке, пропускаем:', this.dataset.cellId);
                    }
                }
            });

            // Обработчик клика для одиночного выбора (если не было перетаскивания)
            cell.addEventListener('click', function(e) {
                if (isContextMenuOpen) return; // игнор кликов, пока меню открыто
                if (!isSelecting) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Очищаем предыдущий выбор
                    clearSelection();

                    // Добавляем текущую ячейку в выбор
                    selectedCells.add(this);
                    this.classList.add('selected');

                    // Принудительно обновляем стили для визуального выделения
                    this.style.backgroundColor = '#3b82f6';
                    this.style.borderColor = '#1d4ed8';
                    this.style.color = 'white';

                    // Показываем контекстное меню
                    showContextMenu(e, [this]);
                }
            });
        });

        // Обработчик mouseup для завершения выделения
        document.addEventListener('mouseup', function(e) {
            console.log('Mouseup сработал, isSelecting:', isSelecting);
            if (isSelecting) {
                isSelecting = false;

                if (selectedCells.size > 0) {
                    console.log('Завершено выделение. Выбрано ячеек:', selectedCells.size);

                    // Небольшая задержка для гарантии завершения всех событий
                    setTimeout(() => {
                        showContextMenuForSelection();
                    }, 100);
                }
            }
        });

        // Обработчик mouseup на самих ячейках
        cells.forEach(cell => {
            cell.addEventListener('mouseup', function(e) {
                console.log('Mouseup на ячейке:', this.dataset.cellId);
                if (isSelecting) {
                    isSelecting = false;

                    if (selectedCells.size > 0) {
                        console.log('Завершено выделение на ячейке. Выбрано ячеек:', selectedCells.size);

                        // Небольшая задержка для гарантии завершения всех событий
                        setTimeout(() => {
                            showContextMenuForSelection();
                        }, 100);
                    }
                }
            });
        });

        // Обработчик mouseleave для таблицы
        const table = document.querySelector('#calendarTable');
        table.addEventListener('mouseleave', function() {
            if (isSelecting) {
                isSelecting = false;

                if (selectedCells.size > 0) {
                    console.log('Выделение завершено при выходе из таблицы');
                    showContextMenuForSelection();
                }
            }
        });
    }

    // Функция очистки выбора
    function clearSelection() {
        selectedCells.forEach(cell => {
            cell.classList.remove('selected');
            cell.style.backgroundColor = ''; // Сбрасываем стили
            cell.style.borderColor = '';
            cell.style.color = '';
        });
        selectedCells.clear();
        hideContextMenu();
        console.log('Выбор очищен');
    }

    // Функция показа контекстного меню для одиночной ячейки
    function showContextMenu(e, cells) {
        const cell = cells[0];
        const rect = cell.getBoundingClientRect();

        // Проверяем, есть ли уже блок в ячейке
        const hasBlock = cell.querySelector('.calendar-block');

        if (hasBlock) {
            // Проверяем тип блока
            const isProjectBlock = hasBlock.classList.contains('bg-green-500');
            const isNonWorkingBlock = hasBlock.classList.contains('bg-red-500');
            const isMixedBlock = hasBlock.classList.contains('mixed-block');

            if (isProjectBlock || isMixedBlock) {
                // Для зеленых блоков (проекты) показываем "Перейти в проект" и "Удалить"
                deleteBlockBtn.classList.remove('hidden');
                assignProjectBtn.classList.add('hidden');
                markNonWorkingBtn.classList.add('hidden');

                // Добавляем кнопку "Перейти в проект"
                if (!document.getElementById('goToProjectBtn')) {
                    const goToProjectBtn = document.createElement('button');
                    goToProjectBtn.id = 'goToProjectBtn';
                    goToProjectBtn.className = 'w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center';
                    goToProjectBtn.innerHTML = '<span class="mr-2">🔗</span> Перейти в проект';
                    contextMenu.querySelector('.py-1').insertBefore(goToProjectBtn, deleteBlockBtn);
                }
                document.getElementById('goToProjectBtn').classList.remove('hidden');
            } else if (isNonWorkingBlock) {
                // Для красных блоков (нерабочее время) показываем только "Удалить"
                deleteBlockBtn.classList.remove('hidden');
                assignProjectBtn.classList.add('hidden');
                markNonWorkingBtn.classList.add('hidden');
                if (document.getElementById('goToProjectBtn')) {
                    document.getElementById('goToProjectBtn').classList.add('hidden');
                }
            }
        } else {
            // Показываем кнопки назначения
            deleteBlockBtn.classList.add('hidden');
            assignProjectBtn.classList.remove('hidden');
            markNonWorkingBtn.classList.remove('hidden');
            if (document.getElementById('goToProjectBtn')) {
                document.getElementById('goToProjectBtn').classList.add('hidden');
            }
        }

        // Позиционируем меню рядом с ячейкой
        let menuLeft = rect.right + 10;
        let menuTop = rect.top;

        // Проверяем, не выходит ли меню за пределы экрана
        const menuWidth = 200;
        const menuHeight = 150;
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        // Если меню выходит за правый край экрана, показываем слева
        if (menuLeft + menuWidth > windowWidth) {
            menuLeft = rect.left - menuWidth - 10;
        }

        // Если меню выходит за нижний край экрана, поднимаем его
        if (menuTop + menuHeight > windowHeight) {
            menuTop = windowHeight - menuHeight - 10;
        }

        // Если меню выходит за верхний край экрана, опускаем его
        if (menuTop < 10) {
            menuTop = 10;
        }

        // Принудительно показываем меню
        contextMenu.style.cssText = `
            position: fixed !important;
            left: ${menuLeft}px !important;
            top: ${menuTop}px !important;
            z-index: 99999 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            background-color: #ffffff !important;
            border: 2px solid #3b82f6 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
        `;

        contextMenu.classList.remove('hidden');
        isContextMenuOpen = true;
        isContextMenuOpen = true;

        // Сохраняем выбранные ячейки
        contextMenu.dataset.selectedCells = JSON.stringify(Array.from(cells).map(c => c.dataset.cellId));

        console.log('Показано контекстное меню для одной ячейки рядом с ячейкой:', menuLeft, menuTop);
    }

    // Функция показа контекстного меню для множественного выбора
    function showContextMenuForSelection() {
        if (selectedCells.size === 0) {
            console.log('Нет выбранных ячеек для показа меню');
            return;
        }

        console.log('Показываем контекстное меню для', selectedCells.size, 'ячеек');

        const cells = Array.from(selectedCells);

        // Проверяем, есть ли уже блоки в выбранных ячейках
        const hasBlocks = cells.some(cell => cell.querySelector('.calendar-block'));

        console.log('Проверка блоков в выбранных ячейках:', hasBlocks);

        if (hasBlocks) {
            // Проверяем тип блоков
            const hasProjectBlocks = cells.some(cell => {
                const block = cell.querySelector('.calendar-block');
                return block && (block.classList.contains('bg-green-500') || block.classList.contains('mixed-block'));
            });

            const hasNonWorkingBlocks = cells.some(cell => {
                const block = cell.querySelector('.calendar-block');
                return block && block.classList.contains('bg-red-500');
            });

            if (hasProjectBlocks) {
                // Для зеленых блоков (проекты) показываем "Перейти в проект" и "Удалить"
                deleteBlockBtn.classList.remove('hidden');
                assignProjectBtn.classList.add('hidden');
                markNonWorkingBtn.classList.add('hidden');

                // Добавляем кнопку "Перейти в проект"
                if (!document.getElementById('goToProjectBtn')) {
                    const goToProjectBtn = document.createElement('button');
                    goToProjectBtn.id = 'goToProjectBtn';
                    goToProjectBtn.className = 'w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center';
                    goToProjectBtn.innerHTML = '<span class="mr-2">🔗</span> Перейти в проект';
                    contextMenu.querySelector('.py-1').insertBefore(goToProjectBtn, deleteBlockBtn);
                }
                document.getElementById('goToProjectBtn').classList.remove('hidden');
            } else if (hasNonWorkingBlocks) {
                // Для красных блоков (нерабочее время) показываем только "Удалить"
                deleteBlockBtn.classList.remove('hidden');
                assignProjectBtn.classList.add('hidden');
                markNonWorkingBtn.classList.add('hidden');
                if (document.getElementById('goToProjectBtn')) {
                    document.getElementById('goToProjectBtn').classList.add('hidden');
                }
            }
        } else {
            deleteBlockBtn.classList.add('hidden');
            assignProjectBtn.classList.remove('hidden');
            markNonWorkingBtn.classList.remove('hidden');
            if (document.getElementById('goToProjectBtn')) {
                document.getElementById('goToProjectBtn').classList.add('hidden');
            }
        }

        // Позиционируем меню рядом с выделенной областью
        const firstCell = cells[0];
        const lastCell = cells[cells.length - 1];

        const firstRect = firstCell.getBoundingClientRect();
        const lastRect = lastCell.getBoundingClientRect();

        // Вычисляем центр выделенной области
        const centerX = (firstRect.left + lastRect.right) / 2;
        const centerY = (firstRect.top + lastRect.bottom) / 2;

                // Позиционируем меню справа от выделенной области
                const menuLeft = lastRect.right + 10;
                const menuTop = Math.max(firstRect.top, 60); // не перекрывать панель управления

        // Проверяем, не выходит ли меню за пределы экрана
        const menuWidth = 200;
        const menuHeight = 120;
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        let finalLeft = menuLeft;
        let finalTop = menuTop;

        // Если меню выходит за правый край экрана, показываем слева
        if (menuLeft + menuWidth > windowWidth) {
            finalLeft = firstRect.left - menuWidth - 10;
        }

        // Если меню выходит за нижний край экрана, поднимаем его
        if (menuTop + menuHeight > windowHeight) {
            finalTop = windowHeight - menuHeight - 10;
        }

        // Если меню выходит за верхний край экрана, опускаем его
        if (finalTop < 10) {
            finalTop = 10;
        }

        // Принудительно показываем меню с компактными размерами
        contextMenu.style.cssText = `
            position: fixed !important;
            left: ${finalLeft}px !important;
            top: ${finalTop}px !important;
            z-index: 99999 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            background-color: #ffffff !important;
            border: 2px solid #3b82f6 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
            width: 200px !important;
            max-width: 200px !important;
            min-width: 200px !important;
        `;

        contextMenu.classList.remove('hidden');
        isContextMenuOpen = true;
        isContextMenuOpen = true;

        // Сохраняем выбранные ячейки
        contextMenu.dataset.selectedCells = JSON.stringify(cells.map(c => c.dataset.cellId));

        console.log('Контекстное меню показано рядом с выделенной областью:', finalLeft, finalTop);
        console.log('Центр выделения:', centerX, centerY);
    }

    // Функция скрытия контекстного меню
    function hideContextMenu() {
        contextMenu.classList.add('hidden');
        contextMenu.style.display = 'none';
        contextMenu.style.visibility = 'hidden';
        contextMenu.style.opacity = '0';
        isContextMenuOpen = false;
    }

    // Обработчики контекстного меню
    assignProjectBtn.addEventListener('click', function() {
        const selectedCellIds = JSON.parse(contextMenu.dataset.selectedCells || '[]');
        const cells = selectedCellIds.map(id => document.querySelector(`[data-cell-id="${id}"]`));

        // Создаем блок проекта
        createProjectBlock(cells);
        hideContextMenu();
        showToast('Проект назначен');
    });

    markNonWorkingBtn.addEventListener('click', function() {
        const selectedCellIds = JSON.parse(contextMenu.dataset.selectedCells || '[]');
        const cells = selectedCellIds.map(id => document.querySelector(`[data-cell-id="${id}"]`));

        // Создаем блоки нерабочего времени
        createNonWorkingBlock(cells);
        hideContextMenu();
        showToast('Нерабочее время добавлено');
    });

    deleteBlockBtn.addEventListener('click', function() {
        const selectedCellIds = JSON.parse(contextMenu.dataset.selectedCells || '[]');
        const cells = selectedCellIds.map(id => document.querySelector(`[data-cell-id="${id}"]`));

        console.log('Удаляем блоки из', cells.length, 'ячеек');

        // Определяем общий диапазон по выбранным ячейкам
        if (cells.length > 0) {
            const sorted = getSortedCellsByTime(cells);
            const first = sorted[0];
            const last  = sorted[sorted.length - 1];
            const date = document.getElementById('calendarDate').value;
            const employeeId = first.dataset.employeeId;
            const step = getStepMinutes();
            const start = first.dataset.timeSlot;
            const end   = addMinutesClampToDay(last.dataset.timeSlot, step); // включительно последнюю

            fetch('/personnel/clear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ employee_id: employeeId, date, start_time: start, end_time: end })
            })
            .then(r => r.json())
            .then(() => { setTimeout(restoreBlocks, 150); })
            .catch(() => {});
        }

        // Очистим визуальное выделение и меню
        cells.forEach(cell => {
            cell.classList.remove('selected');
            cell.style.backgroundColor = '';
            cell.style.borderColor = '';
            cell.style.color = '';
        });
        selectedCells.clear();
        hideContextMenu();
    });

    // Обработчик для кнопки "Перейти в проект"
    document.addEventListener('click', function(e) {
        if (e.target.id === 'goToProjectBtn') {
            const selectedCellIds = JSON.parse(contextMenu.dataset.selectedCells || '[]');
            const cells = selectedCellIds.map(id => document.querySelector(`[data-cell-id="${id}"]`));

            if (cells.length > 0) {
                const cell = cells[0];
                const block = cell.querySelector('.calendar-block');

                // Получаем ID проекта из блока
                const projectId = block.dataset.projectId;

                if (projectId) {
                    // Сохраняем текущее состояние перед переходом
                    console.log('Переход к проекту:', projectId);

                    // Переходим на страницу проекта (plural)
                    window.location.href = `/projects/${projectId}`;
                } else {
                    // Если ID проекта не найден, показываем уведомление
                    showToast('ID проекта не найден');
                }
            }

            hideContextMenu();
        }
    });

    // Функция создания блока проекта
    function createProjectBlock(cells) {
        if (cells.length === 0) return;

        // Очищаем поля модалки перед открытием
        clearAssignmentModal();

        // Заполняем список специальностей динамически из БД
        populatePersonnelSpecialties();

        // Показываем модалку для выбора проекта
        document.getElementById('assignmentModal').classList.remove('hidden');

        // Сохраняем выбранные ячейки для использования в модалке
        document.getElementById('assignmentModal').dataset.selectedCells = JSON.stringify(cells.map(c => c.dataset.cellId));

        // Заполняем поля модалки данными из первой выбранной ячейки
        const firstCell = cells[0];
        const employeeId = firstCell.dataset.employeeId;

        // Находим имя сотрудника по ID
        const employees = @json($employees);
        const employee = employees.find(emp => emp.id == employeeId);
        if (employee) {
            document.getElementById('employeeInput').value = employee.name;
        }
    }

    function populatePersonnelSpecialties(){
        const sel = document.getElementById('specialtySelect');
        if(!sel) return;
        const roles = Array.isArray(rolesPersonnel)? rolesPersonnel : [];
        const options = ['<option value="" disabled selected hidden>Выберите специальность</option>']
            .concat(roles.map(r=>`<option value="${r}">${r}</option>`));
        sel.innerHTML = options.join('');
    }

    // Функция создания блока нерабочего времени
    function createNonWorkingBlock(cells) {
        if (cells.length === 0) return;

        console.log('Создаем блоки нерабочего времени для', cells.length, 'ячеек');

        // Получаем данные для отправки на сервер
        const selectedDate = document.getElementById('calendarDate').value;
        const sorted = getSortedCellsByTime(cells);
        let date = selectedDate;
        let startTime, endTime;
        const employeeId = sorted[0].dataset.employeeId;

        if (currentView === 'week' && currentInterval === '12h') {
            const firstParsed = (function(lbl){
                const months = { 'янв':1,'фев':2,'мар':3,'апр':4,'май':5,'июн':6,'июл':7,'авг':8,'сен':9,'окт':10,'ноя':11,'дек':12 };
                const m = String(lbl).match(/(\d{1,2})\s+([а-я]{3}),\s*(\d{2}:\d{2})$/i);
                const base = new Date(selectedDate);
                if (!m) return { date: selectedDate, time: '00:00' };
                const d = parseInt(m[1],10);
                const mon = months[m[2].toLowerCase()] || (base.getMonth()+1);
                let y = base.getFullYear();
                if (mon === 1 && (base.getMonth()+1) === 12) y += 1;
                if (mon === 12 && (base.getMonth()+1) === 1) y -= 1;
                const mm = String(mon).padStart(2,'0');
                const dd = String(d).padStart(2,'0');
                return { date: `${y}-${mm}-${dd}`, time: m[3] };
            })(sorted[0].dataset.timeSlot);

            const lastParsed = (function(lbl){
                const months = { 'янв':1,'фев':2,'мар':3,'апр':4,'май':5,'июн':6,'июл':7,'авг':8,'сен':9,'окт':10,'ноя':11,'дек':12 };
                const m = String(lbl).match(/(\d{1,2})\s+([а-я]{3}),\s*(\d{2}:\d{2})$/i);
                const base = new Date(selectedDate);
                if (!m) return { date: selectedDate, time: '00:00' };
                const d = parseInt(m[1],10);
                const mon = months[m[2].toLowerCase()] || (base.getMonth()+1);
                let y = base.getFullYear();
                if (mon === 1 && (base.getMonth()+1) === 12) y += 1;
                if (mon === 12 && (base.getMonth()+1) === 1) y -= 1;
                const mm = String(mon).padStart(2,'0');
                const dd = String(d).padStart(2,'0');
                return { date: `${y}-${mm}-${dd}`, time: m[3] };
            })(sorted[sorted.length - 1].dataset.timeSlot);

            if (firstParsed.date !== lastParsed.date) {
                alert('Выберите слоты в пределах одного дня');
                return;
            }
            date = firstParsed.date;
            startTime = firstParsed.time;
            endTime   = addMinutesClampToDay(lastParsed.time, getStepMinutes());
            const sMin = timeToMinutes(startTime);
            const eMin = timeToMinutes(endTime);
            if (!isNaN(sMin) && !isNaN(eMin) && eMin <= sMin) {
                endTime = '23:59';
            }
        } else {
            const range = computeRangeInclusive(sorted);
            startTime = range.start;
            endTime   = range.end;
        }

        // Отправляем данные на сервер
        fetch('/personnel/non-working', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                employee_id: employeeId,
                date: date,
                start_time: startTime,
                end_time: endTime
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Синхронизируем выбранную дату с датой слота (важно для переключения на День)
                if ((currentView === 'week' && (currentInterval === '12h' || currentInterval === '1d')) ||
                    (currentView === 'month' && currentInterval === '1d')) {
                    const dateInput = document.getElementById('calendarDate');
                    if (dateInput && date) {
                        dateInput.value = date;
                    }
                }
                // Создаем блоки только после успешного сохранения
                cells.forEach((cell, index) => {
                    // Очищаем ячейку
                    cell.innerHTML = '';

                    // Убираем выделение с ячейки
                    cell.classList.remove('selected');
                    cell.style.backgroundColor = '';
                    cell.style.borderColor = '';
                    cell.style.color = '';

                    // Создаем блок
                    const block = document.createElement('div');
                    block.className = 'calendar-block bg-red-500 border border-red-600 rounded p-1 h-full flex items-center justify-center relative group';
                    block.innerHTML = ``;

                    cell.appendChild(block);

                    console.log('Создан блок нерабочего времени в ячейке:', cell.dataset.cellId);
                });

                // Очищаем глобальное выделение
                selectedCells.clear();

                console.log('Созданы блоки нерабочего времени для всех ячеек');
                showToast('Нерабочий день добавлен');
            } else {
                console.error('Ошибка при создании нерабочего дня:', data.message);
                showToast('Ошибка при создании нерабочего дня');
            }
        })
        .catch(error => {
            console.error('Ошибка при отправке данных:', error);
            showToast('Ошибка при создании нерабочего дня');
        });
    }

    // Обработчик клика: если меню открыто и клик вне его — скрываем и очищаем выделение
        document.addEventListener('click', function(e) {
        if (isContextMenuOpen && !contextMenu.contains(e.target)) {
            hideContextMenu();
            if (!e.target.closest('.selectable-cell')) {
                clearSelection();
            }
        }
    });

        // Блокируем контекстное меню браузера на таблице, чтобы не прерывать выбор
        const tableEl = document.getElementById('calendarTable');
        if (tableEl) {
            tableEl.addEventListener('contextmenu', (ev) => {
                ev.preventDefault();
                return false;
            });
        }

    // Обработка кнопок вида
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Убираем активный класс у всех кнопок
            document.querySelectorAll('.view-btn').forEach(b => {
                b.classList.remove('bg-blue-600', 'text-white');
                b.classList.add('bg-white', 'text-gray-700');
            });

            // Добавляем активный класс к текущей кнопке
            this.classList.remove('bg-white', 'text-gray-700');
            this.classList.add('bg-blue-600', 'text-white');

            currentView = this.dataset.view;
            console.log('Изменен вид на', currentView);

            // Устанавливаем правильный интервал по умолчанию для каждого вида
            if (currentView === 'week') {
                currentInterval = '12h';
            } else if (currentView === 'day') {
                currentInterval = '60m';
            } else if (currentView === 'month') {
                currentInterval = '1d';
            }

            console.log('Установлен интервал:', currentInterval);

            // Обновляем интервалы и таблицу
            updateIntervalOptions();
            updateTable();
        });
    });

    // Обработка изменения интервала
    document.getElementById('timeInterval').addEventListener('change', function() {
        currentInterval = this.value;
        console.log('Изменен интервал на', currentInterval);
        updateTable();
    });

    // Обработка отправки формы назначения
    document.getElementById('assignmentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const selectedCellIds = JSON.parse(document.getElementById('assignmentModal').dataset.selectedCells || '[]');
        const cells = selectedCellIds.map(id => document.querySelector(`[data-cell-id="${id}"]`));

        // Получаем данные из формы
        const projectId = document.getElementById('projectSelect').value;
        const projectName = document.getElementById('projectSelect').options[document.getElementById('projectSelect').selectedIndex].text;
        const sheetSelect = document.getElementById('sheetSelect').value;
        const employeeInput = document.getElementById('employeeInput').value;
        const sum = document.getElementById('sumInput').value;
        const comment = document.getElementById('commentInput').value;

        // Валидация
        if (!projectId || !sum || sum < 1) {
            alert('Пожалуйста, заполните все обязательные поля. Сумма должна быть не менее 1.');
            return;
        }

        // Создаем блок проекта
        createProjectBlockFromModal(cells, projectName, projectId);

        // Закрываем модалку
        document.getElementById('assignmentModal').classList.add('hidden');

        // Показываем уведомление
        showToast('Проект назначен');

        // Здесь можно добавить отправку данных на сервер
        console.log('Данные для отправки:', {
            projectId,
            projectName,
            sheetSelect,
            employeeInput,
            sum,
            comment,
            selectedCells: selectedCellIds
        });
    });

    // Функция создания блока проекта из модалки
    function createProjectBlockFromModal(cells, projectName, projectId) {
        if (cells.length === 0) return;

        console.log('Создаем блоки проекта для', cells.length, 'ячеек');

        // Получаем данные для отправки на сервер
        const firstCell = cells[0];
        const employeeId = firstCell.dataset.employeeId;
        const date = document.getElementById('calendarDate').value;
        const sorted = getSortedCellsByTime(cells);
        const { start: startTime, end: endTime } = computeRangeInclusive(sorted);
        const sum = document.getElementById('sumInput').value || 0;
        const comment = document.getElementById('commentInput').value || '';

        // Отправляем данные на сервер
        fetch('/personnel/assign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                employee_id: employeeId,
                project_id: projectId,
                date: date,
                start_time: startTime,
                end_time: endTime,
                sum: sum,
                comment: comment
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Создаем блоки только после успешного сохранения
                cells.forEach((cell, index) => {
                    // Очищаем ячейку
                    cell.innerHTML = '';

                    // Убираем выделение с ячейки
                    cell.classList.remove('selected');
                    cell.style.backgroundColor = '';
                    cell.style.borderColor = '';
                    cell.style.color = '';

                    // Создаем блок
                    const block = document.createElement('div');
                    block.className = 'calendar-block bg-green-500 border border-green-600 rounded p-1 h-full flex items-center justify-center relative group';
                    block.setAttribute('data-project-id', projectId);
                    block.innerHTML = ``;

                    cell.appendChild(block);

                    console.log('Создан блок проекта в ячейке:', cell.dataset.cellId, 'с ID проекта:', projectId);
                });

                // Очищаем глобальное выделение
                selectedCells.clear();

                console.log('Созданы блоки проекта для всех ячеек');
                showToast('Назначение успешно создано');
            } else {
                console.error('Ошибка при создании назначения:', data.message);
                showToast('Ошибка при создании назначения');
            }
        })
        .catch(error => {
            console.error('Ошибка при отправке данных:', error);
            showToast('Ошибка при создании назначения');
        });
    }

    // Функция очистки полей модалки
    function clearAssignmentModal() {
        document.getElementById('projectSelect').value = '';
        document.getElementById('sheetSelect').value = '';
        document.getElementById('employeeInput').value = '';
        document.getElementById('sumInput').value = '';
        document.getElementById('commentInput').value = '';
    }

    // Закрытие модалки назначения
    document.getElementById('closeAssignmentModal').addEventListener('click', function() {
        document.getElementById('assignmentModal').classList.add('hidden');
        clearAssignmentModal();
    });

    document.getElementById('cancelAssignment').addEventListener('click', function() {
        document.getElementById('assignmentModal').classList.add('hidden');
        clearAssignmentModal();
    });

    // Закрытие модалки при клике вне её области
    document.getElementById('assignmentModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
            clearAssignmentModal();
        }
    });

    // Фильтрация по сотрудникам
    document.getElementById('employeeFilter').addEventListener('change', function() {
        const selectedEmployeeId = this.value;
        const rows = document.querySelectorAll('.employee-row');

        rows.forEach(row => {
            if (!selectedEmployeeId || row.dataset.employeeId === selectedEmployeeId) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Фильтрация по специальностям
    document.getElementById('specialtyFilter').addEventListener('change', function() {
        const selectedSpecialty = this.options[this.selectedIndex].text;
        const rows = document.querySelectorAll('.employee-row');

        rows.forEach(row => {
            if (!selectedSpecialty || selectedSpecialty === 'Все специальности' || row.dataset.specialty === selectedSpecialty) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Обработка изменения даты
    document.getElementById('calendarDate').addEventListener('change', function() {
        const newDate = this.value;
        document.querySelectorAll('.calendar-cell').forEach(cell => {
            cell.dataset.date = newDate;
        });
        console.log('Изменена дата на', newDate);

        // Обновляем таблицу при изменении даты
        updateTable();

        // Восстанавливаем блоки для новой даты
        setTimeout(restoreBlocks, 200);
    });

    // Инициализация для интервалов без скролла
    const table = document.querySelector('#calendarTable');
    const tableScroll = document.querySelector('.table-scroll');
    // Инициализация: всегда показываем горизонтальный скролл; таблица тянется на 100%, но при нехватке места активирует скролл
    tableScroll.classList.add('overflow-x-auto', 'whitespace-nowrap');
    table.style.width = '100%';
    table.style.minWidth = 'max-content';

    // Обновляем интервалы и таблицу при загрузке страницы
    updateIntervalOptions();
    updateTable();

    // Инициализация обработчиков событий
    attachCellEventHandlers();
});

// Функции для удаления назначений
function deleteAssignment(assignmentId) {
    if (confirm('Вы уверены, что хотите удалить это назначение?')) {
        fetch(`/personnel/assignment/${assignmentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Ошибка при удалении: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при удалении');
        });
    }
}

function deleteNonWorkingDay(nonWorkingDayId) {
    if (confirm('Вы уверены, что хотите удалить этот нерабочий день?')) {
        // Здесь можно добавить AJAX запрос для удаления нерабочего дня
        location.reload();
    }
}
</script>
</body>
</html>
