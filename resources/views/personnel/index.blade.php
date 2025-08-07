

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
</style>

<style>
    /* БАЗОВЫЕ СТИЛИ ДЛЯ ЯЧЕЕК - КОМПАКТНЫЕ */
    .time-slot-header,
    .time-slot-cell {
        width: 50px !important;
        min-width: 50px !important;
        max-width: 50px !important;
        flex: 0 0 50px !important;
        box-sizing: border-box !important;
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
        width: 80px !important;
        min-width: 80px !important;
        max-width: 80px !important;
        flex: 0 0 80px !important;
        box-sizing: border-box !important;
    }

    #calendarTable th.time-slot-header-12h,
    #calendarTable td.time-slot-cell-12h {
        width: 80px !important;
        min-width: 80px !important;
        max-width: 80px !important;
        flex: 0 0 80px !important;
    }

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
                 <select id="employeeFilter" class="w-full sm:w-auto rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary px-3 py-2 text-sm">
                     <option value="">Все сотрудники</option>
                     @foreach($employees as $employee)
                         <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                     @endforeach
                 </select>

                 <select id="specialtyFilter" class="w-full sm:w-auto rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary px-3 py-2 text-sm">
                     <option value="">Все специальности</option>
                     @foreach($specialties as $specialty)
                         <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                     @endforeach
                 </select>

                 <div class="flex items-center space-x-2 w-full sm:w-auto">
                     <span class="text-sm text-gray-600">Дата:</span>
                     <input type="date" id="calendarDate" class="flex-1 sm:flex-none rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary px-3 py-2 text-sm" value="{{ date('Y-m-d') }}">
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
                     <select id="timeInterval" class="flex-1 sm:flex-none rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary px-3 py-2 text-sm">
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
             <table class="divide-y divide-gray-200" id="calendarTable" style="min-width: max-content;">
                 <thead class="bg-gray-50">
                     <tr>
                         <th class="px-4 sm:px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-48 sm:w-64 sticky left-0 bg-gray-50 z-10">
                             Сотрудник
                         </th>
                         @foreach($timeSlots as $slot)
                             <th class="px-1 sm:px-2 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider time-slot-header" style="width: 50px !important; min-width: 50px !important;">
                                 {{ $slot }}
                             </th>
                         @endforeach
                     </tr>
                 </thead>
                 <tbody class="bg-white divide-y divide-gray-200">
                     @foreach($employees as $employee)
                         <tr class="employee-row" data-employee-id="{{ $employee->id }}" data-specialty="{{ $employee->specialty?->name }}">
                             <td class="px-4 sm:px-8 py-4 sm:py-6 whitespace-nowrap sticky left-0 bg-white z-10">
                                 <div class="flex items-center">
                                     <div class="text-sm sm:text-base font-medium text-gray-900">{{ $employee->name }}</div>
                                     <div class="ml-2 text-xs sm:text-base text-gray-500">({{ $employee->specialty?->name }})</div>
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
                                                     <a href="/project/{{ $assignment->project_id }}"
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

<!-- Toast уведомления -->
<div id="toast" class="fixed bottom-4 right-4 bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-y-full transition-transform duration-300">
    <div class="flex items-center">
        <span id="toastMessage"></span>
    </div>
</div>



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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Специальность</label>
                    <select id="specialtySelect" class="w-full rounded-md border border-gray-300 shadow-sm focus:outline-none" required>
                        <option value="">Выберите специальность</option>
                        <option value="administrator">Администратор</option>
                        <option value="manager">Менеджер</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Сотрудник</label>
                    <input type="text" id="employeeInput" class="w-full rounded-md border border-gray-300 shadow-sm focus:outline-none" placeholder="Иван Иванов" readonly>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Сумма</label>
                    <input type="number" id="sumInput" class="w-full rounded-md border border-gray-300 shadow-sm focus:outline-none" placeholder="Введите сумму" min="1" step="1" required>
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

    // Функция восстановления блоков из БД
    function restoreBlocks() {
        const date = document.getElementById('calendarDate').value;
        const cells = document.querySelectorAll('.calendar-cell');
        
        console.log('Загружаем блоки из БД для даты:', date);
        
        // Загружаем данные из БД
        fetch(`/personnel/data?date=${date}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Получены данные из БД:', data);
            
            cells.forEach(cell => {
                // Очищаем существующие блоки
                const existingBlocks = cell.querySelectorAll('.calendar-block');
                existingBlocks.forEach(block => block.remove());
                
                const cellId = cell.dataset.cellId;
                const employeeId = cell.dataset.employeeId;
                const timeSlot = cell.dataset.timeSlot;
                
                // Проверяем нерабочие блоки из БД
                const nonWorkingDay = data.nonWorkingDays.find(nwd => 
                    nwd.employee_id == employeeId && 
                    nwd.date === date &&
                    nwd.start_time <= timeSlot && 
                    nwd.end_time > timeSlot
                );
                
                if (nonWorkingDay) {
                    console.log('Найден нерабочий блок для ячейки:', cellId);
                    // Создаем красный блок
                    const block = document.createElement('div');
                    block.className = 'calendar-block bg-red-500 border border-red-600 rounded p-1 h-full flex items-center justify-center relative group';
                    block.innerHTML = ``;
                    cell.appendChild(block);
                }
                
                // Проверяем блоки проектов из БД
                const assignment = data.assignments.find(ass => 
                    ass.employee_id == employeeId && 
                    ass.date === date &&
                    ass.start_time <= timeSlot && 
                    ass.end_time > timeSlot
                );
                
                if (assignment) {
                    console.log('Найден блок проекта для ячейки:', cellId, 'с ID проекта:', assignment.project_id);
                    // Создаем зеленый блок
                    const block = document.createElement('div');
                    block.className = 'calendar-block bg-green-500 border border-green-600 rounded p-1 h-full flex items-center justify-center relative group';
                    block.setAttribute('data-project-id', assignment.project_id);
                    block.innerHTML = ``;
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

    // Toast уведомления
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');

    function showToast(message) {
        toastMessage.textContent = message;
        toast.classList.remove('translate-y-full');
        setTimeout(() => {
            toast.classList.add('translate-y-full');
        }, 2000);
    }

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

            // Управляем скроллом в зависимости от интервала
            if (currentInterval === '60m' || currentInterval === '4h' || currentInterval === '12h' || (currentView === 'week' && currentInterval === '1d')) {
                tableScroll.classList.remove('overflow-x-auto', 'whitespace-nowrap');
                table.style.minWidth = '100%';
            } else if (currentView === 'month' && currentInterval === '1d') {
                // Для месяца с интервалом "День" включаем горизонтальный скролл
                tableScroll.classList.add('overflow-x-auto', 'whitespace-nowrap');
                table.style.minWidth = 'max-content';
            } else {
                tableScroll.classList.add('overflow-x-auto', 'whitespace-nowrap');
                table.style.minWidth = 'max-content';
            }

            // Обновляем заголовки
            const timeHeader = thead.querySelector('th:first-child');
            thead.innerHTML = '';
            thead.appendChild(timeHeader);

            data.timeSlots.forEach(slot => {
                const th = document.createElement('th');
                let className = 'px-2 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider time-slot-header';
                let width = '50px';

                if (currentInterval === '4h') {
                    className = className.replace('time-slot-header', 'time-slot-header-4h');
                    width = '100px';
                } else if (currentInterval === '12h') {
                    className = className.replace('time-slot-header', 'time-slot-header-12h');
                    width = '80px';
                } else if (currentInterval === '1d') {
                    className = className.replace('time-slot-header', 'time-slot-header-1d');
                    width = '100px';
                }

                th.className = className;
                th.style.width = width;
                th.style.minWidth = width;

                th.textContent = slot;
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
            
            if (isProjectBlock) {
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
        const hasBlocks = cells.some(cell => {
            const block = cell.querySelector('.calendar-block');
            return block !== null;
        });

        console.log('Проверка блоков в выбранных ячейках:', hasBlocks);

        if (hasBlocks) {
            // Проверяем тип блоков
            const hasProjectBlocks = cells.some(cell => {
                const block = cell.querySelector('.calendar-block');
                return block && block.classList.contains('bg-green-500');
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
        const menuTop = firstRect.top;

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

        // Удаляем блоки из всех ячеек и очищаем выделение
        cells.forEach(cell => {
            const block = cell.querySelector('.calendar-block');
            if (block) {
                // Определяем тип блока и удаляем из localStorage
                const cellId = cell.dataset.cellId;
                const date = document.getElementById('calendarDate').value;
                
                if (block.classList.contains('bg-red-500')) {
                    // Удаляем нерабочий блок
                    const userId = {{ auth()->id() ?? 0 }};
                    const nonWorkingKey = `nonworking_${userId}_${date}_${cellId}`;
                    localStorage.removeItem(nonWorkingKey);
                    console.log('Удален нерабочий блок из localStorage:', nonWorkingKey);
                } else if (block.classList.contains('bg-green-500')) {
                    // Удаляем блок проекта
                    const userId = {{ auth()->id() ?? 0 }};
                    const projectKey = `project_${userId}_${date}_${cellId}`;
                    localStorage.removeItem(projectKey);
                    console.log('Удален блок проекта из localStorage:', projectKey);
                }
                
                block.remove();
                console.log('Удален блок из ячейки:', cell.dataset.cellId);
            }

            // Очищаем выделение с ячейки
            cell.classList.remove('selected');
            cell.style.backgroundColor = '';
            cell.style.borderColor = '';
            cell.style.color = '';
            console.log('Очищено выделение ячейки:', cell.dataset.cellId);
        });

        // Очищаем глобальное выделение
        selectedCells.clear();

        hideContextMenu();
        showToast('Блоки удалены, выделение очищено');
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
                    
                    // Переходим на страницу проекта
                    window.location.href = `/project/${projectId}`;
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

    // Функция создания блока нерабочего времени
    function createNonWorkingBlock(cells) {
        if (cells.length === 0) return;

        console.log('Создаем блоки нерабочего времени для', cells.length, 'ячеек');

        // Получаем данные для отправки на сервер
        const firstCell = cells[0];
        const employeeId = firstCell.dataset.employeeId;
        const date = document.getElementById('calendarDate').value;
        const startTime = firstCell.dataset.timeSlot;
        const endTime = cells[cells.length - 1].dataset.timeSlot;

        // Отправляем данные на сервер
        fetch('/personnel/non-working', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
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

    // Обработчик клика вне контекстного меню
    document.addEventListener('click', function(e) {
        if (!contextMenu.contains(e.target)) {
            hideContextMenu();
        }
    });

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
        const specialtySelect = document.getElementById('specialtySelect').value;
        const employeeInput = document.getElementById('employeeInput').value;
        const sum = document.getElementById('sumInput').value;
        const comment = document.getElementById('commentInput').value;

        // Валидация
        if (!projectId || !specialtySelect || !sum || sum < 1) {
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
            specialtySelect,
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
        const startTime = firstCell.dataset.timeSlot;
        const endTime = cells[cells.length - 1].dataset.timeSlot;
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
        document.getElementById('specialtySelect').value = '';
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
    if (currentInterval === '60m' || currentInterval === '4h' || currentInterval === '12h' || (currentView === 'week' && currentInterval === '1d')) {
        tableScroll.classList.remove('overflow-x-auto', 'whitespace-nowrap');
        table.style.minWidth = '100%';
    } else if (currentView === 'month' && currentInterval === '1d') {
        // Для месяца с интервалом "День" включаем горизонтальный скролл
        tableScroll.classList.add('overflow-x-auto', 'whitespace-nowrap');
        table.style.minWidth = 'max-content';
    }

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
