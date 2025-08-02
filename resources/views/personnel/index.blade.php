
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css'])
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
</head>
<div class="bg-white shadow rounded-lg">
     @include('layouts.navigation')
     <!-- Заголовок -->

     <!-- Фильтры и управление -->
     <div class="px-6 py-4 border-b border-gray-200">
         <div class="flex justify-between items-center">
             <!-- Блок фильтров -->
             <div class="flex items-center space-x-4">
                 <select id="employeeFilter" class="rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary px-3 py-2 text-sm">
                     <option value="">Все сотрудники</option>
                     @foreach($employees as $employee)
                         <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
                     @endforeach
                 </select>

                 <select id="specialtyFilter" class="rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary px-3 py-2 text-sm">
                     <option value="">Все специальности</option>
                     @foreach($specialties as $specialty)
                         <option value="{{ $specialty['id'] }}">{{ $specialty['name'] }}</option>
                     @endforeach
                 </select>

                                   <div class="flex items-center space-x-2">
                      <span class="text-sm text-gray-600">Дата:</span>
                      <input type="date" id="calendarDate" class="rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary px-3 py-2 text-sm" value="{{ date('Y-m-d') }}">
                  </div>
             </div>

                           <!-- Блок управления -->
              <div class="flex items-center space-x-4">
                                     <div class="flex items-center space-x-1">
                       <span class="text-sm text-gray-600 mr-2">Вид:</span>
                       <button class="view-btn px-3 py-2 text-sm rounded-lg border bg-primary text-white" data-view="day">День</button>
                       <button class="view-btn px-3 py-2 text-sm rounded-lg border" data-view="week">Неделя</button>
                       <button class="view-btn px-3 py-2 text-sm rounded-lg border" data-view="month">Месяц</button>
                   </div>

                                     <div class="flex items-center space-x-2">
                       <span class="text-sm text-gray-600">Интервал:</span>
                                              <select id="timeInterval" class="rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary px-3 py-2 text-sm">
                            <option value="30m">30 минут</option>
                            <option value="60m" selected>1 час</option>
                            <option value="4h">4 часа</option>
                        </select>
                   </div>


              </div>
         </div>
     </div>

                                                                       <!-- Таблица календаря -->
        <div class="px-8 py-6">
                         <div class="table-scroll overflow-x-auto whitespace-nowrap">
                                                               <table class="divide-y divide-gray-200" id="calendarTable" style="min-width: max-content;">
                  <thead class="bg-gray-50">
                      <tr>
                          <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-64 sticky left-0 bg-gray-50 z-10">
                              Сотрудник
                          </th>
                                                     @foreach($timeSlots as $slot)
                                                               <th class="px-2 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider time-slot-header" style="width: 80px !important; min-width: 80px !important;">
                                    {{ $slot }}
                                </th>
                           @endforeach
                      </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                      @foreach($employees as $employee)
                                                     <tr class="employee-row" data-employee-id="{{ $employee['id'] }}" data-specialty="{{ $employee['specialty']['name'] }}">
                              <td class="px-8 py-6 whitespace-nowrap sticky left-0 bg-white z-10">
                                  <div class="flex items-center">
                                      <div class="text-base font-medium text-gray-900">{{ $employee['name'] }}</div>
                                      @if(isset($employee['specialty']))
                                          <div class="ml-2 text-base text-gray-500">({{ $employee['specialty']['name'] }})</div>
                                      @endif
                                  </div>
                              </td>
                                                             @foreach($timeSlots as $slot)
                                                                       <td class="px-2 py-6 whitespace-nowrap time-slot-cell" style="width: 80px !important; min-width: 80px !important;">
                                        <div class="calendar-cell h-12 border border-gray-200 hover:bg-blue-50 cursor-pointer transition-colors selectable-cell"
                                             data-employee-id="{{ $employee['id'] }}"
                                             data-time-slot="{{ $slot }}"
                                             data-date="{{ date('Y-m-d') }}"
                                             data-cell-id="{{ $employee['id'] }}-{{ $slot }}">
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
                              <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
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
                             <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
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


<style>
    .selectable-cell {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }

    .selectable-cell.selected {
        background-color: #dbeafe !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
    }

    .calendar-block {
        position: relative;
        z-index: 10;
    }

    .delete-block-btn {
        font-size: 12px;
        padding: 2px 4px;
        border-radius: 4px;
        cursor: pointer;
    }

    .delete-block-btn:hover {
        background-color: rgba(239, 68, 68, 0.1);
    }

    #contextMenu {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        min-width: 200px;
    }

    #contextMenu button {
        transition: background-color 0.15s ease;
    }

    #contextMenu button:hover {
        background-color: #f3f4f6;
    }

    #toast {
        z-index: 9999;
    }
</style>

<style>
/* БАЗОВЫЕ СТИЛИ ДЛЯ ЯЧЕЕК */
.time-slot-header,
.time-slot-cell {
    width: 80px !important;
    min-width: 80px !important;
    max-width: 80px !important;
    flex: 0 0 80px !important;
    box-sizing: border-box !important;
}

/* Принудительно устанавливаем ширину для всех ячеек времени */
#calendarTable th.time-slot-header,
#calendarTable td.time-slot-cell {
    width: 80px !important;
    min-width: 80px !important;
    max-width: 80px !important;
    flex: 0 0 80px !important;
}

/* Специальные стили для интервала 4 часа */
.time-slot-header-4h,
.time-slot-cell-4h {
    width: 170px !important;
    min-width: 170px !important;
    max-width: 170px !important;
    flex: 0 0 170px !important;
    box-sizing: border-box !important;
}

#calendarTable th.time-slot-header-4h,
#calendarTable td.time-slot-cell-4h {
    width: 170px !important;
    min-width: 170px !important;
    max-width: 170px !important;
    flex: 0 0 170px !important;
}

/* Специальные стили для интервалов недели */
.time-slot-header-12h,
.time-slot-cell-12h {
    width: 120px !important;
    min-width: 120px !important;
    max-width: 120px !important;
    flex: 0 0 120px !important;
    box-sizing: border-box !important;
}

#calendarTable th.time-slot-header-12h,
#calendarTable td.time-slot-cell-12h {
    width: 120px !important;
    min-width: 120px !important;
    max-width: 120px !important;
    flex: 0 0 120px !important;
}

.time-slot-header-1d,
.time-slot-cell-1d {
    width: 150px !important;
    min-width: 150px !important;
    max-width: 150px !important;
    flex: 0 0 150px !important;
    box-sizing: border-box !important;
}

#calendarTable th.time-slot-header-1d,
#calendarTable td.time-slot-cell-1d {
    width: 150px !important;
    min-width: 150px !important;
    max-width: 150px !important;
    flex: 0 0 150px !important;
}
</style>



<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentView = 'day';
    let currentInterval = '60m';
    let selectedCells = new Set();
    let isSelecting = false;
    let selectionStart = null;

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
                    let width = '80px';

                    if (currentInterval === '4h') {
                        className = className.replace('time-slot-header', 'time-slot-header-4h');
                        width = '170px';
                    } else if (currentInterval === '12h') {
                        className = className.replace('time-slot-header', 'time-slot-header-12h');
                        width = '120px';
                    } else if (currentInterval === '1d') {
                        className = className.replace('time-slot-header', 'time-slot-header-1d');
                        width = '150px';
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
                  row.dataset.specialty = employee.specialty.name;

                 // Ячейка с именем сотрудника (sticky)
                 const nameCell = document.createElement('td');
                 nameCell.className = 'px-8 py-6 whitespace-nowrap sticky left-0 bg-white z-10';
                 nameCell.innerHTML = `
                     <div class="flex items-center">
                         <div class="text-base font-medium text-gray-900">${employee.name}</div>
                         <div class="ml-2 text-base text-gray-500">(${employee.specialty.name})</div>
                     </div>
                 `;
                 row.appendChild(nameCell);

                 // Ячейки времени
                                                     data.timeSlots.forEach(slot => {
                      const cell = document.createElement('td');
                      let className = 'px-2 py-6 whitespace-nowrap time-slot-cell';
                      let width = '80px';

                      if (currentInterval === '4h') {
                          className = className.replace('time-slot-cell', 'time-slot-cell-4h');
                          width = '170px';
                      } else if (currentInterval === '12h') {
                          className = className.replace('time-slot-cell', 'time-slot-cell-12h');
                          width = '120px';
                      } else if (currentInterval === '1d') {
                          className = className.replace('time-slot-cell', 'time-slot-cell-1d');
                          width = '150px';
                      }

                      cell.className = className;
                      cell.style.width = width;
                      cell.style.minWidth = width;

                                             cell.innerHTML = `
                          <div class="calendar-cell h-12 border border-gray-200 hover:bg-blue-50 cursor-pointer transition-colors selectable-cell"
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

         } catch (error) {
             console.error('Ошибка при обновлении таблицы:', error);
         }
     }

    // Функция для подключения обработчиков событий ячеек
    function attachCellEventHandlers() {
        const cells = document.querySelectorAll('.selectable-cell');

        cells.forEach(cell => {
            // Обработчик клика для одиночного выбора
            cell.addEventListener('click', function(e) {
                if (!isSelecting) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Очищаем предыдущий выбор
                    clearSelection();

                    // Добавляем текущую ячейку в выбор
                    selectedCells.add(this);
                    this.classList.add('selected');

                    // Показываем контекстное меню
                    showContextMenu(e, [this]);
                }
            });

            // Обработчики для мультивыделения
            cell.addEventListener('mousedown', function(e) {
                if (e.button === 0) { // Левая кнопка мыши
                    isSelecting = true;
                    selectionStart = this;
                    clearSelection();
                    selectedCells.add(this);
                    this.classList.add('selected');
                }
            });

            cell.addEventListener('mouseenter', function(e) {
                if (isSelecting && selectionStart) {
                    selectedCells.add(this);
                    this.classList.add('selected');
                }
            });
        });

        // Обработчик отпускания мыши
        document.addEventListener('mouseup', function() {
            if (isSelecting && selectedCells.size > 0) {
                isSelecting = false;
                showContextMenuForSelection();
            }
        });
    }

    // Функция очистки выбора
    function clearSelection() {
        selectedCells.forEach(cell => {
            cell.classList.remove('selected');
        });
        selectedCells.clear();
        hideContextMenu();
    }

    // Функция показа контекстного меню для одиночной ячейки
    function showContextMenu(e, cells) {
        const cell = cells[0];
        const rect = cell.getBoundingClientRect();

        // Проверяем, есть ли уже блок в ячейке
        const hasBlock = cell.querySelector('.calendar-block');

        if (hasBlock) {
            // Показываем только кнопку удаления
            deleteBlockBtn.classList.remove('hidden');
            assignProjectBtn.classList.add('hidden');
            markNonWorkingBtn.classList.add('hidden');
        } else {
            // Показываем кнопки назначения
            deleteBlockBtn.classList.add('hidden');
            assignProjectBtn.classList.remove('hidden');
            markNonWorkingBtn.classList.remove('hidden');
        }

        // Позиционируем меню
        contextMenu.style.left = rect.right + 'px';
        contextMenu.style.top = rect.top + 'px';
        contextMenu.classList.remove('hidden');

        // Сохраняем выбранные ячейки
        contextMenu.dataset.selectedCells = JSON.stringify(Array.from(cells).map(c => c.dataset.cellId));
    }

    // Функция показа контекстного меню для множественного выбора
    function showContextMenuForSelection() {
        if (selectedCells.size === 0) return;

        const cells = Array.from(selectedCells);
        const firstCell = cells[0];
        const lastCell = cells[cells.length - 1];

        // Проверяем, есть ли уже блоки в выбранных ячейках
        const hasBlocks = cells.some(cell => cell.querySelector('.calendar-block'));

        if (hasBlocks) {
            deleteBlockBtn.classList.remove('hidden');
            assignProjectBtn.classList.add('hidden');
            markNonWorkingBtn.classList.add('hidden');
        } else {
            deleteBlockBtn.classList.add('hidden');
            assignProjectBtn.classList.remove('hidden');
            markNonWorkingBtn.classList.remove('hidden');
        }

        // Позиционируем меню в центре выделения
        const firstRect = firstCell.getBoundingClientRect();
        const lastRect = lastCell.getBoundingClientRect();

        contextMenu.style.left = (firstRect.left + (lastRect.right - firstRect.left) / 2) + 'px';
        contextMenu.style.top = (firstRect.bottom + 5) + 'px';
        contextMenu.classList.remove('hidden');

        // Сохраняем выбранные ячейки
        contextMenu.dataset.selectedCells = JSON.stringify(cells.map(c => c.dataset.cellId));
    }

    // Функция скрытия контекстного меню
    function hideContextMenu() {
        contextMenu.classList.add('hidden');
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

        // Создаем блок нерабочего времени
        createNonWorkingBlock(cells);
        hideContextMenu();
        showToast('Нерабочее время добавлено');
    });

    deleteBlockBtn.addEventListener('click', function() {
        const selectedCellIds = JSON.parse(contextMenu.dataset.selectedCells || '[]');
        const cells = selectedCellIds.map(id => document.querySelector(`[data-cell-id="${id}"]`));

        // Удаляем блоки
        cells.forEach(cell => {
            const block = cell.querySelector('.calendar-block');
            if (block) {
                block.remove();
            }
        });
        hideContextMenu();
        showToast('Блок удалён');
    });

                   // Функция создания блока проекта
      function createProjectBlock(cells) {
          if (cells.length === 0) return;

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

        // Создаем объединенный блок
        const firstCell = cells[0];

        // Очищаем все ячейки
        cells.forEach(cell => {
            cell.innerHTML = '';
        });

                 // Создаем блок в первой ячейке
         const block = document.createElement('div');
         block.className = 'calendar-block bg-red-100 border border-red-300 rounded p-2 h-full flex items-center justify-center relative group';
         block.innerHTML = `
             <span class="text-red-800 font-medium text-sm">Нерабочее время</span>
         `;

         firstCell.appendChild(block);

         // Скрываем остальные ячейки
         for (let i = 1; i < cells.length; i++) {
             cells[i].style.display = 'none';
         }
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
                b.classList.remove('bg-primary', 'text-white');
                b.classList.add('bg-white', 'text-gray-700');
            });

            // Добавляем активный класс к текущей кнопке
            this.classList.remove('bg-white', 'text-gray-700');
            this.classList.add('bg-primary', 'text-white');

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
           createProjectBlockFromModal(cells, projectName);

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
    function createProjectBlockFromModal(cells, projectName) {
        if (cells.length === 0) return;

        // Создаем объединенный блок
        const firstCell = cells[0];

        // Очищаем все ячейки
        cells.forEach(cell => {
            cell.innerHTML = '';
        });

                 // Создаем блок в первой ячейке
         const block = document.createElement('div');
         block.className = 'calendar-block bg-blue-100 border border-blue-300 rounded p-2 h-full flex items-center justify-center relative group';
         block.innerHTML = `
             <span class="text-blue-800 font-medium text-sm">${projectName}</span>
         `;

        firstCell.appendChild(block);

        // Скрываем остальные ячейки
        for (let i = 1; i < cells.length; i++) {
            cells[i].style.display = 'none';
        }
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
</script>

