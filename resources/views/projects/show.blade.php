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
            <span id="statusBadge" class="px-3 py-1 text-sm rounded-full font-medium {{ $badge['class'] }}">
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
                <!-- Модалка смены статуса -->
                <div id="changeStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
                    <div class="bg-white rounded-lg p-6 w-full max-w-md">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Сменить статус проекта</h2>
                        <form id="changeStatusForm" action="{{ route('projects.status.update', $project) }}">
                            @csrf
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
            @endcan
        </div>
    </div>
    <div id="toast" class="hidden">
        <span id="toastMessage"></span>
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
        </div>

        {{-- Контент вкладки --}}
        @if ($tab === 'estimate')
            {{-- Смета --}}
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-semibold mb-4 text-gray-800">Сметы проекта</h1>

            @if($project->status !== 'completed')
                <button id="addEstimateBtn" class="bg-green-600 text-white py-2 px-4 rounded-md mb-4 hover:bg-green-700 transition">Добавить новую смету</button>
            @endif

            <button id="openCatalog" class="bg-blue-600 text-white py-2 px-4 rounded-md mb-4 hover:bg-blue-700 transition">Открыть каталог</button>

            <div id="catalogSidebar" class="hidden w-1/3 bg-gray-50 p-4 overflow-y-auto fixed left-0 top-0 h-full shadow-xl border-r border-gray-200 transform -translate-x-full transition-transform duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Каталог оборудования</h3>
                    <button id="closeCatalog" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="catalogTree" class="text-gray-600"></div>
            </div>

            {{-- Суб-табы для смет --}}
            <div class="flex space-x-4 border-b border-gray-200 mb-4">
                @foreach($estimates as $est)
                    <a href="{{ route('projects.show', $project->id) }}?tab=estimate&est_id={{ $est->id }}" class="px-4 py-2 {{ $currentEstimate->id === $est->id ? 'border-b-2 border-blue-600 font-semibold text-blue-700' : 'text-gray-600 hover:text-blue-600' }}">
                        {{ $est->name }}
                    </a>
                @endforeach
            </div>

            {{-- Контент текущей сметы --}}
            <div class="mb-8 border-b pb-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800">{{ $currentEstimate->name }}</h2>
                    @if($project->status !== 'completed')
                        <button onclick="deleteEstimate({{ $currentEstimate->id }})" class="text-red-600 hover:text-red-800">Удалить</button>
                    @endif
                    <a href="{{ route('estimates.export', $currentEstimate) }}" class="text-green-600 hover:text-green-800">Экспорт в PDF</a>
                    <a href="{{ route('estimates.exportExcel', $currentEstimate) }}"
                       class="text-blue-600 hover:text-blue-800">
                        Экспорт в Excel
                    </a>
                </div>

                @if($project->status === 'completed')
                    <p class="text-red-600 mb-4">Проект завершён: смета фиксирована.</p>
                @endif

                @if($project->status !== 'completed')
                    <form class="estimateForm mb-6" data-id="{{ $currentEstimate->id }}" action="{{ route('estimates.update', $currentEstimate) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label for="client_id_{{ $currentEstimate->id }}" class="block text-sm font-medium text-gray-700">Клиент</label>
                                <select name="client_id" id="client_id_{{ $currentEstimate->id }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Без клиента</option>
                                    @foreach($clients as $cl)
                                        <option value="{{ $cl->id }}" {{ $currentEstimate->client_id == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="company_id_{{ $currentEstimate->id }}" class="block text-sm font-medium text-gray-700">Фирма</label>
                                <select name="company_id" id="company_id_{{ $currentEstimate->id }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Без фирмы</option>
                                    @foreach($companies as $comp)
                                        <option value="{{ $comp->id }}" {{ $currentEstimate->company_id == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="delivery_cost_{{ $currentEstimate->id }}" class="block text-sm font-medium text-gray-700">Стоимость доставки (₽)</label>
                                <input type="number" name="delivery_cost" id="delivery_cost_{{ $currentEstimate->id }}" value="{{ $currentEstimate->delivery_cost }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" min="0" step="0.01">
                            </div>
                        </div>
                        <button type="submit" class="mt-4 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">Сохранить изменения</button>
                    </form>
                @endif


                @php
                    if (!function_exists('renderTree1')) {
                        function renderTree1($tree, $level = 0, $calculated, $section = 'equipment', $estimateId, $hasViewPrices, $canEdit) {
                            \Log::debug('renderTree1 data', ['tree' => $tree, 'section' => $section]);
                            foreach ($tree as $key => $val) {
                                $colspan = 2 + ($hasViewPrices ? 4 : 0) + ($canEdit ? 1 : 0);

                                // Рендеринг категории
                                echo '<tr><td class="p-2 border pl-' . ($level * 4) . '" colspan="' . $colspan . '">' . htmlspecialchars($key) . '</td></tr>';

                                // Рекурсия для подкатегорий
                                if (isset($val['sub']) && !empty($val['sub'])) {
                                    renderTree1($val['sub'], $level + 1, $calculated, $section, $estimateId, $hasViewPrices, $canEdit);
                                }
                                // Рендеринг оборудования
                                if (isset($val['equipment']) && !empty($val['equipment'])) {
                                    foreach ($val['equipment'] as $eqName => $eq) {
                                        $sum = ($eq['price'] ?? 0) * ($eq['qty'] ?? 0);
                                        $discount = $calculated[$section]['discount'] ?? 0;
                                        $afterDisc = $sum * (1 - $discount / 100);

                                        echo '<tr data-equipment-id="' . ($eq['id'] ?? 0) . '">';
                                        echo '<td class="p-2 border pl-' . (($level + 1) * 4) . '">' . htmlspecialchars($eqName) . '</td>';
                                        echo '<td class="p-2 border">' . ($eq['qty'] ?? 0) . '</td>'; // Количество
                                        if ($hasViewPrices) {
                                            echo '<td class="p-2 border">' . number_format($eq['price'] ?? 0, 2) . '</td>'; // Цена
                                            echo '<td class="p-2 border">' . number_format($sum, 2) . '</td>'; // Сумма
                                            echo '<td class="p-2 border">' . $discount . '</td>'; // Скидка
                                            echo '<td class="p-2 border">' . number_format($afterDisc, 2) . '</td>'; // После скидки
                                        }
                                        if ($canEdit) {
                                            // Столбец для действий (крестик удаления)
                                            echo '<td class="p-2 border">';
                                            echo '<button onclick="removeEquipment(' . ($eq['id'] ?? 0) . ', ' . $estimateId . ')" class="text-red-600 hover:text-red-800">';
                                            echo '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                                            echo '</button>';
                                            echo '</td>';
                                        }
                                        echo '</tr>';
                                    }
                                }
                            }
                        }
                    }
                @endphp

                <table class="w-full border-collapse">
                    <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 border">Наименование</th>
                        <th class="p-2 border">Кол-во</th>
                        @can('view prices')
                            <th class="p-2 border">Цена</th>
                            <th class="p-2 border">Сумма</th>
                            <th class="p-2 border">Скидка (%)</th>
                            <th class="p-2 border">После скидки</th>
                        @endcan
                        @if ($project->status !== 'completed')
                            <th class="p-2 border">Действия</th>
                        @endif
                        </tr>
                        </thead>
                    <tbody id="estimateTableBody">
                    <!-- Оборудование -->
                    <tr class="bg-gray-100">
                        <td colspan="{{ auth()->user()->can('view prices') ? ($project->status !== 'completed' ? 7 : 6) : ($project->status !== 'completed' ? 3 : 2) }}" class="p-2 font-bold text-gray-800">Оборудование</td>
                    </tr>
                    @if (empty($currentEstimate->calculated['equipment']['tree']))
                        <tr><td colspan="{{ auth()->user()->can('view prices') ? ($project->status !== 'completed' ? 7 : 6) : ($project->status !== 'completed' ? 3 : 2) }}" class="p-2 border text-center">Нет оборудования</td></tr>
                    @else
                        {!! renderTree1($currentEstimate->calculated['equipment']['tree'], 0, $currentEstimate->calculated, 'equipment', $currentEstimate->id, auth()->user()->can('view prices'), $project->status !== 'completed') !!}
                    @endif
                    <tr class="font-bold">
                        <td class="p-2 border">Итого оборудование</td>
                        <td class="p-2 border"></td>
                        @can('view prices')
                            <td class="p-2 border"></td>
                            <td class="p-2 border">{{ number_format($currentEstimate->calculated['equipment']['total'] ?? 0, 2) }}</td>
                            <td class="p-2 border">{{ $currentEstimate->calculated['equipment']['discount'] ?? 0 }}</td>
                            <td class="p-2 border">{{ number_format($currentEstimate->calculated['equipment']['after_disc'] ?? 0, 2) }}</td>
                        @endcan
                        @if ($project->status !== 'completed')
                            <td class="p-2 border"></td>
                        @endif
                    </tr>

                    <!-- Материалы -->
                    <tr class="bg-gray-100">
                        <td colspan="{{ auth()->user()->can('view prices') ? ($project->status !== 'completed' ? 7 : 6) : ($project->status !== 'completed' ? 3 : 2) }}" class="p-2 font-bold text-gray-800">Материалы</td>
                    </tr>
                    @if (empty($currentEstimate->calculated['materials']['tree']))
                        <tr><td colspan="{{ auth()->user()->can('view prices') ? ($project->status !== 'completed' ? 7 : 6) : ($project->status !== 'completed' ? 3 : 2) }}" class="p-2 border text-center">Нет материалов</td></tr>
                    @else
                        {!! renderTree1($currentEstimate->calculated['materials']['tree'], 0, $currentEstimate->calculated, 'materials', $currentEstimate->id, auth()->user()->can('view prices'), $project->status !== 'completed') !!}
                    @endif
                    <tr class="font-bold">
                        <td class="p-2 border">Итого материалы</td>
                        <td class="p-2 border"></td>
                        @can('view prices')
                            <td class="p-2 border"></td>
                            <td class="p-2 border">{{ number_format($currentEstimate->calculated['materials']['total'] ?? 0, 2) }}</td>
                            <td class="p-2 border">{{ $currentEstimate->calculated['materials']['discount'] ?? 0 }}</td>
                            <td class="p-2 border">{{ number_format($currentEstimate->calculated['materials']['after_disc'] ?? 0, 2) }}</td>
                        @endcan
                        @if ($project->status !== 'completed')
                            <td class="p-2 border"></td>
                        @endif
                    </tr>

                    <!-- Услуги -->
                    <tr class="bg-gray-100">
                        <td colspan="{{ auth()->user()->can('view prices') ? ($project->status !== 'completed' ? 7 : 6) : ($project->status !== 'completed' ? 3 : 2) }}" class="p-2 font-bold text-gray-800">Услуги</td>
                    </tr>
                    @if (empty($currentEstimate->calculated['services']['staff']) && $currentEstimate->delivery_cost == 0)
                        <tr><td colspan="{{ auth()->user()->can('view prices') ? ($project->status !== 'completed' ? 7 : 6) : ($project->status !== 'completed' ? 3 : 2) }}" class="p-2 border text-center">Нет услуг</td></tr>
                    @else
                        @foreach($currentEstimate->calculated['services']['staff'] as $st)
                            <tr>
                                <td class="p-2 border">{{ htmlspecialchars($st['name']) }} ({{ $st['rate_type'] }}, {{ $st['minutes'] }} мин)</td>
                                <td class="p-2 border">1</td>
                                @can('view prices')
                                    <td class="p-2 border">{{ number_format($st['rate'] ?? 0, 2) }}</td>
                                    <td class="p-2 border">{{ number_format($st['sum'] ?? 0, 2) }}</td>
                                    <td class="p-2 border">{{ $currentEstimate->calculated['services']['discount'] ?? 0 }}</td>
                                    <td class="p-2 border">{{ number_format(($st['sum'] ?? 0) * (1 - ($currentEstimate->calculated['services']['discount'] ?? 0)/100), 2) }}</td>
                                @endcan
                                @if ($project->status !== 'completed')
                                    <td class="p-2 border"></td> <!-- Нет удаления для услуг -->
                                @endif
                            </tr>
                        @endforeach
                        <tr>
                            <td class="p-2 border">Доставка</td>
                            <td class="p-2 border">1</td>
                            @can('view prices')
                                <td class="p-2 border">{{ number_format($currentEstimate->delivery_cost ?? 0, 2) }}</td>
                                <td class="p-2 border">{{ number_format($currentEstimate->delivery_cost ?? 0, 2) }}</td>
                                <td class="p-2 border">{{ $currentEstimate->calculated['services']['discount'] ?? 0 }}</td>
                                <td class="p-2 border">{{ number_format(($currentEstimate->delivery_cost ?? 0) * (1 - ($currentEstimate->calculated['services']['discount'] ?? 0)/100), 2) }}</td>
                            @endcan
                            @if ($project->status !== 'completed')
                                <td class="p-2 border"></td>
                            @endif
                        </tr>
                    @endif
                    <tr class="font-bold">
                        <td class="p-2 border">Итого услуги</td>
                        <td class="p-2 border"></td>
                        @can('view prices')
                            <td class="p-2 border"></td>
                            <td class="p-2 border">{{ number_format($currentEstimate->calculated['services']['total'] ?? 0, 2) }}</td>
                            <td class="p-2 border">{{ $currentEstimate->calculated['services']['discount'] ?? 0 }}</td>
                            <td class="p-2 border">{{ number_format($currentEstimate->calculated['services']['after_disc'] ?? 0, 2) }}</td>
                        @endcan
                        @if ($project->status !== 'completed')
                            <td class="p-2 border"></td>
                        @endif
                    </tr>

                    <!-- Итоговые строки -->
                    @can('view prices')
                        <tr class="font-bold bg-gray-100">
                            <td class="p-2 border">Подытог</td>
                            <td colspan="{{ $project->status !== 'completed' ? 6 : 5 }}" class="p-2 border text-right">{{ number_format($currentEstimate->calculated['subtotal'] ?? 0, 2) }} ₽</td>
                        </tr>
                        <tr class="font-bold">
                            <td class="p-2 border">Налог ({{ $currentEstimate->calculated['tax_method'] ?? 'none' }})</td>
                            <td colspan="{{ $project->status !== 'completed' ? 6 : 5 }}" class="p-2 border text-right">{{ number_format($currentEstimate->calculated['tax'] ?? 0, 2) }} ₽</td>
                        </tr>
                        <tr class="font-bold bg-green-100">
                            <td class="p-2 border">Итого</td>
                            <td colspan="{{ $project->status !== 'completed' ? 6 : 5 }}" class="p-2 border text-right">{{ number_format($currentEstimate->calculated['total'] ?? 0, 2) }} ₽</td>
                        </tr>
                    @endcan
                        </tbody>
                    </table>
                    </div>
            </div>
        @elseif ($tab === 'staff')
            @php
                // Сотрудники проекта по прямой привязке (project_user)
                $employeesList = $project->staff()->get(['users.id','users.name','users.role']);
                // Для раздела проекта «Сотрудники» данные по сумме тянем из work_intervals по текущему проекту
                $projectIntervals = \App\Models\WorkInterval::where('project_id', $project->id)
                    ->select(['id','employee_id','project_id','type','hour_rate','project_rate','start_time','end_time','updated_at'])
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
                 $adminUsersPool = \App\Models\User::where('admin_id', auth()->id())
                    ->get(['id','name','phone','role']);
                 $adminUsersPoolMapped = $adminUsersPool->map(function($u){
                     return [ 'id'=>$u->id, 'name'=>$u->name, 'phone'=>$u->phone, 'role'=>$u->role ];
                 });
                // Роли из Spatie (таблица roles)
                $adminRoles = \Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name');
            @endphp
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-700">Сотрудники</h2>
                    <div class="space-x-2">
                        <button id="openStaffScheduleModal" class="px-3 py-2 text-sm rounded-lg border bg-white text-gray-700">Расписание</button>
                        <button id="openAddStaffModal" class="px-3 py-2 text-sm rounded-lg border bg-white text-gray-700">Добавить сотрудника</button>
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
                            @php
                                $empIvs = $projectIntervals->get($emp->id) ?? collect();
                                $empProjectRate = optional($empIvs->firstWhere('project_rate','!=',null))->project_rate;
                                $empHourRate    = optional($empIvs->firstWhere('hour_rate','!=',null))->hour_rate;
                                $empRateType    = !is_null($empProjectRate) ? 'project' : (!is_null($empHourRate) ? 'hour' : '');
                                $empRateValue   = !is_null($empProjectRate) ? (float)$empProjectRate : (!is_null($empHourRate) ? (float)$empHourRate : '');
                            @endphp
                            <tr data-emp-id="{{ $emp->id }}" data-rate-type="{{ $empRateType }}" data-rate="{{ $empRateValue }}" class="group staff-grid hover:bg-gray-50">
                                <td class="staff-cell truncate min-w-[140px]" title="{{ $emp->name }}">{{ $emp->name }}</td>
                                <td class="staff-cell truncate" title="{{ $emp->role ?? '' }}">{{ $emp->role ?? '' }}</td>
                                 @php
                                     $ivs = $projectIntervals->get($emp->id) ?? collect();
                                     $pc  = ($projectComments->get($emp->id) ?? collect())->first();
                                     // Рассчитываем сумму: если есть project_rate — берём её; иначе hour_rate * часы
                                     $displaySumm = null;
                                     if ($ivs->isNotEmpty()) {
                                         $minutes = 0;
                                         $projectRate = null;
                                         $hourRate = null;
                                         foreach ($ivs as $x) {
                                             if ((int)$x->project_id !== (int)$project->id) continue;
                                             if ((string)$x->type !== 'busy') continue; // учитываем только рабочие интервалы
                                             $s = strtotime($x->start_time);
                                             $e = strtotime($x->end_time);
                                             if ($e > $s) { $minutes += ($e - $s) / 60; }
                                             if (!is_null($x->project_rate)) { $projectRate = (float)$x->project_rate; }
                                             if (!is_null($x->hour_rate) && $hourRate === null) { $hourRate = (float)$x->hour_rate; }
                                              
                                         }
                                         if ($projectRate !== null) { $displaySumm = $projectRate; }
                                          elseif ($hourRate !== null) { $displaySumm = $hourRate * ($minutes/60.0); }
                                     }
                                 @endphp
                                <td class="staff-cell staff-shift whitespace-nowrap tabular-nums">{{ $userPhones[$emp->id] ?? '' }}</td>
                                 <td class="staff-cell staff-shift whitespace-nowrap tabular-nums">{{ $displaySumm !== null ? number_format((float)$displaySumm, 2, ',', ' ') : '' }}</td>
                                <td class="staff-cell staff-shift break-words">
                                    <button type="button"
                                        class="comments-link open-all-comments"
                                        data-emp-id="{{ $emp->id }}"
                                        aria-label="Все комментарии по сотруднику"
                                    >
                                        <span>Посмотреть все комментарии</span>
                                    </button>
                                </td>
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
                        <h3 id="addStaffModalTitle" class="text-lg font-medium text-gray-900">Добавить сотрудника</h3>
                        <button onclick="closeAddStaff()" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>
                     <form id="addStaffForm" class="space-y-3">
                        <select id="staffSpecialty" class="w-full rounded-md border-gray-300" required></select>
                        <select id="staffUser" class="w-full rounded-md border-gray-300" required></select>
                         <div class="grid grid-cols-2 gap-2">
                             <select id="staffRateType" class="w-full rounded-md border-gray-300">
                                 <option value="hour">Часовая</option>
                                 <option value="project">За проект</option>
                             </select>
                             <input id="staffAmount" type="number" min="0" class="w-full rounded-md border-gray-300 no-spin" placeholder="Ставка" value="0" />
                         </div>
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
                /* Визуал выделения как в Personnel */
                .sched-selected{
                    border-color:#1d4ed8 !important;
                    box-shadow:0 0 0 2px rgba(59,130,246,0.7) inset !important;
                }
                /* Для пустых ячеек дополнительно заливаем синим */
                .sched-selected-solid{
                    background-color:#3b82f6 !important;
                    color:#fff !important;
                }
                .comment-item{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:4px 8px;border:1px solid #eee;border-radius:6px}
                .comment-item button{color:#B42318}
            </style>
            <!-- ВНЕШНИЕ МОДАЛКИ ДЛЯ РАСПИСАНИЯ (над модалкой расписания) -->
            <div id="schedCommentsModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center" style="z-index:1000;">
                <div class="bg-white rounded-lg w-11/12 max-w-3xl shadow-lg">
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <h4 class="text-sm font-medium text-gray-700">Комментарии</h4>
                        <button id="schedCommentsCloseBtn" class="text-gray-500 hover:text-gray-700">✕</button>
                    </div>
                    <div class="p-4 space-y-3 max-h-[70vh] overflow-auto">
                        <div id="schedCommentsNotice" class="hidden text-xs px-3 py-2 bg-amber-50 text-amber-700 rounded"></div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-sm text-gray-600">Выбранный интервал:</span>
                            <span id="cmtIntervalLabel" class="text-sm text-gray-800"></span>
                        </div>
                        <div id="schedCommentsList" class="space-y-2"></div>
                        
                        <div class="mt-4 border-t pt-4">
                            <div class="text-sm font-medium text-gray-700 mb-2">Добавить комментарий к этому времени</div>
                            <textarea id="intervalCommentText" class="w-full border rounded p-2 text-sm" rows="3" placeholder="Комментарий"></textarea>
                            <div class="mt-2 text-right">
                                <button id="saveIntervalComment" class="px-3 py-1 text-sm rounded border bg-blue-600 text-white hover:bg-blue-700">Сохранить</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Модалка «Все комментарии сотрудника» -->
            <div id="allCommentsModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center" style="z-index:1000;">
                <div class="bg-white rounded-lg w-11/12 max-w-3xl shadow-lg">
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <h4 class="text-sm font-medium text-gray-700">Все комментарии</h4>
                        <button id="allCommentsCloseBtn" class="text-gray-500 hover:text-gray-700">✕</button>
                    </div>
                    <div class="p-4 space-y-3 max-h-[70vh] overflow-auto">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-sm text-gray-600">Дата:</span>
                            <input type="date" id="allCommentsDate" class="rounded-md border-gray-300" />
                        </div>

                        <div id="allGeneralBanner" class="hidden"></div>

                        <div id="allCommentsList" class="space-y-2"></div>

                        <div class="mt-4 border-t pt-4">
                            <div class="text-sm font-medium text-gray-700 mb-2">Общий комментарий к проекту</div>
                            <textarea id="projectGeneralCommentText" class="w-full border rounded p-2 text-sm" rows="3" placeholder="Общий комментарий"></textarea>
                            <div class="mt-2 text-right">
                                <button id="saveProjectGeneralComment" class="px-3 py-1 text-sm rounded border bg-blue-600 text-white hover:bg-blue-700">Сохранить</button>
                            </div>
                        </div>
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
        @endif
    </div>
    <script>
        function openModal(id) {
            document.getElementById(id)?.classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id)?.classList.add('hidden');
        }
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
        let editingStaffId = null;
        function openAddStaff(){
            const titleEl = document.getElementById('addStaffModalTitle');
            if (titleEl) titleEl.textContent = editingStaffId ? 'Редактировать сотрудника' : 'Добавить сотрудника';
            document.getElementById('addStaffModal').classList.remove('hidden');
            document.getElementById('addStaffModal').classList.add('flex');
        }
        function closeAddStaff(){ editingStaffId = null; document.getElementById('addStaffModal').classList.add('hidden'); document.getElementById('addStaffModal').classList.remove('flex'); }
        document.getElementById('openAddStaffModal')?.addEventListener('click', ()=>{ editingStaffId=null; populateAddStaffModal(); openAddStaff(); });
        // Инициализация селектов в модалке «Добавить сотрудника»
        const adminUsersPool = @json($adminUsersPoolMapped ?? []);
        const rolesPool = @json($adminRoles ?? []);
        const summaryBaseUrl = `{{ route('projects.staff.summary', ['project'=>$project->id, 'user'=>0]) }}`;
        const buildSummaryUrl = (userId)=> summaryBaseUrl.replace(/\/staff\/0\//, `/staff/${String(userId)}/`);
        function populateAddStaffModal(){
            const specSel = document.getElementById('staffSpecialty');
            const userSel = document.getElementById('staffUser');
            // Роли из БД (без хардкода)
            const rolesOnly = (rolesPool||[]).filter(r=>r);
            const allRolesSet = new Set([...rolesOnly]);
            const roleOptions = Array.from(allRolesSet).map(r=>`<option value="${r}">${r}</option>`).join('');
            // Заголовок: «все специальности» + роли из БД
            specSel.innerHTML = '<option value="__all__" selected>все специальности</option>' + roleOptions;
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
            } else if (role) {
                filtered = all.filter(u=>String(u.role||'')===String(role));
            }
            userSel.innerHTML = '<option value="">Имя</option>' + filtered.map(u=>`<option value="${u.id}" data-role="${u.role||''}">${u.name}</option>`).join('');
        });
        document.getElementById('changeStatusForm')?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = event.target;
            const status = form.querySelector('#project_status').value;
            console.log('Sending updateStatus request', { status, action: form.action });

            try {
                const response = await fetch(form.action, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status }),
                });
                const data = await response.json();
                console.log('updateStatus response', data);

                if (response.ok) {

                    closeModal('changeStatusModal');

                    // Обновление бейджа
                    const badge = document.getElementById('statusBadge');
                    const statusMap = {
                        new: { label: 'Новый', class: 'bg-yellow-100 text-yellow-800' },
                        active: { label: 'В работе', class: 'bg-green-100 text-green-800' },
                        completed: { label: 'Завершён', class: 'bg-blue-100 text-blue-800' },
                        cancelled: { label: 'Отменён', class: 'bg-red-100 text-red-800' },
                    };
                    const { label, class: className } = statusMap[status] || { label: status, class: 'bg-gray-100 text-gray-800' };
                    badge.textContent = label;
                    badge.className = `px-3 py-1 text-sm rounded-full font-medium ${className}`;
                } else {

                }
            } catch (e) {
                console.error('updateStatus network error', e);

            }
        });
        // Наполняем при открытии
        document.getElementById('openAddStaffModal')?.addEventListener('click', populateAddStaffModal);
        // Базовые URL для привязки/отвязки сотрудников (Laravel routes)
        const attachStaffBase = `{{ route('projects.staff.attach', ['project' => $project->id, 'user' => 0]) }}`; // .../staff/0
        const detachStaffBase = `{{ route('projects.staff.detach', ['project' => $project->id, 'user' => 0]) }}`;

        document.getElementById('addStaffForm')?.addEventListener('submit', async (e)=>{
            e.preventDefault();
            const userId = document.getElementById('staffUser').value;
            const specialtyId = document.getElementById('staffSpecialty').value;
            const amount = document.getElementById('staffAmount').value || 0;
            const rateType = document.getElementById('staffRateType').value;
            if(!userId || !specialtyId){ return; }
            const tbody = document.getElementById('staffTableBody');
            if (editingStaffId && String(editingStaffId) === String(userId)) {
                // Редактирование существующей строки — обновляем без дублирования и без повторной привязки
                const row = document.querySelector(`#staffTableBody tr[data-emp-id="${userId}"]`);
                if (row) {
                    row.dataset.rateType = rateType;
                    row.dataset.rate = String(amount || 0);
                    // Сначала применим ставку в БД ко всем busy-интервалам сотрудника в проекте
                    try {
                        const url = attachStaffBase.replace(/\/0$/, `/${userId}`);
                        await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ rate_type: rateType, rate: Number(amount) || null })
                        });
                    } catch (_e) { /* no-op */ }
                    // Затем получим актуальную сумму с сервера и обновим ячейку
                    const sumCell = row.cells[3];
                    try {
                        const u = buildSummaryUrl(userId);
                        const r = await fetch(u);
                        const j = await r.json();
                        if (sumCell) {
                            const val = (j && j.sum!=null) ? Number(j.sum).toLocaleString('ru-RU',{minimumFractionDigits:2,maximumFractionDigits:2}) : '';
                            sumCell.textContent = val;
                        } else if (btn.id === 'schedDeleteInterval') {
                            console.log('schedDeleteInterval click', p);
                            await fetch('/personnel/clear', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({
                                    employee_id: p.employee_id,
                                    project_id: p.project_id,
                                    date: p.date,
                                    start_time: p.start_time,
                                    end_time: p.end_time,
                                }),
                            });
                            const currentTbody3 = document.querySelector('#staffSchedGrid tbody');
                            let row = (currentTbody3 ? currentTbody3.querySelector(`tr[data-emp-id="${p.employee_id}"]`) : null) || document.querySelector(`#staffSchedGrid tr[data-emp-id="${p.employee_id}"]`);
                            if (row) {
                                const cells = Array.from(row.querySelectorAll('td.sched-cell'));
                                const fromMin = timeToMin(p.start_time);
                                const toMin = timeToMin(p.end_time);
                                cells.forEach((td) => {
                                    const s = timeToMin(td.dataset.from);
                                    const e = timeToMin(td.dataset.to);
                                    if (s < toMin && e > fromMin) {
                                        td.style.background = '';
                                        td.classList.remove('bg-red-500');
                                    }
                                });
                            }
                            await renderStaffSchedule();
                        }
                    } catch(_e) { /* no-op */ }
                }
                closeAddStaff();
                return;
            }
            // Создание новой строки или обновление, если уже есть такая строка (на случай рассинхронизации)
            try {
                const url = attachStaffBase.replace(/\/0$/, `/${userId}`);
                await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ rate_type: rateType, rate: Number(amount) || null })
                });
            } catch (err) { /* no-op */ }
            const existing = document.querySelector(`#staffTableBody tr[data-emp-id="${userId}"]`);
            if (existing) {
                existing.dataset.rateType = rateType;
                existing.dataset.rate = String(amount || 0);
                const sumCell = existing.cells[3];
                if (sumCell) sumCell.textContent = rateType==='project' ? Number(amount).toLocaleString('ru-RU', {minimumFractionDigits:2, maximumFractionDigits:2}) : '';
                closeAddStaff();
                return;
            }
            const tr = document.createElement('tr');
            tr.className = 'group staff-grid hover:bg-gray-50';
            tr.dataset.empId = userId;
            tr.dataset.rateType = rateType;
            tr.dataset.rate = String(amount || 0);
            const user = adminUsersPool.find(u=>String(u.id)===String(userId));
            const specLabel = specialtyId==='__all__' ? '' : (specialtyId==='__none__' ? 'нет специальности' : specialtyId);
            tr.innerHTML = `
                <td class="staff-cell truncate min-w-[140px]" title="${user?.name||''}">${user?.name||''}</td>
                <td class="staff-cell truncate" title="${specLabel}">${specLabel}</td>
                <td class="staff-cell staff-shift whitespace-nowrap tabular-nums">${user?.phone||''}</td>
                <td class="staff-cell staff-shift whitespace-nowrap text-right">${rateType==='project' ? Number(amount).toLocaleString('ru-RU', {minimumFractionDigits:2, maximumFractionDigits:2}) : ''}</td>
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
            try {
                const url = detachStaffBase.replace(/\/0$/, `/${id}`);
                await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-HTTP-Method-Override': 'DELETE'
                    }
                });
            } catch (err) { /* no-op */ }
        }
        function editStaff(id){
            populateAddStaffModal();
            const row = document.querySelector(`#staffTableBody tr[data-emp-id="${id}"]`);
            const userSel = document.getElementById('staffUser');
            const specSel = document.getElementById('staffSpecialty');
            const amountInput = document.getElementById('staffAmount');
            const rateTypeSel = document.getElementById('staffRateType');
            editingStaffId = id;
            userSel.value = String(id);
            const currentRole = (row?.cells[1]?.textContent || '').trim();
            // если текущая роль присутствует в списке — выбираем её, иначе оставляем «все специальности»
            const hasRole = Array.from(specSel.options).some(o=>o.value===currentRole);
            specSel.value = hasRole ? currentRole : '__all__';
            const rowRateType = row?.dataset.rateType || 'hour';
            const rowRateVal  = row?.dataset.rate || '';
            rateTypeSel.value = rowRateType;
            amountInput.value = rowRateVal;
            openAddStaff();
        }

        // ====== Расписание сотрудника (модалка, день) ======
        let currentStaffId = null;
        function openStaffSchedule(empId, date){ currentStaffId=empId; const m=document.getElementById('staffScheduleModal'); m.classList.remove('hidden'); m.classList.add('flex'); if(date) document.getElementById('staffSchedDate').value=date; renderStaffSchedule(); }
        function closeStaffSchedule(){ const m=document.getElementById('staffScheduleModal'); m.classList.add('hidden'); m.classList.remove('flex'); location.reload();}
            document.getElementById('openStaffScheduleModal')?.addEventListener('click', ()=>{
            const first = document.querySelector('#staffTableBody tr');
            if(!first){ alert('Нет сотрудников'); return; }
                openStaffSchedule(first.dataset.empId, '{{ optional(\Carbon\Carbon::parse($project->start_date))->format('Y-m-d') ?? now()->format('Y-m-d') }}');
        });
        document.getElementById('staffSchedInterval')?.addEventListener('change', renderStaffSchedule);
            document.getElementById('staffSchedDate')?.addEventListener('change', ()=>{ renderStaffSchedule().then(()=>{ const m = document.getElementById('schedInlineMenu'); if(m) m.dataset.payload='{}'; }); });
        document.getElementById('addEstimateBtn')?.addEventListener('click', async () => {
            const name = prompt('Название новой сметы:');
            if (!name) return;
            const resp = await fetch('{{ route('projects.estimates.create', $project) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name })
            });
            const data = await resp.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.error);
            }
        });

        // Удаление сметы
        async function deleteEstimate(id) {
            if (!confirm('Удалить смету?')) return;
            const resp = await fetch(`/estimates/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await resp.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.error);
            }
        }

        // Обновление формы сметы (AJAX)
        document.querySelectorAll('.estimateForm').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                const resp = await fetch(e.target.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData
                });
                const data = await resp.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error);
                }
            });
        });

        // Каталог


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

            // Берём сотрудников прямо из таблицы проекта (только прикреплённые к проекту)
            const users = Array.from(document.querySelectorAll('#staffTableBody tr')).map(tr=>{
                return {
                    id: parseInt(tr.dataset.empId, 10),
                    name: (tr.cells[0]?.textContent||'').trim(),
                    role: (tr.cells[1]?.textContent||'').trim()
                };
            });
            const res = await fetch(`/personnel/data?date=${date}&project_id={{ $project->id }}`);
            const data = await res.json();
            function timeToMin(t){ const [h,m]=String(t).split(':').map(Number); return (isNaN(h)||isNaN(m))?0:(h*60+m); }
            function makeMixed(td){ td.classList.remove('bg-green-500','bg-red-500'); td.style.background='linear-gradient(90deg, #ef4444 0 50%, #22c55e 50% 100%)'; }
            function makeGreen(td){ td.style.background=''; td.classList.remove('bg-red-500'); td.classList.add('bg-green-500'); }
            function makeRed(td){ td.style.background=''; td.classList.remove('bg-green-500'); td.classList.add('bg-red-500'); }
            async function refreshStaffSum(userId){
                try{
                    const u = buildSummaryUrl(userId);
                    const r = await fetch(u);
                    const j = await r.json();
                    const row = document.querySelector(`#staffTableBody tr[data-emp-id="${userId}"]`);
                    if(row){ const sumCell = row.cells[3]; if(sumCell){ sumCell.textContent = (j && j.sum!=null)? Number(j.sum).toLocaleString('ru-RU',{minimumFractionDigits:2,maximumFractionDigits:2}):''; } }
                }catch(_e){ /* no-op */ }
            }

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

            // Если модальные элементы отсутствуют (например, часть HTML не отрисована), выходим без навешивания обработчиков
            if (!document.getElementById('schedCommentsModal') || !document.getElementById('schedDeleteModal')) {
                return;
            }
            // Обработчики для внешних модалок
            const commentsModal = document.getElementById('schedCommentsModal');
            const deleteModal = document.getElementById('schedDeleteModal');
            const openComments = ()=>{
                if(!commentsModal) return;
                const ta = document.getElementById('intervalCommentText');
                if (ta) ta.value = '';
                commentsModal.classList.remove('hidden');
                commentsModal.classList.add('flex');
            };
            const closeComments = ()=>{
                if(!commentsModal) return;
                commentsModal.classList.add('hidden');
                commentsModal.classList.remove('flex');
                const ta = document.getElementById('intervalCommentText');
                if (ta) ta.value = '';
                const lbl = document.getElementById('cmtIntervalLabel');
                if (lbl) lbl.textContent = '';
                cmtRange = null;
            };
            document.getElementById('schedCommentsCloseBtn')?.addEventListener('click', closeComments);
            document.getElementById('schedCommentsOkBtn')?.addEventListener('click', closeComments);
            const openDelete = (metaText, payload)=>{ 
                document.getElementById('schedDeleteText').textContent = metaText||'Вы действительно хотите удалить выбранный интервал?';
                deleteModal.dataset.payload = JSON.stringify(payload||{});
                deleteModal.classList.remove('hidden'); deleteModal.classList.add('flex'); 
            };
            const closeDelete = ()=>{ if(!deleteModal) return; deleteModal.classList.add('hidden'); deleteModal.classList.remove('flex'); };
            document.getElementById('schedDeleteCancel')?.addEventListener('click', closeDelete);
            const schedDeleteConfirmBtn = document.getElementById('schedDeleteConfirm');
            if (schedDeleteConfirmBtn) schedDeleteConfirmBtn.onclick = async ()=>{ 
                try{
                    const p = JSON.parse(deleteModal.dataset.payload||'{}');
                    await fetch('/personnel/clear', { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(p)});
                    // Also remove intersecting comments (excluding global handled on backend)
                    try{ await fetch('/comments', { method:'DELETE', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(p) }); }catch(_e){}
                    closeDelete();
                    await renderStaffSchedule();
                    try{ document.querySelectorAll('#staffSchedGrid td.sched-selected').forEach(c=>c.classList.remove('sched-selected')); }catch(_e){}
                }catch(e){ closeDelete(); }
            };

            // Выделение диапазона мышью (похоже на «Персонал»)
            let isSelecting = false; let startCell = null; let currentRow = null;
            // Инлайн-меню для пустых слотов — один экземпляр + делегирование событий
            let inlineMenu = document.getElementById('schedInlineMenu');
            let inlineDocHandler = null;
            if (!inlineMenu) {
                inlineMenu = document.createElement('div');
                inlineMenu.id = 'schedInlineMenu';
                inlineMenu.className = 'fixed bg-white border rounded shadow-lg text-sm hidden';
                inlineMenu.style.zIndex = '1001';
                inlineMenu.innerHTML = `<div class="py-1">
                    <button id="schedAddWork" class="px-4 py-2 hover:bg-gray-100 w-full text-left">Добавить рабочее время</button>
                    <button id="schedAddOff" class="px-4 py-2 hover:bg-gray-100 w-full text-left">Отметить нерабочее время</button>
                </div>`;
                document.body.appendChild(inlineMenu);

                // Делегирование кликов по кнопкам меню
                inlineMenu.addEventListener('click', async (ev) => {
                    ev.stopPropagation();
                    const btn = ev.target.closest('button');
                    if (!btn) return;
                    const p = JSON.parse(inlineMenu.dataset.payload || '{}');
                    try {
                        if (btn.id === 'schedAddWork') {
                            console.log('schedAddWork click', p);
                            // Сначала очищаем пересечения (убираем off/старые busy), затем красим как рабочее время
                            try { await fetch('/personnel/clear', { method:'POST', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ employee_id: p.employee_id, project_id: p.project_id, date: p.date, start_time: p.start_time, end_time: p.end_time }) }); } catch(_e){}
                            await fetch('/personnel/assign', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({
                                    employee_id: p.employee_id,
                                    project_id: p.project_id,
                                    date: p.date,
                                    start_time: p.start_time,
                                    end_time: p.end_time,
                                    rate_type: null,
                                    rate: null,
                                    comment: '',
                                }),
                            });
                            const currentTbody = document.querySelector('#staffSchedGrid tbody');
                            let row = (currentTbody ? currentTbody.querySelector(`tr[data-emp-id="${p.employee_id}"]`) : null) || document.querySelector(`#staffSchedGrid tr[data-emp-id="${p.employee_id}"]`);
                            let painted = 0;
                            if (row) {
                                const cells = Array.from(row.querySelectorAll('td.sched-cell'));
                                const fromMin = timeToMin(p.start_time);
                                const toMin = timeToMin(p.end_time);
                                cells.forEach((td) => {
                                    const s = timeToMin(td.dataset.from);
                                    const e = timeToMin(td.dataset.to);
                                    // Красим только пересекающиеся со [from, to) (half-open)
                                    if (s < toMin && e > fromMin) {
                                        // Рабочее время имеет приоритет в режиме «добавить рабочее время»
                                        makeGreen(td);
                                        painted++;
                                    }
                                });
                            }
                            refreshStaffSum(p.employee_id);
                            // Фоллбек: если по какой-то причине локально не закрасили всё, синхронизируем сетку
                            try{
                                const step = {'60m':60,'30m':30,'15m':15,'10m':10,'5m':5}[document.getElementById('staffSchedInterval').value] || 60;
                                const expected = Math.max(1, (timeToMin(p.end_time)-timeToMin(p.start_time))/step);
                                if (painted < expected) { setTimeout(()=>renderStaffSchedule(), 0); }
                            }catch(_e){ /* no-op */ }
                            hideInline();
                        } else if (btn.id === 'schedAddOff') {
                            console.log('schedAddOff click', p);
                            // Сначала очищаем пересекающиеся интервалы (убираем busy), затем красим как нерабочее
                            try { await fetch('/personnel/clear', { method:'POST', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ employee_id: p.employee_id, project_id: p.project_id, date: p.date, start_time: p.start_time, end_time: p.end_time }) }); } catch(_e){}
                            await fetch('/personnel/non-working', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({
                                    employee_id: p.employee_id,
                                    project_id: p.project_id,
                                    date: p.date,
                                    start_time: p.start_time,
                                    end_time: p.end_time,
                                }),
                            });
                            // Удалим комментарии в пересечении диапазона
                            try { await fetch('/comments', { method: 'DELETE', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ employee_id: p.employee_id, project_id: p.project_id, date: p.date, start_time: p.start_time, end_time: p.end_time }) }); } catch(_e){}
                            const currentTbody2 = document.querySelector('#staffSchedGrid tbody');
                            let row = (currentTbody2 ? currentTbody2.querySelector(`tr[data-emp-id="${p.employee_id}"]`) : null) || document.querySelector(`#staffSchedGrid tr[data-emp-id="${p.employee_id}"]`);
                            let paintedOff = 0;
                            if (row) {
                                const cells = Array.from(row.querySelectorAll('td.sched-cell'));
                                const fromMin = timeToMin(p.start_time);
                                const toMin = timeToMin(p.end_time);
                                cells.forEach((td) => {
                                    const s = timeToMin(td.dataset.from);
                                    const e = timeToMin(td.dataset.to);
                                    if (s < toMin && e > fromMin) {
                                        // Нерабочее время имеет приоритет при отметке «нерабочее»
                                        makeRed(td);
                                        paintedOff++;
                                    }
                                });
                            }
                            refreshStaffSum(p.employee_id);
                            try{
                                const step = {'60m':60,'30m':30,'15m':15,'10m':10,'5m':5}[document.getElementById('staffSchedInterval').value] || 60;
                                const expected = Math.max(1, (timeToMin(p.end_time)-timeToMin(p.start_time))/step);
                                if (paintedOff < expected) { setTimeout(()=>renderStaffSchedule(), 0); }
                            }catch(_e){ /* no-op */ }
                            hideInline();
                        } else if (btn.id === 'schedDeleteInterval') {
                            console.log('schedDeleteInterval click', p);
                            const resp = await fetch('/personnel/clear', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({
                                    employee_id: p.employee_id,
                                    project_id: p.project_id,
                                    date: p.date,
                                    start_time: p.start_time,
                                    end_time: p.end_time,
                                }),
                            });
                            if (!resp.ok) { console.error('clear failed', await resp.text()); return; }
                            // Remove intersecting comments as well (backend skips global)
                            try{ await fetch('/comments', { method:'DELETE', headers:{ 'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ employee_id: p.employee_id, project_id: p.project_id, date: p.date, start_time: p.start_time, end_time: p.end_time }) }); }catch(_e){}
                            const currentTbody3 = document.querySelector('#staffSchedGrid tbody');
                            let row = (currentTbody3 ? currentTbody3.querySelector(`tr[data-emp-id="${p.employee_id}"]`) : null) || document.querySelector(`#staffSchedGrid tr[data-emp-id="${p.employee_id}"]`);
                            if (row) {
                                const cells = Array.from(row.querySelectorAll('td.sched-cell'));
                                const fromMin = timeToMin(p.start_time);
                                const toMin = timeToMin(p.end_time);
                                cells.forEach((td) => {
                                    const s = timeToMin(td.dataset.from);
                                    const e = timeToMin(td.dataset.to);
                                    if (s < toMin && e > fromMin) {
                                        td.style.background = '';
                                        td.classList.remove('bg-red-500');
                                        td.classList.remove('bg-green-500');
                                    }
                                });
                            }
                            // После удаления сразу убираем выделение и закрываем меню
                            clearSelection();
                            currentRow = null;
                            startCell = null;
                            hideMenuOnly();
                    forceClearSelection();
                        } else if (btn.id === 'schedViewComments') {
                            try{
                                if (typeof loadComments === 'function') {
                                    cmtEmployeeId = p.employee_id;
                                    cmtRange = { date: p.date, from: p.start_time, to: p.end_time };
                                    await loadComments();
                                }
                            }catch(_e){ /* no-op */ }
                            if (typeof openComments === 'function') openComments();
                            hideInline();
                        }
                    } catch (err) {
                        console.error('inlineMenu action error', err);
                    }
                    hideInline();
                });
            }

            const hideMenuOnly = () => {
                inlineMenu.classList.add('hidden');
                inlineMenu.dataset.payload = '{}';
            };
            const hideInline = () => {
                inlineMenu.classList.add('hidden');
                inlineMenu.dataset.payload = '{}';
                // снимаем выделение только когда закрываем меню кликом вне
                clearSelection();
                currentRow = null;
                startCell = null;
            };
            function clearSelection(){ tbody.querySelectorAll('.sched-selected').forEach(c=>{ c.classList.remove('sched-selected'); c.classList.remove('sched-selected-solid'); c.style.backgroundColor=''; c.style.borderColor=''; c.style.color=''; }); }
            function forceClearSelection(){
                try { document.querySelectorAll('#staffSchedGrid td.sched-selected').forEach(c=>c.classList.remove('sched-selected')); } catch(_e){}
                isSelecting = false; currentRow = null; startCell = null;
            }
            tbody.querySelectorAll('td.sched-cell').forEach(td=>{
                td.addEventListener('mousedown', ()=>{
                    // При старте новой выделения убираем прежний обработчик и прячем меню
                    if (inlineDocHandler) { document.removeEventListener('click', inlineDocHandler, true); inlineDocHandler = null; }
                    hideMenuOnly();
                    isSelecting=true; currentRow=td.parentElement; startCell=td; clearSelection();
                    td.classList.add('sched-selected');
                    if(!td.classList.contains('bg-green-500') && !td.classList.contains('bg-red-500')) td.classList.add('sched-selected-solid');
                });
                td.addEventListener('mouseenter', ()=>{
                    if(!isSelecting || td.parentElement!==currentRow) return;
                    clearSelection();
                    const cells = Array.from(currentRow.querySelectorAll('td.sched-cell'));
                    const a = cells.indexOf(startCell); const b = cells.indexOf(td);
                    const [fromIdx,toIdx] = a<=b ? [a,b] : [b,a];
                    for(let i=fromIdx;i<=toIdx;i++) { cells[i].classList.add('sched-selected'); if(!cells[i].classList.contains('bg-green-500') && !cells[i].classList.contains('bg-red-500')) cells[i].classList.add('sched-selected-solid'); }
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
                    // Для зелёных ячеек показываем меню: «Посмотреть комментарии», «Удалить»
                    const rect = selected[selected.length-1].getBoundingClientRect();
                    inlineMenu.innerHTML = `<div class=\"py-1\">
                        <button id=\"schedViewComments\" class=\"px-4 py-2 hover:bg-gray-100 w-full text-left\">Посмотреть комментарии</button>
                        <button id=\"schedDeleteInterval\" class=\"px-4 py-2 text-red-600 hover:bg-gray-100 w-full text-left\">Удалить</button>
                    </div>`;
                    inlineMenu.dataset.payload = JSON.stringify({ employee_id: empId, project_id: {{ $project->id }}, date, start_time: from, end_time: to });
                    inlineMenu.style.left = `${rect.right + 8}px`;
                    inlineMenu.style.top = `${Math.max(rect.top, 60)}px`;
                    inlineMenu.classList.remove('hidden');
                    if (inlineDocHandler) { document.removeEventListener('click', inlineDocHandler, true); inlineDocHandler = null; }
                    inlineDocHandler = (ev)=>{ if(!inlineMenu.contains(ev.target)) { hideInline(); document.removeEventListener('click', inlineDocHandler, true); inlineDocHandler = null; } };
                    setTimeout(()=>document.addEventListener('click', inlineDocHandler, true), 0);
                } else if(allRed){
                    // Для красных ячеек показываем контекстное меню «Удалить», как в «Персонал»
                    const rect = selected[selected.length-1].getBoundingClientRect();
                    inlineMenu.innerHTML = `<div class=\"py-1\">
                        <button id=\"schedDeleteInterval\" class=\"px-4 py-2 text-red-600 hover:bg-gray-100 w-full text-left\">Удалить</button>
                    </div>`;
                    inlineMenu.dataset.payload = JSON.stringify({ employee_id: empId, project_id: {{ $project->id }}, date, start_time: from, end_time: to });
                    inlineMenu.style.left = `${rect.right + 8}px`;
                    inlineMenu.style.top = `${Math.max(rect.top, 60)}px`;
                    inlineMenu.classList.remove('hidden');
                    if (inlineDocHandler) { document.removeEventListener('click', inlineDocHandler, true); inlineDocHandler = null; }
                    inlineDocHandler = (ev)=>{ if(!inlineMenu.contains(ev.target)) { hideInline(); document.removeEventListener('click', inlineDocHandler, true); inlineDocHandler = null; } };
                    setTimeout(()=>document.addEventListener('click', inlineDocHandler, true), 0);
                } else {
                    // Пустые ячейки — показать инлайн меню (всегда только действия добавления)
                    inlineMenu.innerHTML = `<div class=\"py-1\">\n                        <button id=\"schedAddWork\" class=\"px-4 py-2 hover:bg-gray-100 w-full text-left\">Добавить рабочее время</button>\n                        <button id=\"schedAddOff\" class=\"px-4 py-2 hover:bg-gray-100 w-full text-left\">Отметить нерабочее время</button>\n                    </div>`;
                    const rect = selected[selected.length-1].getBoundingClientRect();
                    inlineMenu.style.left = `${rect.right + 8}px`;
                    inlineMenu.style.top = `${Math.max(rect.top, 60)}px`;
                    inlineMenu.classList.remove('hidden');
                    // Сохраняем payload для глобальных обработчиков
                    inlineMenu.dataset.payload = JSON.stringify({ employee_id: empId, project_id: {{ $project->id }}, date, start_time: from, end_time: to });
                    console.log('inlineMenu payload set', inlineMenu.dataset.payload);
                    if (inlineDocHandler) { document.removeEventListener('click', inlineDocHandler, true); inlineDocHandler = null; }
                    inlineDocHandler = (ev)=>{ if(!inlineMenu.contains(ev.target)) { hideInline(); document.removeEventListener('click', inlineDocHandler, true); inlineDocHandler = null; } };
                    setTimeout(()=>document.addEventListener('click', inlineDocHandler, true), 0);
                }
                // Не сбрасываем выделение — снимаем его только при закрытии меню
            };
            document.addEventListener('mouseup', ()=>{ if(!isSelecting) return; isSelecting=false; finalizeSelection(); });
            table.addEventListener('mouseleave', ()=>{ if(isSelecting){ isSelecting=false; finalizeSelection(); } });
            document.addEventListener('mousemove', (e)=>{ if(isSelecting && e.buttons===0){ isSelecting=false; finalizeSelection(); } });
        }

        // ====== «Все комментарии» (модалка) ======
        const commentsModal = document.getElementById('schedCommentsModal');
        const commentsList = document.getElementById('schedCommentsList');
        const cmtIntervalLabel = document.getElementById('cmtIntervalLabel');
        const cmtNotice = document.getElementById('schedCommentsNotice');
        function ensureNoIntervalCommentsRow(){
            const intervalRows = commentsList ? Array.from(commentsList.querySelectorAll('.cmt-row:not(#projectGeneralBanner)')) : [];
            if (commentsList && intervalRows.length === 0 && !document.getElementById('noCommentsRow')){
                commentsList.insertAdjacentHTML('beforeend', `<div id="noCommentsRow" class="text-center text-sm text-gray-500 py-8">Нет комментариев за этот интервал.</div>`);
            }
        }
        let cmtEmployeeId = null;
        let cmtRange = null; // {from, to, date}
        function setCmtIntervals(){}
        function addDays(dateStr, d){ const dt = new Date(dateStr); dt.setDate(dt.getDate()+d); return dt.toISOString().slice(0,10); }
        function getPeriod(dateStr){ return {from: dateStr, to: dateStr}; }
        function slotLabel(start, end){ return `${start.slice(0,5)}–${end.slice(0,5)}`; }
        function showNotice(msg, action){ if(!msg){ cmtNotice.classList.add('hidden'); cmtNotice.textContent=''; cmtNotice.onclick=null; return; } cmtNotice.textContent=msg; cmtNotice.classList.remove('hidden'); cmtNotice.onclick=action||null; }
        async function loadComments(){
            if(!cmtEmployeeId) return;
            const date = (cmtRange?.date) || new Date().toISOString().slice(0,10);
            const from = cmtRange?.from || '00:00';
            const to   = cmtRange?.to   || '23:59';
            if (cmtIntervalLabel) {
                const fromLbl = (cmtRange?.from || '00:00').slice(0,5);
                const toLbl   = (cmtRange?.to   || '23:59').slice(0,5);
                cmtIntervalLabel.textContent = `${fromLbl}–${toLbl}`;
            }
            const url = `/comments?employee_id=${cmtEmployeeId}&date=${date}&project_id={{ $project->id }}&start_time=${from}&end_time=${to}`;
            const r = await fetch(url);
            const list = await r.json();
            commentsList.innerHTML = '';

            // Пробуем подтянуть общий комментарий как закреплённый (если существует)
            try{
                const gcResp = await fetch(`/projects/{{ $project->id }}/comment`);
                const gc = await gcResp.json();
                if(gc?.comment){
                    const bannerHTML = `<div id="projectGeneralBanner" class="cmt-row flex items-center gap-3 p-2 border rounded bg-white min-h-[44px]">
                        <div class="w-28 shrink-0 text-sm text-gray-700"><span class="mr-1">📌</span>Общий</div>
                        <div class="flex-1 text-sm">${String(gc.comment).replace(/&/g,'&amp;').replace(/</g,'&lt;')}</div>
                        <button class="ml-auto icon-btn icon-danger cmt-delete cmt-delete-general" aria-label="Удалить" data-id="${gc.id||''}"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342-.052.682-.108 1.022-.168M4.772 5.79c.34.06.68.116 1.022.168m2.68-2.29A2.25 2.25 0 0110.5 3h3a2.25 2.25 0 012.25 2.25V6m-9 0h9M5.25 6H18.75M6 6v12.75A2.25 2.25 0 008.25 21h7.5A2.25 2.25 0 0018 18.75V6"/></svg></button>
                    </div>`;
                    commentsList.insertAdjacentHTML('beforeend', bannerHTML);
                }
            }catch(_e){ /* no-op */ }

            if(!Array.isArray(list) || list.length===0){
                commentsList.insertAdjacentHTML('beforeend', `<div id=\"noCommentsRow\" class=\"text-center text-sm text-gray-500 py-8\">Нет комментариев за этот интервал.</div>`);
                return;
            }
            let grouped = {};
            grouped[(cmtRange?.date)||from] = list;
            const frags = [];
            Object.keys(grouped).sort().forEach(d=>{
                grouped[d].sort((a,b)=> a.start_time.localeCompare(b.start_time));
                grouped[d].forEach(item=>{
                    const slot = slotLabel(item.start_time, item.end_time);
                    const intervalBadge = (()=>{
                        const m = (new Date(`1970-01-01T${item.end_time}:00Z`)-new Date(`1970-01-01T${item.start_time}:00Z`))/60000;
                        if(m===5) return '5 мин'; if(m===10) return '10 мин'; if(m===15) return '15 мин'; if(m===30) return '30 мин'; if(m===60) return '60 мин'; if(m===240) return '4 часа'; if(m===720) return '12 часов'; if(m===1440) return '1 день'; return '';
                    })();
                    frags.push(`<div class=\"cmt-row flex items-start gap-3 p-2 border rounded\">
                        <div class=\"w-28 shrink-0 text-sm text-gray-700\">${slot}</div>
                        <div class=\"flex-1 text-sm\">
                            <div contenteditable data-id=\"${item.id||''}\" class=\"cmt-editable outline-none\">${(item.comment||'').replace(/&/g,'&amp;').replace(/</g,'&lt;')}</div>
                            <div class=\"mt-1 text-xs text-gray-500 flex items-center gap-2\">${intervalBadge?`<span class=\"px-1.5 py-0.5 bg-gray-100 rounded\">${intervalBadge}</span>`:''}<button class=\"ml-auto icon-btn icon-danger cmt-delete\" aria-label=\"Удалить\" data-id=\"${item.id||''}\"><svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.75\" stroke=\"currentColor\" class=\"w-5 h-5\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342-.052.682-.108 1.022-.168M4.772 5.79c.34.06.68.116 1.022.168m2.68-2.29A2.25 2.25 0 0110.5 3h3a2.25 2.25 0 012.25 2.25V6m-9 0h9M5.25 6H18.75M6 6v12.75A2.25 2.25 0 008.25 21h7.5A2.25 2.25 0 0018 18.75V6\"/></svg></button></div>
                        </div>
                    </div>`);
                });
            });
            // Добавляем строки после уже вставленного общего комментария (если он есть)
            commentsList.insertAdjacentHTML('beforeend', frags.join(''));
            // Делегирование: удаление без перерендера модалки
            commentsList.querySelectorAll('.cmt-delete').forEach(btn=>{
                btn.addEventListener('click', async ()=>{
                    const id = btn.dataset.id; if(!id) return;
                    let row = btn.closest('.cmt-row');
                    if (!row) row = btn.closest('div[style]')?.parentElement?.parentElement; // бэкаповый поиск
                    if (!row) row = btn.closest('div');
                    if (row) row.remove();
                    try{ await fetch(`/comments/${id}`, { method:'DELETE', headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content} }); }catch(_e){}
                    refreshCommentCounters();
                    ensureNoIntervalCommentsRow();
                });
            });
            // Удаление общего комментария
            commentsList.querySelectorAll('.cmt-delete-general').forEach(btn=>{
                btn.addEventListener('click', async ()=>{
                    const id = btn.dataset.id; if(!id) return;
                    const banner = document.getElementById('projectGeneralBanner'); if(banner) banner.remove();
                    try{ await fetch(`/comments/${id}`, { method:'DELETE', headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content} }); }catch(_e){}
                });
            });
            // Делегирование: инлайн-редактирование
            commentsList.querySelectorAll('.cmt-editable').forEach(div=>{
                div.addEventListener('blur', async ()=>{
                    const id = div.getAttribute('data-id'); if(!id) return; const txt = div.textContent.trim();
                    await fetch(`/comments/${id}`, { method:'PUT', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ comment: txt }) });
                });
            });
            showNotice('');
            // Запрос "все комментарии" отключен по требованию — остаётся только дневной список
        }
        function setupCommentsUI({employeeId, date, from, to}){
            cmtEmployeeId = employeeId;
            cmtRange = { date: date || (new Date()).toISOString().slice(0,10), from: from || '00:00', to: to || '23:59' };
            loadComments();
        }


        // Кнопки «Все комментарии» в таблице сотрудников
        async function refreshCommentCounters(){
            const rows = Array.from(document.querySelectorAll('#staffTableBody tr[data-emp-id]'));
            const date = (new Date()).toISOString().slice(0,10);
            for(const tr of rows){
                const eid = tr.dataset.empId; try{
                    // Счётчик больше не отображаем; ничего не делаем здесь
                }catch(_e){ /* no-op */ }
            }
        }
        refreshCommentCounters();
        document.querySelectorAll('.open-all-comments').forEach(btn=>{
            btn.addEventListener('click', (e)=>{ e.preventDefault(); const empId = btn.dataset.empId; openAllComments(empId); });
        });

        // Сохранение комментария для выбранного интервала
        document.getElementById('saveIntervalComment')?.addEventListener('click', async ()=>{
            const ta = document.getElementById('intervalCommentText');
            const txt = (ta?.value||'').trim();
            if(!txt || !cmtEmployeeId || !cmtRange) return;
            try{
                const resp = await fetch('/comments', { method:'POST', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ employee_id: cmtEmployeeId, project_id: {{ $project->id }}, date: cmtRange.date, start_time: cmtRange.from, end_time: cmtRange.to, comment: txt })});
                const item = await resp.json();
                const createdId = item?.comment?.id || item?.id || '';
                ta.value='';
                // Вставим новую карточку без перерендера
                const slot = `${cmtRange.from}–${cmtRange.to}`;
                const row = document.createElement('div');
                row.className = 'cmt-row flex items-start gap-3 p-2 border rounded';
                row.innerHTML = `<div class="w-28 shrink-0 text-sm text-gray-700">${slot}</div>
                    <div class="flex-1 text-sm">
                        <div contenteditable data-id="${createdId}" class="cmt-editable outline-none">${txt.replace(/&/g,'&amp;').replace(/</g,'&lt;')}</div>
                        <div class="mt-1 text-xs text-gray-500 flex items-center gap-2"><button class="ml-auto icon-btn icon-danger cmt-delete" aria-label="Удалить" data-id="${createdId}"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342-.052.682-.108 1.022-.168M4.772 5.79c.34.06.68.116 1.022.168m2.68-2.29A2.25 2.25 0 0110.5 3h3a2.25 2.25 0 012.25 2.25V6m-9 0h9M5.25 6H18.75M6 6v12.75A2.25 2.25 0 008.25 21h7.5A2.25 2.25 0 0018 18.75V6"/></svg></button></div>
                    </div>`;
                const empty = document.getElementById('noCommentsRow'); if(empty) empty.remove();
                const banner = document.getElementById('projectGeneralBanner');
                if (banner) {
                    banner.insertAdjacentElement('afterend', row);
                } else {
                commentsList.prepend(row);
                }
                // Навешиваем обработчики на только что вставленную карточку
                const delBtn = row.querySelector('.cmt-delete');
                if (delBtn) {
                    delBtn.addEventListener('click', async ()=>{
                        const id = delBtn.dataset.id; if(!id) return; row.remove();
                        try{ await fetch(`/comments/${id}`, { method:'DELETE', headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content } }); }catch(_e){}
                        refreshCommentCounters();
                        ensureNoIntervalCommentsRow();
                    });
                }
                const editable = row.querySelector('.cmt-editable');
                if (editable) {
                    editable.addEventListener('blur', async ()=>{
                        const id = editable.getAttribute('data-id'); if(!id) return; const t = editable.textContent.trim();
                        await fetch(`/comments/${id}`, { method:'PUT', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ comment: t }) });
                    });
                }
            }catch(_e){ /* no-op */ }
        });

        // Глобальные обработчики закрытия модалки комментариев
        (function ensureCommentsCloseHandlers(){
            const modal = document.getElementById('schedCommentsModal');
            const closeBtn = document.getElementById('schedCommentsCloseBtn');
            const close = ()=>{ modal.classList.add('hidden'); modal.classList.remove('flex'); };
            closeBtn?.addEventListener('click', close);
            // Клик по подложке
            modal?.addEventListener('click', (e)=>{ if (e.target === modal) close(); });
            // Esc
            document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape' && !modal.classList.contains('hidden')) close(); });
        })();

        // ====== «Все комментарии сотрудника» ======
        const allCommentsModal = document.getElementById('allCommentsModal');
        const allCommentsList = document.getElementById('allCommentsList');
        const allCommentsDate = document.getElementById('allCommentsDate');
        const pgcTextarea = document.getElementById('projectGeneralCommentText');
        const allGeneralBanner = document.getElementById('allGeneralBanner');

        function renderAllGeneralBanner(text, id){
            if(!allGeneralBanner) return;
            const value = String(text||'').trim();
            if(!value){ allGeneralBanner.classList.add('hidden'); allGeneralBanner.innerHTML=''; return; }
            allGeneralBanner.classList.remove('hidden');
            allGeneralBanner.innerHTML = `<div class="cmt-row flex items-center gap-3 p-2 border rounded bg-white min-h-[44px]">
                <div class="w-28 shrink-0 text-sm text-gray-700"><span class="mr-1">📌</span>Общий</div>
                <div class="flex-1 text-sm">${value.replace(/&/g,'&amp;').replace(/</g,'&lt;')}</div>
                <button class="ml-auto icon-btn icon-danger cmt-delete-general" aria-label="Удалить общий комментарий" data-id="${id||''}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342-.052.682-.108 1.022-.168M4.772 5.79c.34.06.68.116 1.022.168m2.68-2.29A2.25 2.25 0 0110.5 3h3a2.25 2.25 0 012.25 2.25V6m-9 0h9M5.25 6H18.75M6 6v12.75A2.25 2.25 0 008.25 21h7.5A2.25 2.25 0 0018 18.75V6"/></svg>
                </button>
            </div>`;
            const delBtn = allGeneralBanner.querySelector('.cmt-delete-general');
            if (delBtn) {
                delBtn.addEventListener('click', async ()=>{
                    const gid = delBtn.dataset.id; if(!gid) return;
                    try{ await fetch(`/comments/${gid}`, { method:'DELETE', headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content} }); }catch(_e){}
                    allGeneralBanner.classList.add('hidden'); allGeneralBanner.innerHTML='';
                    if (pgcTextarea) pgcTextarea.value = '';
                });
            }
        }
        async function loadAllComments(employeeId){
            if(!employeeId) return;
            try{
                const dateParam = allCommentsDate?.value ? `&date=${encodeURIComponent(allCommentsDate.value)}` : '';
                const url = `/comments/all?employee_id=${employeeId}&project_id={{ $project->id }}${dateParam}`;
                const r = await fetch(url, { headers:{ 'Accept':'application/json' } });
                const list = await r.json();
                allCommentsList.innerHTML = '';
                if(!Array.isArray(list) || list.length===0){
                    allCommentsList.insertAdjacentHTML('beforeend', `<div class="text-center text-sm text-gray-500 py-8">Комментариев нет.</div>`);
                } else {
                    const byDate = {};
                    list.forEach(it=>{ (byDate[it.date] ||= []).push(it); });
                    Object.keys(byDate).sort().forEach(date=>{
                        const [y,m,d] = String(date).split('-');
                        allCommentsList.insertAdjacentHTML('beforeend', `<div class=\"text-xs text-gray-500 mt-4\">${d}.${m}.${y}</div>`);
                        byDate[date].sort((a,b)=> String(a.start_time).localeCompare(String(b.start_time)) );
                        byDate[date].forEach(item=>{
                            const slot = `${String(item.start_time||'').slice(0,5)}–${String(item.end_time||'').slice(0,5)}`;
                            const row = document.createElement('div');
                            row.className = 'cmt-row flex items-start gap-3 p-2 border rounded';
                            row.innerHTML = `<div class=\"w-28 shrink-0 text-sm text-gray-700\">${slot}</div>
                                <div class=\"flex-1 text-sm\">
                                    <div contenteditable data-id=\"${item.id||''}\" class=\"cmt-editable outline-none\">${String(item.comment||'').replace(/&/g,'&amp;').replace(/</g,'&lt;')}</div>
                                    <div class=\"mt-1 text-xs text-gray-500 flex items-center gap-2\"><button class=\"ml-auto icon-btn icon-danger cmt-delete\" aria-label=\"Удалить\" data-id=\"${item.id||''}\"><svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.75\" stroke=\"currentColor\" class=\"w-5 h-5\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342-.052.682-.108 1.022-.168M4.772 5.79c.34.06.68.116 1.022.168m2.68-2.29A2.25 2.25 0 0110.5 3h3a2.25 2.25 0 012.25 2.25V6m-9 0h9M5.25 6H18.75M6 6v12.75A2.25 2.25 0 008.25 21h7.5A2.25 2.25 0 0018 18.75V6\"/></svg></button></div>
                                </div>`;
                            allCommentsList.appendChild(row);
                        });
                    });
                    // обработчики
                    allCommentsList.querySelectorAll('.cmt-delete').forEach(btn=>{
                        btn.addEventListener('click', async ()=>{
                            const id = btn.dataset.id; if(!id) return;
                            let row = btn.closest('.cmt-row'); if(row) row.remove();
                            try{ await fetch(`/comments/${id}`, { method:'DELETE', headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name=\"csrf-token\"]').content} }); }catch(_e){}
                        });
                    });
                    allCommentsList.querySelectorAll('.cmt-editable').forEach(div=>{
                        div.addEventListener('blur', async ()=>{
                            const id = div.getAttribute('data-id'); if(!id) return; const txt = div.textContent.trim();
                            await fetch(`/comments/${id}`, { method:'PUT', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name=\"csrf-token\"]').content }, body: JSON.stringify({ comment: txt }) });
                        });
                    });
                }

                // подтянуть текущий общий комментарий проекта
                try{
                    const gcResp = await fetch(`/projects/{{ $project->id }}/comment`);
                    const gc = await gcResp.json();
                    if (pgcTextarea) pgcTextarea.value = '';
                    renderAllGeneralBanner(gc?.comment||'', gc?.id||'');
                }catch(_e){}
            }catch(_e){ /* no-op */ }
        }
        function openAllComments(employeeId){
            // при первом открытии — дата по умолчанию = дате начала проекта (если есть), иначе сегодня
            if (allCommentsDate && !allCommentsDate.value) allCommentsDate.value = "{{ optional(\Carbon\Carbon::parse($project->start_date))->format('Y-m-d') ?? now()->format('Y-m-d') }}";
            loadAllComments(employeeId);
            if(allCommentsModal){ allCommentsModal.classList.remove('hidden'); allCommentsModal.classList.add('flex'); }
            // пересчёт на смену даты
            allCommentsDate?.addEventListener('change', ()=> loadAllComments(employeeId));
        }
        (function ensureAllCommentsCloseHandlers(){
            const modal = document.getElementById('allCommentsModal');
            const closeBtn = document.getElementById('allCommentsCloseBtn');
            const close = ()=>{ modal.classList.add('hidden'); modal.classList.remove('flex'); };
            closeBtn?.addEventListener('click', close);
            modal?.addEventListener('click', (e)=>{ if (e.target === modal) close(); });
            document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape' && !modal.classList.contains('hidden')) close(); });
        })();

        // Сохранение общего комментария проекта
        document.getElementById('saveProjectGeneralComment')?.addEventListener('click', async ()=>{
            try{
                const txt = (pgcTextarea?.value||'').trim();
                const resp = await fetch(`/projects/{{ $project->id }}/comment`, { method:'POST', headers:{ 'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ comment: txt })});
                const data = await resp.json();
                renderAllGeneralBanner(txt, data?.id||'');
                if (pgcTextarea) pgcTextarea.value = ''; // clear after save
            }catch(_e){}
        });
        let sortDirection = 1;

        const categoriesBase = "{{ url('/categories') }}"; // базовый URL
        const projectEquipmentListBase = "{{ route('projects.equipmentList', $project) }}"; // новый эндпоинт (ниже добавим)

        async function loadEquipment(categoryId) {
            try {
                window.currentCategoryId = categoryId;
                const url = `{{ route('projects.equipmentList', $project) }}?category_id=${categoryId}`;
                const r = await fetch(url);
                if (!r.ok) throw new Error('Ошибка загрузки оборудования');
                const data = await r.json();
                console.log('Data:', data); // Для отладки в консоли
                if (!Array.isArray(data)) {
                    alert('Ошибка данных: не массив');
                    return;
                }
                const tbody = document.getElementById('equipmentTableBody');
                if (!tbody) return;
                tbody.innerHTML = '';
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-red-600">Нет оборудования в этой категории</td></tr>';
                    return;
                }
                data.forEach(eq => {
                    const tr = document.createElement('tr');
                    tr.dataset.id = eq.id;
                    tr.innerHTML = `
                <td>${eq.name}</td>
                <td>${eq.description}</td>
                <td>${eq.price}</td>
                <td>${eq.status}</td>
                <td>
                    <button onclick="editEquipment(${eq.id})">Редактировать</button>
                    <button onclick="deleteEquipment(${eq.id})">Удалить</button>
                    <button onclick="attachToProject(${eq.id})">Прикрепить к проекту</button>
                    <button onclick="detachFromProject(${eq.id})">Открепить</button>
                </td>
            `;
                    tbody.appendChild(tr);
                });
                attachFilterHandler();
            } catch (e) {
                alert(e.message);
            }
        }

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

            const openCatalogBtn = document.getElementById('openCatalog');
            const closeCatalogBtn = document.getElementById('closeCatalog');
            const sidebar = document.getElementById('catalogSidebar');
            const container = document.querySelector('.container.mx-auto.p-6');

            if (openCatalogBtn) {
                openCatalogBtn.addEventListener('click', function() {
                    if (sidebar) {
                        sidebar.classList.toggle('hidden');
                        sidebar.classList.toggle('translate-x-0');
                        sidebar.classList.toggle('-translate-x-full');
                        container.classList.toggle('ml-[33%]');
                        container.classList.toggle('transition-all');
                        container.classList.toggle('duration-300');
                        if (!sidebar.classList.contains('hidden') && !document.getElementById('catalogTree').innerHTML) {
                            loadCatalog();
                        }
                    }
                });
            }

            if (closeCatalogBtn) {
                closeCatalogBtn.addEventListener('click', function() {
                    if (sidebar) {
                        sidebar.classList.add('hidden');
                        sidebar.classList.remove('translate-x-0');
                        sidebar.classList.add('-translate-x-full');
                        container.classList.remove('ml-[33%]');
                        container.classList.remove('transition-all');
                        container.classList.remove('duration-300');
                    }
                });
            }

            async function loadCatalog() {
                try {
                    const r = await fetch('{{ route('projects.catalog') }}');
                    if (!r.ok) throw new Error('Ошибка загрузки каталога');
                    const tree = await r.json();
                    console.log('Catalog Data:', tree); // Для отладки
                    const treeDiv = document.getElementById('catalogTree');
                    treeDiv.innerHTML = '';

                    function renderTree(parent, data, level = 0) {
                        for (const [key, val] of Object.entries(data)) {
                            const div = document.createElement('div');
                            div.className = `pl-${level * 4} py-1`;
                            const toggle = document.createElement('span');
                            toggle.className = 'cursor-pointer mr-2 text-gray-500 hover:text-gray-700';
                            toggle.innerHTML = (val.sub && Object.keys(val.sub).length) || (val.equipment && Object.keys(val.equipment).length) ? '<svg class="inline w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>' : '';
                            const name = document.createElement('span');
                            name.textContent = key;
                            name.className = 'text-gray-700 font-medium';
                            div.appendChild(toggle);
                            div.appendChild(name);

                            const content = document.createElement('div');
                            content.className = 'pl-4 hidden';
                            if (val.sub && Object.keys(val.sub).length) {
                                renderTree(content, val.sub, level + 1);
                            }
                            if (val.equipment && Object.keys(val.equipment).length) {
                                for (const [eqName, eq] of Object.entries(val.equipment)) {
                                    const eqDiv = document.createElement('div');
                                    eqDiv.className = 'flex justify-between items-center py-1 hover:bg-gray-100 rounded px-2';
                                    eqDiv.innerHTML = `<span class="text-gray-600">${eqName} (кол-во: ${eq.qty}, цена: ${eq.price} ₽)</span>`;
                                    const addBtn = document.createElement('button');
                                    addBtn.innerHTML = '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>';
                                    addBtn.className = 'p-1 rounded hover:bg-green-100';
                                    addBtn.onclick = async () => {
                                        let quantity = 1;
                                        if (eq.qty > 1) {
                                            quantity = prompt('Введите количество:', 1);
                                            if (quantity === null || isNaN(parseInt(quantity)) || quantity < 1) {
                                                alert('Некорректное количество');
                                                return;
                                            }
                                            quantity = parseInt(quantity);
                                        }
                                        const status = eq.is_consumable ? 'used' : prompt('Статус (on_stock, assigned, used):', 'assigned');
                                        if (!status || !['on_stock', 'assigned', 'used'].includes(status)) {
                                            alert('Некорректный статус');
                                            return;
                                        }
                                        await addToEstimate(eq.id, quantity, status);
                                    };
                                    eqDiv.appendChild(addBtn);
                                    content.appendChild(eqDiv);
                                }
                            }
                            div.appendChild(content);
                            toggle.onclick = () => {
                                content.classList.toggle('hidden');
                                toggle.innerHTML = content.classList.contains('hidden')
                                    ? '<svg class="inline w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>'
                                    : '<svg class="inline w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>';
                            };
                            parent.appendChild(div);
                        }
                    }
                    renderTree(treeDiv, tree);
                } catch (e) {
                    alert('Ошибка загрузки каталога: ' + e.message);
                }
            }

            async function addToEstimate(equipmentId, quantity, status) {
                try {
                    equipmentId = parseInt(equipmentId);
                    quantity = parseInt(quantity);
                    if (isNaN(equipmentId) || equipmentId <= 0) {
                        throw new Error('Некорректный ID оборудования');
                    }
                    if (isNaN(quantity) || quantity < 1) {
                        throw new Error('Количество должно быть целым числом больше 0');
                    }
                    if (!['on_stock', 'assigned', 'used'].includes(status)) {
                        status = 'assigned';
                    }

                    const response = await fetch('{{ route('estimates.add_equipment', $currentEstimate) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ equipment_id: equipmentId, quantity: quantity, status: status })
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.error || 'Ошибка добавления оборудования');
                    console.log('addToEstimate response:', data);
                    updateEstimateTable(data);
                    alert('Оборудование добавлено в смету');
                } catch (e) {
                    console.error('addToEstimate error:', e);
                    alert('Ошибка: ' + e.message);
                }
            }



        function updateEstimateTable(data) {
            const tbody = document.getElementById('estimateTableBody');
            if (!tbody) {
                console.error('Table body not found');
                return;
            }

            const newEquipment = data.new_equipment || {};
            const calculated = data.calculated || {};
            const section = newEquipment.is_consumable ? 'materials' : 'equipment';
            const sectionHeaderText = newEquipment.is_consumable ? 'Материалы' : 'Оборудование';
            const hasViewPrices = {{ auth()->user()->can('view prices') ? 'true' : 'false' }};
            const canEdit = {{ $project->status !== 'completed' ? 'true' : 'false' }};

            // Функция для поиска заголовка секции
            const findHeaderRow = (text) => {
                return Array.from(tbody.querySelectorAll('tr')).find(tr => {
                    const td = tr.querySelector('td[colspan]');
                    return td && td.textContent.trim() === text;
                });
            };

            // Находим или создаём заголовок секции
            let sectionHeader = findHeaderRow(sectionHeaderText);
            if (!sectionHeader) {
                const colspan = hasViewPrices ? (canEdit && section === 'equipment' ? 7 : 6) : (canEdit && section === 'equipment' ? 3 : 2);
                sectionHeader = document.createElement('tr');
                sectionHeader.className = 'bg-gray-100';
                sectionHeader.innerHTML = `<td colspan="${colspan}" class="p-2 font-bold text-gray-800">${sectionHeaderText}</td>`;
                const insertBefore = section === 'equipment' ? tbody.firstChild : findHeaderRow('Услуги') || tbody.firstChild;
                tbody.insertBefore(sectionHeader, insertBefore);
            }

            // Находим или создаём строки категорий
            let lastCategoryRow = sectionHeader;
            let level = 0;
            (newEquipment.category_path || ['Без категории']).forEach((category, index) => {
                const categoryKey = (newEquipment.category_path || ['Без категории']).slice(0, index + 1).join(' > ');
                let categoryRow = findHeaderRow(categoryKey); // Используем ту же функцию, но для категорий без bg-gray-100
                if (!categoryRow) {
                    const colspan = hasViewPrices ? (canEdit && section === 'equipment' ? 7 : 6) : (canEdit && section === 'equipment' ? 3 : 2);
                    categoryRow = document.createElement('tr');
                    categoryRow.innerHTML = `<td class="p-2 border pl-${index * 4}" colspan="${colspan}">${category}</td>`;
                    lastCategoryRow.insertAdjacentElement('afterend', categoryRow);
                }
                lastCategoryRow = categoryRow;
                level = index + 1;
            });

            // Проверяем, существует ли строка с таким оборудованием
            let existingRow = tbody.querySelector(`tr[data-equipment-id="${newEquipment.id || 0}"]`);
            if (existingRow) {
                // Обновляем количество и суммы
                const qtyCell = existingRow.cells[1];
                const newQty = parseInt(qtyCell.textContent || 0) + (newEquipment.quantity || 0);
                qtyCell.textContent = newQty;
                if (hasViewPrices) {
                    const sumCell = existingRow.cells[3];
                    const afterDiscCell = existingRow.cells[5];
                    sumCell.textContent = ((newEquipment.price || 0) * newQty).toFixed(2);
                    afterDiscCell.textContent = ((newEquipment.price || 0) * newQty * (1 - ((calculated[section]?.discount || 0) / 100))).toFixed(2);
                }
            } else {
                // Добавляем новую строку
                const newRow = document.createElement('tr');
                newRow.dataset.equipmentId = newEquipment.id || 0;
                newRow.innerHTML = `
            <td class="p-2 border pl-${level * 4}">${newEquipment.name || 'Без названия'}</td>
            <td class="p-2 border">${newEquipment.quantity || 0}</td>
            ${hasViewPrices ? `
                <td class="p-2 border">${(newEquipment.price || 0).toFixed(2)}</td>
                <td class="p-2 border">${(newEquipment.sum || 0).toFixed(2)}</td>
                <td class="p-2 border">${(calculated[section]?.discount || 0)}</td>
                <td class="p-2 border">${(newEquipment.after_discount || 0).toFixed(2)}</td>
            ` : ''}
            ${canEdit && section === 'equipment' ? `
                <td class="p-2 border">
                    <button onclick="removeEquipment(${newEquipment.id || 0}, ${data.estimate_id})" class="text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </td>
            ` : ''}
        `;
                newRow.classList.add('new');
                setTimeout(() => newRow.classList.add('visible'), 10);
                lastCategoryRow.insertAdjacentElement('afterend', newRow);
            }

            // После добавления/обновления: Удаляем placeholder "Нет ..." если теперь есть строки в разделе
            const removeNoItemsRow = (headerText, noItemsText) => {
                const header = findHeaderRow(headerText);
                if (header) {
                    const nextRow = header.nextElementSibling;
                    if (nextRow && nextRow.querySelector('td').textContent.trim() === noItemsText) {
                        // Проверяем, есть ли теперь строки с data-equipment-id после заголовка (до следующего раздела)
                        let hasItems = false;
                        let current = nextRow.nextElementSibling;
                        while (current && !current.classList.contains('bg-gray-100') && !current.classList.contains('font-bold')) {
                            if (current.dataset.equipmentId) {
                                hasItems = true;
                                break;
                            }
                            current = current.nextElementSibling;
                        }
                        if (hasItems) {
                            nextRow.remove();
                        }
                    }
                }
            };

            removeNoItemsRow('Оборудование', 'Нет оборудования');
            removeNoItemsRow('Материалы', 'Нет материалов');
            removeNoItemsRow('Услуги', 'Нет услуг');

            // Обновляем значения в существующих строках итого (без удаления и добавления)
            const updateTotalRow = (label, sectionKey) => {
                const row = Array.from(tbody.querySelectorAll('tr.font-bold')).find(tr => tr.querySelector('td').textContent.trim() === label);
                if (row && hasViewPrices) {
                    row.cells[3].textContent = (calculated[sectionKey]?.total || 0).toFixed(2);
                    row.cells[4].textContent = (calculated[sectionKey]?.discount || 0);
                    row.cells[5].textContent = (calculated[sectionKey]?.after_disc || 0).toFixed(2);
                }
            };

            updateTotalRow('Итого оборудование', 'equipment');
            updateTotalRow('Итого материалы', 'materials');
            updateTotalRow('Итого услуги', 'services');

            // Обновляем подытог, налог, итого (они в colspan, обновляем последний td)
            if (hasViewPrices) {
                const subTotalRow = Array.from(tbody.querySelectorAll('tr.font-bold')).find(tr => tr.querySelector('td').textContent.trim() === 'Подытог');
                if (subTotalRow) subTotalRow.cells[subTotalRow.cells.length - 1].textContent = (calculated.subtotal || 0).toFixed(2) + ' ₽';

                const taxRow = Array.from(tbody.querySelectorAll('tr.font-bold')).find(tr => tr.querySelector('td').textContent.includes('Налог'));
                if (taxRow) {
                    taxRow.cells[0].textContent = `Налог (${calculated.tax_method || 'none'})`;
                    taxRow.cells[taxRow.cells.length - 1].textContent = (calculated.tax || 0).toFixed(2) + ' ₽';
                }

                const totalRow = Array.from(tbody.querySelectorAll('tr.font-bold')).find(tr => tr.querySelector('td').textContent.trim() === 'Итого');
                if (totalRow) totalRow.cells[totalRow.cells.length - 1].textContent = (calculated.total || 0).toFixed(2) + ' ₽';
            }

            // Применяем фильтр, если он активен
            const filterInput = document.getElementById('filterEquipment');
            if (filterInput && filterInput.value) {
                const filter = filterInput.value.toLowerCase();
                document.querySelectorAll('#estimateTableBody tr').forEach(row => {
                    const name = row.querySelector('td')?.textContent.toLowerCase();
                    row.style.display = name && name.includes(filter) ? '' : 'none';
                });
            }
        }

            function attachFilterHandler() {
                const filterInput = document.getElementById('filterEquipment');
                if (filterInput) {
                    filterInput.addEventListener('input', function() {
                        const filter = this.value.toLowerCase();
                        document.querySelectorAll('#estimateTableBody tr').forEach(row => {
                            const name = row.querySelector('td')?.textContent.toLowerCase();
                            row.style.display = name && name.includes(filter) ? '' : 'none';
                        });
                    });
                }
            }

            // Инициализация фильтра
            attachFilterHandler();
        /**
         * Обновляем только строки Подытог, Налог, Итого
         */
        function refreshSummary(calculated) {
            const tbody = document.getElementById('estimateTableBody');
            if (!tbody) return;

            // Подытог
            const subRow = Array.from(tbody.querySelectorAll('tr.font-bold')).find(
                tr => tr.firstElementChild.textContent.trim() === 'Подытог'
            );
            if (subRow) {
                const lastTd = subRow.cells[subRow.cells.length - 1];
                lastTd.textContent = (calculated.subtotal || 0).toFixed(2) + ' ₽';
            }

            // Налог
            const taxRow = Array.from(tbody.querySelectorAll('tr.font-bold')).find(
                tr => tr.firstElementChild.textContent.includes('Налог')
            );
            if (taxRow) {
                taxRow.firstElementChild.textContent = `Налог (${calculated.tax_method || 'none'})`;
                const lastTd = taxRow.cells[taxRow.cells.length - 1];
                lastTd.textContent = (calculated.tax || 0).toFixed(2) + ' ₽';
            }

            // Итого
            const totRow = Array.from(tbody.querySelectorAll('tr.font-bold')).find(
                tr => tr.firstElementChild.textContent.trim() === 'Итого'
            );
            if (totRow) {
                const lastTd = totRow.cells[totRow.cells.length - 1];
                lastTd.textContent = (calculated.total || 0).toFixed(2) + ' ₽';
            }
        }

        async function removeEquipment(equipmentId, estimateId) {
            if (!confirm('Удалить это оборудование из сметы?')) return;
            try {
                const response = await fetch(`/estimates/${estimateId}/remove-equipment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ equipment_id: equipmentId })
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'Ошибка удаления оборудования');
                // Удаляем строку
                const row = document.querySelector(`tr[data-equipment-id="${equipmentId}"]`);
                if (row) row.remove();
                // Обновляем таблицу
                refreshSummary(data.calculated);
                alert('Оборудование удалено из сметы');
            } catch (e) {
                console.error('removeEquipment error:', e);
                alert('Ошибка: ' + e.message);
            }
        }
    </script>

