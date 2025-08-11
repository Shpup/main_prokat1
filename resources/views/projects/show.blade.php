<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Склад оборудования</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@include('layouts.navigation')
<div class="container mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">
            Проект: {{ $project->name }}
        </h1>

        <div class="flex items-center gap-3">
            {{-- Бейдж текущего статуса --}}
            @php
                $status = $project->status ?? 'new';
                $statusMap = [
                    'new' => ['label' => 'Новый', 'class' => 'bg-yellow-100 text-yellow-800'],
                    'active' => ['label' => 'В работе', 'class' => 'bg-green-100 text-green-800'],
                    'completed' => ['label' => 'Завершён', 'class' => 'bg-blue-100 text-blue-800'],
                    'cancelled' => ['label' => 'Отменён', 'class' => 'bg-red-100 text-red-800'],
                ];
                $badge = $statusMap[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-gray-100 text-gray-800'];
            @endphp
            <span class="px-3 py-1 text-sm rounded-full font-medium {{ $badge['class'] }}">
                    {{ $badge['label'] }}
                </span>

            @can('edit projects')
                <button
                    type="button"
                    onclick="openModal('changeStatusModal')"
                    class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700"
                >
                    Сменить статус
                </button>
            @endcan
            @can('edit projects')
                <!-- Модалка смены статуса -->
                <div id="changeStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
                    <div class="bg-white rounded-lg p-6 w-full max-w-md">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Сменить статус проекта</h2>
                        <form action="{{ route('projects.updateStatus', $project) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="mb-4">
                                <label for="project_status" class="block text-sm font-medium text-gray-600">Новый статус</label>
                                <select name="status" id="project_status" class="mt-1 block w-full border-gray-300 rounded-md" required>
                                    <option value="new" {{ $project->status === 'new' ? 'selected' : '' }}>Новый</option>
                                    <option value="active" {{ $project->status === 'active' ? 'selected' : '' }}>В работе</option>
                                    <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Завершён</option>
                                    <option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>Отменён</option>
                                </select>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" onclick="closeModal('changeStatusModal')" class="mr-2 bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Отмена</button>
                                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Сохранить</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    function openModal(id) {
                        document.getElementById(id)?.classList.remove('hidden');
                    }
                    function closeModal(id) {
                        document.getElementById(id)?.classList.add('hidden');
                    }
                </script>
            @endcan
        </div>
    </div>


        {{-- Вкладки --}}
        @php
            $tab = request('tab', 'estimate');
        @endphp

        <div class="flex space-x-4 border-b border-gray-200 mb-4">
            <a href="{{ route('projects.show', $project->id) }}?tab=estimate" class="px-4 py-2 {{ $tab === 'estimate' ? 'border-b-2 border-blue-600 font-semibold text-blue-700' : 'text-gray-600' }}">
                Смета
            </a>
            <a href="{{ route('projects.show', $project->id) }}?tab=staff" class="px-4 py-2 {{ $tab === 'staff' ? 'border-b-2 border-blue-600 font-semibold text-blue-700' : 'text-gray-600' }}">
                Персонал
            </a>
            <a href="{{ route('projects.show', $project->id) }}?tab=equipment" class="px-4 py-2 {{ $tab === 'equipment' ? 'border-b-2 border-blue-600 font-semibold text-blue-700' : 'text-gray-600' }}">
                Оборудование
            </a>
        </div>

        {{-- Контент вкладки --}}
        @if ($tab === 'estimate')
            {{-- Смета --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Смета оборудования</h2>

                @php
                    $total = $project->equipment->sum('price');
                @endphp

                @if ($project->equipment->isEmpty())
                    <p class="text-gray-600">Нет прикреплённого оборудования</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 mb-4">
                        <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Название</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Цена</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Статус</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach ($project->equipment as $item)
                            <tr>
                                <td class="px-6 py-4">{{ $item->name }}</td>
                                <td class="px-6 py-4">{{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-4">{{ $item->pivot->status === 'assigned' ? 'прикреплён' : $item->pivot->status }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="text-right font-semibold text-gray-800">
                        Всего: {{ number_format($total, 2) }} ₽
                    </div>
                @endif
            </div>
        @elseif ($tab === 'staff')
            @php
                // Список сотрудников как сущностей — оставляем для колонок Имя/Спец.
                $employeesList = \App\Models\User::where('admin_id', auth()->id())->get(['id','name','role']);
                // Для раздела проекта «Сотрудники» данные по сумме тянем из work_intervals по текущему проекту
                $projectIntervals = \App\Models\WorkInterval::where('project_id', $project->id)
                    ->select(['id','employee_id','project_id','summ','updated_at'])
                    ->orderByDesc('updated_at')
                    ->get()
                    ->groupBy('employee_id');
                // Комментарии теперь в отдельной таблице comments
                $projectComments = \App\Models\Comment::where('project_id', $project->id)
                    ->orderByDesc('created_at')
                    ->get()
                    ->groupBy('employee_id');
                $userPhones = \App\Models\User::where('admin_id', auth()->id())
                    ->get(['id','phone'])
                    ->pluck('phone','id');
                // Полный пул сотрудников админа для модалки «Добавить сотрудника»
                $adminUsersPool = \App\Models\User::where('admin_id', auth()->id())->get(['id','name','role','phone']);
                // Список ролей (как в «Персонале»)
                $adminRoles = \App\Models\User::where('admin_id', auth()->id())
                    ->whereNotNull('role')
                    ->distinct()
                    ->pluck('role');
            @endphp
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-700">Сотрудники</h2>
                    <div class="space-x-2">
                        <button id="openStaffScheduleModal" class="px-3 py-2 text-sm rounded-lg border bg-white text-gray-700">Расписание</button>
                        <button id="openAddStaffModal" class="px-3 py-2 text-sm rounded-lg border bg-blue-600 text-white">Добавить сотрудника</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr class="staff-grid">
                            <th class="staff-cell text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="staffSort(0)">Имя</th>
                            <th class="staff-cell text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="staffSort(1)">Специальность</th>
                            <th class="staff-cell staff-shift text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="staffSort(2)">Телефон</th>
                            <th class="staff-cell staff-shift text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="staffSort(3)">Сумма</th>
                            <th class="staff-cell staff-shift text-left text-xs font-medium text-gray-500 uppercase">Комментарий</th>
                            <th class="staff-cell staff-actions-head text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
                        </tr>
                        </thead>
                        <tbody id="staffTableBody" class="bg-white divide-y divide-gray-200">
                        @foreach($employeesList as $emp)
                            <tr data-emp-id="{{ $emp->id }}" class="group staff-grid hover:bg-gray-50">
                                <td class="staff-cell truncate min-w-[140px]" title="{{ $emp->name }}">{{ $emp->name }}</td>
                                <td class="staff-cell truncate" title="{{ $emp->role ?? '' }}">{{ $emp->role ?? '' }}</td>
                                @php
                                    $ivs = $projectIntervals->get($emp->id) ?? collect();
                                    $iv  = $ivs->first(function($x){ return !is_null($x->summ); }) ?? $ivs->first();
                                    $pc  = ($projectComments->get($emp->id) ?? collect())->first();
                                @endphp
                                <td class="staff-cell staff-shift whitespace-nowrap tabular-nums">{{ $userPhones[$emp->id] ?? '' }}</td>
                                <td class="staff-cell staff-shift whitespace-nowrap tabular-nums">{{ isset($iv) && $iv && $iv->summ !== null ? number_format((float)$iv->summ, 2, ',', ' ') : '' }}</td>
                                <td class="staff-cell staff-shift break-words line-clamp-2" title="{{ $pc->comment ?? '' }}">{{ $pc->comment ?? '' }}</td>
                                <td class="staff-cell text-right">
                                    <div class="icon-row justify-end">
                                        <button type="button" class="icon-btn icon-edit" onclick="editStaff({{ $emp->id }})" aria-label="Редактировать">
                                            <!-- Heroicons Outline: pencil (без квадрата) -->
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.651-1.651a1.875 1.875 0 112.652 2.652L8.832 17.822a4.5 4.5 0 01-1.591 1.06l-2.64.88.88-2.64a4.5 4.5 0 011.06-1.591L16.862 4.487z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125L16.875 4.5"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="icon-btn icon-danger" onclick="deleteStaff({{ $emp->id }})" aria-label="Удалить">
                                            <!-- Heroicons Outline: trash -->
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342-.052.682-.108 1.022-.168M4.772 5.79c.34.06.68.116 1.022.168m2.68-2.29A2.25 2.25 0 0110.5 3h3a2.25 2.25 0 012.25 2.25V6m-9 0h9M5.25 6H18.75M6 6v12.75A2.25 2.25 0 008.25 21h7.5A2.25 2.25 0 0018 18.75V6"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Модалка добавления сотрудника -->
            <div id="addStaffModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 items-center justify-center">
                <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Добавить сотрудника</h3>
                        <button onclick="closeAddStaff()" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>
                    <form id="addStaffForm" class="space-y-3">
                        <select id="staffSpecialty" class="w-full rounded-md border-gray-300" required></select>
                        <select id="staffUser" class="w-full rounded-md border-gray-300" required></select>
                        <input id="staffAmount" type="number" min="0" class="w-full rounded-md border-gray-300 no-spin" placeholder="Сумма" value="0" required />
                        <div class="flex justify-end space-x-2 pt-2">
                            <button type="button" onclick="closeAddStaff()" class="px-3 py-2 text-sm rounded-lg border bg-white text-gray-700">Отмена</button>
                            <button type="submit" class="px-3 py-2 text-sm rounded-lg border bg-blue-600 text-white">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Модалка расписания (день) -->
            <div id="staffScheduleModal" class="fixed inset-0 bg-gray-900 bg-opacity-70 hidden z-50 items-center justify-center">
                <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-5xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-medium text-gray-900">Расписание (День)</h3>
                        <button onclick="closeStaffSchedule()" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>
                    <div class="flex items-center space-x-3 mb-3">
                        <input type="date" id="staffSchedDate" class="rounded-md border-gray-300" value="{{ optional(\Carbon\Carbon::parse($project->start_date))->format('Y-m-d') ?? now()->format('Y-m-d') }}" />
                        <select id="staffSchedInterval" class="rounded-md border-gray-300">
                            <option value="60m" selected>60 минут</option>
                            <option value="30m">30 минут</option>
                            <option value="15m">15 минут</option>
                            <option value="10m">10 минут</option>
                            <option value="5m">5 минут</option>
                        </select>
                    </div>
                    <div id="staffSchedGrid" class="border rounded-lg overflow-x-auto whitespace-nowrap max-w-full"></div>
                </div>
            </div>
            <style>
                /* Убираем спиннеры у input[type=number] */
                .no-spin::-webkit-outer-spin-button,
                .no-spin::-webkit-inner-spin-button{ -webkit-appearance: none; margin: 0; }
                .no-spin{ -moz-appearance: textfield; }
                /* Сетка колонок: 1fr 1fr 1fr 1fr 3fr + фикс */
                .staff-grid{display:grid;grid-template-columns:1fr 1fr 1fr 1fr 3fr 96px;align-items:center;min-height:48px}
                .staff-cell{padding:12px 16px}
                .staff-shift{padding-left:56px}
                .staff-actions-head{padding-right:20px}
                .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
                .tabular-nums{font-variant-numeric:tabular-nums}
                .icon-row{display:flex;justify-content:flex-end;gap:12px;padding-right:4px}
                .icon-btn{display:inline-flex;align-items:center;justify-content:center;width:36px;height:32px;border-radius:8px;cursor:pointer;transition:background-color .15s ease,opacity .15s ease}
                .icon-btn svg{width:20px;height:20px}
                .icon-edit{color:#344054}
                .icon-edit svg{transform: translateY(1px)}
                .icon-edit:hover{background:#EBEBEB}
                .icon-danger{color:#B42318}
                .icon-danger:hover{background:#FEE4E2}
                /* Выравнивание значений в колонке "Сумма" ровно под заголовком */
                #staffTableBody td:nth-child(4){ text-align:left; padding-left:56px; }
                .sched-selected{outline:2px solid rgba(59,130,246,.7)}
                .comment-item{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:4px 8px;border:1px solid #eee;border-radius:6px}
                .comment-item button{color:#B42318}
            </style>
            <!-- ВНЕШНИЕ МОДАЛКИ ДЛЯ РАСПИСАНИЯ (над модалкой расписания) -->
            <div id="schedCommentsModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center" style="z-index:1000;">
                <div class="bg-white rounded-lg w-11/12 max-w-lg shadow-lg">
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <h4 class="text-sm font-medium text-gray-700">Комментарии</h4>
                        <button id="schedCommentsCloseBtn" class="text-gray-500 hover:text-gray-700">✕</button>
                    </div>
                    <div class="p-4 space-y-3 max-h-[60vh] overflow-auto">
                        <div id="schedCommentsMeta" class="text-xs text-gray-500"></div>
                        <div id="schedCommentsList" class="space-y-2"></div>
                        <div class="flex items-center gap-2">
                            <input id="schedCommentTime" type="time" class="border rounded px-2 py-1 text-sm" value="00:00" />
                            <input id="schedCommentText" type="text" class="border rounded px-2 py-1 text-sm flex-1" placeholder="Комментарий" />
                            <button id="schedCommentAddBtn" class="px-3 py-1 text-sm rounded border">Добавить</button>
                        </div>
                    </div>
                    <div class="px-4 py-3 border-t flex justify-end">
                        <button class="px-3 py-2 text-sm rounded border" id="schedCommentsOkBtn">Готово</button>
                    </div>
                </div>
            </div>
            <div id="schedDeleteModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center" style="z-index:1000;">
                <div class="bg-white rounded-lg w-11/12 max-w-sm shadow-lg">
                    <div class="px-4 py-3 border-b text-sm font-medium text-gray-700">Удалить интервал</div>
                    <div class="p-4 text-sm text-gray-700" id="schedDeleteText">Вы действительно хотите удалить выбранный интервал?</div>
                    <div class="px-4 py-3 border-t flex justify-end gap-2">
                        <button class="px-3 py-2 text-sm rounded border" id="schedDeleteCancel">Отмена</button>
                        <button class="px-3 py-2 text-sm rounded bg-red-600 text-white" id="schedDeleteConfirm">Удалить</button>
                    </div>
                </div>
            </div>
        @elseif ($tab === 'equipment')
            {{-- Оборудование --}}
            <div class="flex flex-row gap-8">
                <div class="w-1/3 bg-white rounded-lg shadow-lg p-6 h-fit">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Категории</h2>
                    <div id="categoryTree" class="space-y-3">
                        @foreach (\App\Models\Category::whereNull('parent_id')->with('children', 'user')->get() as $category)
                            @include('equipment.category-item', ['category' => $category, 'depth' => 0])
                        @endforeach
                    </div>
                </div>

                <div class="w-2/3 bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 id="categoryTitle" class="text-xl font-semibold text-gray-700"></h2>
                    </div>
                    <div class="mb-4">
                        <input type="text" id="filterEquipment" placeholder="Фильтр по названию..." class="w-full p-2 border rounded-md">
                    </div>
                    <div id="equipmentList">
                        {{-- Таблица будет загружена через loadEquipment(categoryId) --}}
                    </div>
                </div>
            </div>
        @endif
    </div>
    <script>
        // ====== Сотрудники (таб) ======
        let staffSortDir = 1;
        function staffSort(col){
            const tbody = document.getElementById('staffTableBody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort((a,b)=>{
                const av=(a.cells[col]?.textContent||'').trim();
                const bv=(b.cells[col]?.textContent||'').trim();
                return av.localeCompare(bv, undefined, {numeric:true})*staffSortDir;
            });
            staffSortDir*=-1; rows.forEach(r=>tbody.appendChild(r));
        }
        function openAddStaff(){ document.getElementById('addStaffModal').classList.remove('hidden'); document.getElementById('addStaffModal').classList.add('flex'); }
        function closeAddStaff(){ document.getElementById('addStaffModal').classList.add('hidden'); document.getElementById('addStaffModal').classList.remove('flex'); }
        document.getElementById('openAddStaffModal')?.addEventListener('click', openAddStaff);
        // Инициализация селектов в модалке «Добавить сотрудника»
        const adminUsersPool = @json($adminUsersPool ?? []);
        const rolesPool = @json($adminRoles ?? []);
        function populateAddStaffModal(){
            const specSel = document.getElementById('staffSpecialty');
            const userSel = document.getElementById('staffUser');
            // Сформируем список ролей: добавим предустановленные и уникальные из БД
            const builtin = ['manager','admin'];
            // Исключаем роль 'нет специальности' из общего списка (её показываем отдельным пунктом)
            const rolesOnly = (rolesPool||[]).filter(r=>r && String(r).toLowerCase() !== 'нет специальности');
            const allRolesSet = new Set([...rolesOnly, ...builtin]);
            const roleOptions = Array.from(allRolesSet).map(r=>`<option value="${r}">${r}</option>`).join('');
            // Заголовок: все специальности, потом нет специальности, затем роли
            specSel.innerHTML = '<option value="__all__" selected>все специальности</option>' +
                                '<option value="__none__">нет специальности</option>' +
                                roleOptions;
            // Пользователи админа, доступные для выбора
            userSel.innerHTML = '<option value="">Имя</option>' + adminUsersPool.map(u=>`<option value="${u.id}" data-role="${u.role||''}">${u.name}</option>`).join('');
        }
        // Зависимая фильтрация сотрудников по выбранной «специальности/роли»
        document.getElementById('staffSpecialty')?.addEventListener('change', ()=>{
            const role = document.getElementById('staffSpecialty').value;
            const userSel = document.getElementById('staffUser');
            const all = adminUsersPool;
            let filtered = all;
            if (role === '__all__') {
                filtered = all;
            } else if (role === '__none__') {
                // Вариант 'нет специальности' — ищем пользователей с ролью 'нет специальности'
                filtered = all.filter(u=>String(u.role||'').toLowerCase()==='нет специальности');
            } else if (role) {
                filtered = all.filter(u=>String(u.role||'')===String(role));
            }
            userSel.innerHTML = '<option value="">Имя</option>' + filtered.map(u=>`<option value="${u.id}" data-role="${u.role||''}">${u.name}</option>`).join('');
        });
        // Наполняем при открытии
        document.getElementById('openAddStaffModal')?.addEventListener('click', populateAddStaffModal);
        document.getElementById('addStaffForm')?.addEventListener('submit', async (e)=>{
            e.preventDefault();
            const userId = document.getElementById('staffUser').value;
            const specialtyId = document.getElementById('staffSpecialty').value;
            const amount = document.getElementById('staffAmount').value || 0;
            if(!userId || !specialtyId){ return; }
            // Оптимистичное добавление строки (визуально). Бэкенд-эндпоинт можно подключить позже.
            const tbody = document.getElementById('staffTableBody');
            const tr = document.createElement('tr');
            tr.className = 'group staff-grid hover:bg-gray-50';
            tr.dataset.empId = userId;
            const user = adminUsersPool.find(u=>String(u.id)===String(userId));
            const specLabel = specialtyId==='__all__' ? '' : (specialtyId==='__none__' ? 'нет специальности' : specialtyId);
            tr.innerHTML = `
                <td class="staff-cell truncate min-w-[140px]" title="${user?.name||''}">${user?.name||''}</td>
                <td class="staff-cell truncate" title="${specLabel}">${specLabel}</td>
                <td class="staff-cell staff-shift whitespace-nowrap tabular-nums">${user?.phone||''}</td>
                <td class="staff-cell staff-shift whitespace-nowrap text-right">${Number(amount).toLocaleString('ru-RU', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                <td class="staff-cell staff-shift break-words line-clamp-2" title=""></td>
                <td class="staff-cell text-right">
                    <div class="icon-row justify-end">
                        <button type="button" class="icon-btn icon-edit" onclick="editStaff(${userId})" aria-label="Редактировать">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.651-1.651a1.875 1.875 0 112.652 2.652L8.832 17.822a4.5 4.5 0 01-1.591 1.06l-2.64.88.88-2.64a4.5 4.5 0 011.06-1.591L16.862 4.487z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125L16.875 4.5"/></svg>
                        </button>
                        <button type="button" class="icon-btn icon-danger" onclick="deleteStaff(${userId})" aria-label="Удалить">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342-.052.682-.108 1.022-.168M4.772 5.79c.34.06.68.116 1.022.168m2.68-2.29A2.25 2.25 0 0110.5 3h3a2.25 2.25 0 012.25 2.25V6m-9 0h9M5.25 6H18.75M6 6v12.75A2.25 2.25 0 008.25 21h7.5A2.25 2.25 0 0018 18.75V6"/></svg>
                        </button>
                    </div>
                </td>`;
            tbody.prepend(tr);
            closeAddStaff();
        });
        async function deleteStaff(id){
            const row = document.querySelector(`#staffTableBody tr[data-emp-id="${id}"]`);
            if(row){ row.remove(); }
        }
        function editStaff(id){
            populateAddStaffModal();
            const row = document.querySelector(`#staffTableBody tr[data-emp-id="${id}"]`);
            const userSel = document.getElementById('staffUser');
            const specSel = document.getElementById('staffSpecialty');
            const amountInput = document.getElementById('staffAmount');
            userSel.value = String(id);
            const currentRole = (row?.cells[1]?.textContent || '').trim();
            specSel.value = currentRole
                ? (currentRole === 'нет специальности' ? '__none__' : currentRole)
                : '__all__';
            const sumText = (row?.cells[3]?.textContent||'').trim().replace(/\s/g,'').replace(',', '.');
            const sumVal = parseFloat(sumText||'0') || 0;
            amountInput.value = sumVal;
            openAddStaff();
        }

        // ====== Расписание сотрудника (модалка, день) ======
        let currentStaffId = null;
        function openStaffSchedule(empId, date){ currentStaffId=empId; const m=document.getElementById('staffScheduleModal'); m.classList.remove('hidden'); m.classList.add('flex'); if(date) document.getElementById('staffSchedDate').value=date; renderStaffSchedule(); }
        function closeStaffSchedule(){ const m=document.getElementById('staffScheduleModal'); m.classList.add('hidden'); m.classList.remove('flex'); }
            document.getElementById('openStaffScheduleModal')?.addEventListener('click', ()=>{
            const first = document.querySelector('#staffTableBody tr');
            if(!first){ alert('Нет сотрудников'); return; }
                openStaffSchedule(first.dataset.empId, '{{ optional(\Carbon\Carbon::parse($project->start_date))->format('Y-m-d') ?? now()->format('Y-m-d') }}');
        });
        document.getElementById('staffSchedInterval')?.addEventListener('change', renderStaffSchedule);
        document.getElementById('staffSchedDate')?.addEventListener('change', renderStaffSchedule);

        async function renderStaffSchedule(){
            const grid = document.getElementById('staffSchedGrid'); grid.innerHTML='';
            const interval = document.getElementById('staffSchedInterval').value;
            const date = document.getElementById('staffSchedDate').value;
            const minutes = { '60m':60, '30m':30, '15m':15, '10m':10, '5m':5 }[interval] || 60;
            const slots = []; for(let t=0; t<1440; t+=minutes){ const hh=String(Math.floor(t/60)).padStart(2,'0'); const mm=String(t%60).padStart(2,'0'); slots.push(`${hh}:${mm}`);}

            // Подложка-таблица с шапкой времени и строками сотрудников
            const table = document.createElement('table');
            table.className = 'min-w-max border-collapse';
            const thead = document.createElement('thead');
            const htr = document.createElement('tr');
            const hfirst = document.createElement('th'); hfirst.className='sticky left-0 bg-white text-xs font-medium text-gray-500 px-3 py-2 text-left'; hfirst.textContent='Сотрудник'; htr.appendChild(hfirst);
            slots.forEach(s=>{ const th=document.createElement('th'); th.className='text-[10px] font-normal text-gray-500 px-2 py-1 whitespace-nowrap'; th.textContent=s; htr.appendChild(th); });
            thead.appendChild(htr); table.appendChild(thead);
            const tbody = document.createElement('tbody');

            const users = (Array.isArray(adminUsersPool)? adminUsersPool: []).slice(0,10);
            const res = await fetch(`/personnel/data?date=${date}&project_id={{ $project->id }}`);
            const data = await res.json();
            function timeToMin(t){ const [h,m]=String(t).split(':').map(Number); return (isNaN(h)||isNaN(m))?0:(h*60+m); }

            users.forEach(u=>{
                const tr = document.createElement('tr');
                const nameTd = document.createElement('td');
                nameTd.className='sticky left-0 bg-white text-sm text-gray-800 px-3 py-1 whitespace-nowrap';
                const roleText = u.role ? ` (${u.role})` : '';
                nameTd.textContent = u.name + roleText;
                tr.dataset.empId = u.id;
                tr.appendChild(nameTd);
                const off = (data.nonWorkingDays||[]).filter(x=> x.employee_id==u.id && x.date===date);
                const busy= (data.assignments||[]).filter(x=> x.employee_id==u.id && x.date===date);
                slots.forEach(s=>{
                    const td=document.createElement('td'); td.className='border w-16 h-6 sched-cell';
                    td.dataset.from = s;
                    const sMin = parseInt(s.slice(0,2))*60 + parseInt(s.slice(3,5));
                    const eMin = Math.min(sMin + minutes, 1440);
                    td.dataset.to = `${String(Math.floor(eMin/60)).padStart(2,'0')}:${String(eMin%60).padStart(2,'0')}`;
                    const hasOff = off.some(x=> timeToMin(x.start_time) < eMin && timeToMin(x.end_time) > sMin);
                    const hasBusy= busy.some(x=> timeToMin(x.start_time) < eMin && timeToMin(x.end_time) > sMin);
                    if(hasOff && hasBusy){ td.style.background='linear-gradient(90deg, #ef4444 0 50%, #22c55e 50% 100%)'; }
                    else if(hasOff){ td.classList.add('bg-red-500'); }
                    else if(hasBusy){ td.classList.add('bg-green-500'); }
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
                // узкая разделительная строка
                const spacer = document.createElement('tr');
                const spacerTd = document.createElement('td');
                spacerTd.colSpan = slots.length + 1;
                spacerTd.style.height = '12px';
                spacerTd.style.background = 'transparent';
                spacer.appendChild(spacerTd);
                tbody.appendChild(spacer);
            });
            table.appendChild(tbody);
            grid.appendChild(table);
            // Обработчики для внешних модалок
            const commentsModal = document.getElementById('schedCommentsModal');
            const deleteModal = document.getElementById('schedDeleteModal');
            const openComments = (metaText)=>{ document.getElementById('schedCommentsMeta').textContent = metaText||''; commentsModal.classList.remove('hidden'); commentsModal.classList.add('flex'); };
            const closeComments = ()=>{ commentsModal.classList.add('hidden'); commentsModal.classList.remove('flex'); };
            document.getElementById('schedCommentsCloseBtn').onclick = closeComments;
            document.getElementById('schedCommentsOkBtn').onclick = closeComments;
            const openDelete = (metaText)=>{ document.getElementById('schedDeleteText').textContent = metaText||'Вы действительно хотите удалить выбранный интервал?'; deleteModal.classList.remove('hidden'); deleteModal.classList.add('flex'); };
            const closeDelete = ()=>{ deleteModal.classList.add('hidden'); deleteModal.classList.remove('flex'); };
            document.getElementById('schedDeleteCancel').onclick = closeDelete;
            document.getElementById('schedDeleteConfirm').onclick = ()=>{ closeDelete(); };

            // Выделение диапазона мышью (похоже на «Персонал»)
            let isSelecting = false; let startCell = null; let currentRow = null;
            function clearSelection(){ tbody.querySelectorAll('.sched-selected').forEach(c=>c.classList.remove('sched-selected')); }
            tbody.querySelectorAll('td.sched-cell').forEach(td=>{
                td.addEventListener('mousedown', ()=>{ isSelecting=true; currentRow=td.parentElement; startCell=td; clearSelection(); td.classList.add('sched-selected'); });
                td.addEventListener('mouseenter', ()=>{
                    if(!isSelecting || td.parentElement!==currentRow) return;
                    clearSelection();
                    const cells = Array.from(currentRow.querySelectorAll('td.sched-cell'));
                    const a = cells.indexOf(startCell); const b = cells.indexOf(td);
                    const [fromIdx,toIdx] = a<=b ? [a,b] : [b,a];
                    for(let i=fromIdx;i<=toIdx;i++) cells[i].classList.add('sched-selected');
                });
            });
            const finalizeSelection = async ()=>{
                if(!currentRow) return;
                const selected = Array.from(currentRow.querySelectorAll('td.sched-selected'));
                if(selected.length===0){ currentRow=null; startCell=null; return; }
                const allGreen = selected.every(c=>c.classList.contains('bg-green-500'));
                const allRed   = selected.every(c=>c.classList.contains('bg-red-500'));
                const from = selected.map(c=>c.dataset.from).sort()[0] || '';
                const to   = selected.map(c=>c.dataset.to).sort().slice(-1)[0] || '';
                const employeeName = currentRow.firstChild?.textContent || 'Сотрудник';
                const empId = parseInt(currentRow.dataset.empId,10);
                if(allGreen){
                    // Загружаем комментарии для диапазона, удаляем дубли
                    const params = new URLSearchParams({ employee_id: String(empId), date, start_time: from, end_time: to, project_id: String({{ $project->id }}) });
                    try{
                        const res = await fetch(`/comments?${params.toString()}`);
                        const list = await res.json();
                        const seen = new Set();
                        const items = (Array.isArray(list)?list:[]).map(x=>({time:x.start_time, text:x.comment||''}))
                          .filter(x=>x.text.trim().length>0)
                          .filter(x=>{ const k = x.time+"|"+x.text.trim(); if(seen.has(k)) return false; seen.add(k); return true; });
                        // отрисуем
                        const meta = `${employeeName}: ${from} – ${to}`;
                        document.getElementById('schedCommentsMeta').textContent = meta;
                        const ul = document.getElementById('schedCommentsList'); ul.innerHTML='';
                        items.forEach((it, idx)=>{
                            const row = document.createElement('div'); row.className='comment-item';
                            row.innerHTML = `<span class="text-sm text-gray-700">${it.time} — ${it.text}</span><button data-idx="${idx}" title="Удалить">✕</button>`;
                            row.querySelector('button').onclick = async ()=>{
                                // удаляем комментарии, совпадающие по времени/тексту в этом диапазоне
                                await fetch('/comments', { method:'DELETE', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ employee_id: empId, date, start_time: from, end_time: to })});
                                closeComments();
                            };
                            ul.appendChild(row);
                        });
                        // Добавление нового
                        document.getElementById('schedCommentAddBtn').onclick = async ()=>{
                            const t = (document.getElementById('schedCommentTime').value || from);
                            const txt = (document.getElementById('schedCommentText').value||'').trim();
                            if(!txt) return;
                            await fetch('/comments', { method:'POST', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ employee_id: empId, project_id: {{ $project->id }}, date, start_time: t, end_time: to, comment: txt })});
                            closeComments();
                        };
                        commentsModal.classList.remove('hidden'); commentsModal.classList.add('flex');
                    }catch(e){ commentsModal.classList.remove('hidden'); commentsModal.classList.add('flex'); }
                } else if(allRed){
                    openDelete(`Удалить интервал ${from} – ${to} для ${employeeName}?`);
                }
                clearSelection(); currentRow=null; startCell=null;
            };
            document.addEventListener('mouseup', ()=>{ if(!isSelecting) return; isSelecting=false; finalizeSelection(); });
            table.addEventListener('mouseleave', ()=>{ if(isSelecting){ isSelecting=false; finalizeSelection(); } });
            document.addEventListener('mousemove', (e)=>{ if(isSelecting && e.buttons===0){ isSelecting=false; finalizeSelection(); } });
        }
        let sortDirection = 1;

        const categoriesBase = "{{ url('/categories') }}"; // базовый URL
        const projectEquipmentListBase = "{{ route('projects.equipmentList', $project) }}"; // новый эндпоинт (ниже добавим)

        async function loadEquipment(categoryId) {
            window.currentCategoryId = categoryId;

            // Заголовок категории
            try {
                const r = await fetch(`${categoriesBase}/${categoryId}`, { headers: { 'Accept': 'application/json' } });
                if (r.ok) {
                    const data = await r.json();
                    const h = document.getElementById('categoryTitle');
                    if (h) h.textContent = data.name || '';
                }
            } catch (e) { /* no-op */ }

            // Таблица оборудования (HTML-фрагмент)
            const resp = await fetch(`${projectEquipmentListBase}?category_id=${categoryId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await resp.text();
            document.getElementById('equipmentList').innerHTML = html;

            attachFilterHandler();
        }
        async function attachToProject(equipmentId) {
            try {
                const attachBase = `{{ route('projects.equipment.attach', ['project' => $project, 'equipment' => 0]) }}`;
                const url = attachBase.replace('/0', `/${equipmentId}`);
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await r.json();
                if (!r.ok) throw new Error(data.error || 'Ошибка прикрепления');
                // Перезагрузим таблицу текущей категории
                if (window.currentCategoryId) await loadEquipment(window.currentCategoryId);
            } catch (e) {
                alert(e.message);
            }
        }
        async function detachFromProject(equipmentId) {
            try {
                const detachBase = `{{ route('projects.equipment.detach', ['project' => $project, 'equipment' => 0]) }}`;
                const url = detachBase.replace('/0', `/${equipmentId}`);
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-HTTP-Method-Override': 'DELETE',
                    },
                });
                const data = await r.json();
                if (!r.ok) throw new Error(data.error || 'Ошибка удаления');
                if (window.currentCategoryId) await loadEquipment(window.currentCategoryId);
            } catch (e) {
                alert(e.message);
            }
        }


        function attachFilterHandler() {
            const input = document.getElementById('filterEquipment');
            if (!input) return;
            input.oninput = function () {
                const q = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#equipmentTableBody tr');
                rows.forEach(tr => {
                    const name = tr.cells[0]?.textContent?.toLowerCase() || '';
                    tr.style.display = name.includes(q) ? '' : 'none';
                });
            };
        }
        attachFilterHandler();

        function sortTable(column) {
            const tbody = document.getElementById('equipmentTableBody');
            const rows = Array.from(tbody?.getElementsByTagName('tr') || []);
            const isPrice = column === 2;

            rows.sort((a, b) => {
                let aValue = a.cells[column]?.textContent?.trim() || '';
                let bValue = b.cells[column]?.textContent?.trim() || '';
                if (isPrice) {
                    aValue = parseFloat(aValue.replace(',', '.') || 0);
                    bValue = parseFloat(bValue.replace(',', '.') || 0);
                }
                return aValue.toString().localeCompare(bValue.toString(), undefined, { numeric: true }) * sortDirection;
            });

            sortDirection *= -1;
            rows.forEach(row => tbody.appendChild(row));
        }

        // Раскрывающиеся ветки дерева
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.expand-toggle');
            if (!btn) return;
            const wrapper = btn.closest('.collapsible');
            if (!wrapper) return;
            const content = wrapper.querySelector('.collapsible-content');
            if (!content) return;
            const open = content.style.display !== 'none';
            content.style.display = open ? 'none' : '';
            btn.textContent = open ? '►' : '▼';
        });

        // Удаление категории
        async function deleteCategory(categoryId) {
            if (!confirm('Удалить категорию и всё содержимое?')) return;
            try {
                const resp = await fetch(`{{ route('categories.destroy', ['category' => 'ID']) }}`.replace('ID', categoryId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-HTTP-Method-Override': 'DELETE',
                    },
                });
                const data = await resp.json();
                if (resp.ok) {
                    // Удалим узел дерева из DOM
                    const node = document.querySelector(`.collapsible[data-category-id="${categoryId}"]`);
                    if (node) node.remove();
                    // Очистим правую часть если смотрели эту категорию
                    if (window.currentCategoryId === categoryId) {
                        document.getElementById('categoryTitle').textContent = '';
                        document.getElementById('equipmentList').innerHTML = '';
                    }
                    alert('Категория удалена');
                } else {
                    alert(data.error || 'Ошибка при удалении категории');
                }
            } catch (e) {
                alert('Ошибка сети при удалении категории');
            }
        }

        // Редактирование оборудования
        async function editEquipment(id) {
            try {
                const r = await fetch(`{{ url('/equipment') }}/${id}/edit`);
                const data = await r.json();
                openEquipmentModal(data.category_id);

                // Заполняем форму
                document.getElementById('equipment_id').value = data.id;
                document.getElementById('name').value = data.name || '';
                document.getElementById('description').value = data.description || '';
                document.getElementById('price').value = data.price || '';
                document.getElementById('category_id').value = data.category_id || '';

                // specs — JSON
                if (data.specifications) {
                    document.getElementById('specifications').value = JSON.stringify(data.specifications, null, 2);
                } else if (document.getElementById('specifications')) {
                    document.getElementById('specifications').value = '';
                }

                document.getElementById('modalTitle').textContent = 'Редактировать оборудование';
                document.getElementById('submitEquipment').textContent = 'Сохранить';
                const form = document.getElementById('createEquipmentForm');
                form.action = `{{ url('/equipment') }}/${id}`;
                ensurePutMethod(form);
            } catch (e) {
                alert('Не удалось загрузить данные оборудования');
            }
        }

        function ensurePutMethod(form) {
            let method = form.querySelector('input[name="_method"]');
            if (!method) {
                method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                form.appendChild(method);
            }
            method.value = 'PUT';
        }

        // Удаление оборудования
        async function deleteEquipment(id) {
            if (!confirm('Удалить это оборудование?')) return;
            try {
                const resp = await fetch(`{{ url('/equipment') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-HTTP-Method-Override': 'DELETE',
                    },
                });
                const data = await resp.json();
                if (resp.ok) {
                    // Удалим строку
                    const tr = document.querySelector(`tr[data-id="${id}"]`);
                    if (tr) tr.remove();
                } else {
                    alert(data.error || 'Ошибка при удалении');
                }
            } catch (e) {
                alert('Ошибка сети при удалении');
            }
        }
    </script>

