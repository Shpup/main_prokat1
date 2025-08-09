<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Транспорт</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .collapsible { cursor: pointer; }
        .collapsible-content { display: none; }
        .category-tree .collapsible {
            padding-left: 1rem;
            transition: padding-left 0.2s ease;
        }
        .category-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            background-color: #f9fafb;
            margin-bottom: 0.25rem;
        }
        .category-item:hover {
            background-color: #f3f4f6;
        }
        .category-name {
            display: flex;
            align-items: center;
        }
        .category-name a {
            color: #3b82f6;
            text-decoration: none;
            margin-left: 0.5rem;
        }
        .category-name a:hover {
            text-decoration: underline;
        }
        .expand-toggle {
            font-size: 0.875rem;
            color: #6b7280;
            transition: transform 0.2s ease;
            cursor: pointer;
        }
        .add-button {
            color: #10b981;
            font-size: 0.875rem;
            margin-left: 0.5rem;
        }
        .add-button:hover {
            color: #059669;
        }
        .delete-button {
            font-size: 0.875rem;
            margin-left: 0.5rem;
        }
        .fuel-extra-fields { display: none; margin-top: 0.5rem; }
        .fuel-extra-fields.active { display: block; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-button { padding: 10px 20px; margin-right: 5px; cursor: pointer; background: #e5e7eb; border: none; border-radius: 5px; }
        .tab-button.active { background: #3b82f6; color: white; }
        .sortable { cursor: pointer; }
        .sortable:hover { color: #3b82f6; }
    </style>
</head>
<body class="antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Транспорт</h1>

        <!-- Поиск и сортировка -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <input type="text" id="search" name="search" placeholder="Поиск..." class="border-gray-300 rounded-md p-2" value="{{ request('search') }}">
                <button onclick="searchTable()" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 ml-2">Поиск</button>
            </div>
            <div>
                Сортировать по:
                <select id="sort_field" onchange="sortTable()">
                    <option value="id" {{ request('sort_field') == 'id' ? 'selected' : '' }}>ID</option>
                    <option value="brand" {{ request('sort_field') == 'brand' ? 'selected' : '' }}>Марка</option>
                    <option value="model" {{ request('sort_field') == 'model' ? 'selected' : '' }}>Модель</option>
                    <option value="license_plate" {{ request('sort_field') == 'license_plate' ? 'selected' : '' }}>Номер</option>
                    <option value="date_time" {{ request('sort_field') == 'date_time' ? 'selected' : '' }}>Дата</option>
                    <option value="distance" {{ request('sort_field') == 'distance' ? 'selected' : '' }}>Расстояние</option>
                    <option value="cost" {{ request('sort_field') == 'cost' ? 'selected' : '' }}>Стоимость</option>
                </select>
                <select id="sort_direction" onchange="sortTable()">
                    <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>По возрастанию</option>
                    <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>По убыванию</option>
                </select>
            </div>
        </div>

        <!-- Вкладки -->
        <div class="mb-6">
            <button class="tab-button active" onclick="openTab('vehicles')">Транспорт</button>
            <button class="tab-button" onclick="openTab('tripSheets')">Путеводные листы</button>
        </div>

        <!-- Таблица транспорта -->
        <div id="vehiclesTab" class="tab-content active">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-700"></h2>

                <button onclick="openCreate()" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                    Добавить транспорт
                </button>

            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('brand')">Марка</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('model')">Модель</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('year')">Год</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('license_plate')">Номер</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('status')">Статус</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('mileage')">Пробег (км)</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('fuel_type')">Тип топлива</th>
                            <th class="px-6 py-3 text-right text-sm font-medium text-gray-600">Действия</th>
                        </tr>
                        </thead>
                        <tbody id="vehiclesTableBody" class="divide-y divide-gray-200">
                        @forelse ($vehicles as $vehicle)
                            <tr class="hover:bg-gray-50" data-id="{{ $vehicle->id }}">
                                <td class="px-6 py-4">{{ $vehicle->brand ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $vehicle->model ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $vehicle->year ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $vehicle->license_plate ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $vehicle->status ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $vehicle->mileage ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $vehicle->fuel_type ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="openEdit({{ $vehicle->id }})" class="text-blue-600 hover:underline mr-4">Ред.</button>
                                    <button onclick="destroy({{ $vehicle->id }})" class="text-red-600 hover:underline">Удалить</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-600">Нет транспорта</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $vehicles->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

        <!-- Таблица путеводных листов -->
        <div id="tripSheetsTab" class="tab-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-700"></h2>

                <button onclick="openCreateTripSheet()" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                    Добавить путеводный лист
                </button>

            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('date_time')">Дата и время</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('address')">Площадка/Адрес</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('vehicle_id')">Транспорт</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('driver_id')">Водитель</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('distance')">Расстояние (км)</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('cost')">Стоимость (RUB)</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 sortable" onclick="sortTable('status')">Статус</th>
                            <th class="px-6 py-3 text-right text-sm font-medium text-gray-600">Действия</th>
                        </tr>
                        </thead>
                        <tbody id="tripSheetsTableBody" class="divide-y divide-gray-200">
                        @forelse ($tripSheets as $tripSheet)
                            <tr class="hover:bg-gray-50" data-id="{{ $tripSheet->id }}">
                                <td class="px-6 py-4">{{ $tripSheet->date_time }}</td>
                                <td class="px-6 py-4">{{ $tripSheet->location ? $tripSheet->location->name : $tripSheet->address }}</td>
                                <td class="px-6 py-4">{{ $tripSheet->vehicle ? $tripSheet->vehicle->brand . ' ' . $tripSheet->vehicle->model : '—' }}</td>
                                <td class="px-6 py-4">{{ $tripSheet->driver ? $tripSheet->driver->name : '—' }}</td>
                                <td class="px-6 py-4">{{ $tripSheet->distance ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $tripSheet->cost ?? '—' }}</td>
                                <td class="px-6 py-4">{{ ucfirst($tripSheet->status) ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="openEditTripSheet({{ $tripSheet->id }})" class="text-blue-600 hover:underline mr-4">Ред.</button>
                                    <button onclick="destroyTripSheet({{ $tripSheet->id }})" class="text-red-600 hover:underline">Удалить</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-600">Нет путеводных листов</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $tripSheets->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

        <!-- Модальное окно для транспорта -->
        <div id="createVehicleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg">
                <h2 id="modalTitle" class="text-xl font-semibold text-gray-800 mb-4">Добавить транспорт</h2>
                <form id="createVehicleForm" action="{{ route('vehicles.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="vehicle_id">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="brand" class="block text-sm font-medium text-gray-600">Марка</label>
                            <input type="text" name="brand" id="brand" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="model" class="block text-sm font-medium text-gray-600">Модель</label>
                            <input type="text" name="model" id="model" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="year" class="block text-sm font-medium text-gray-600">Год</label>
                            <input type="number" name="year" id="year" class="mt-1 block w-full border-gray-300 rounded-md" min="1900" max="{{ date('Y') }}">
                        </div>
                        <div class="mb-4">
                            <label for="license_plate" class="block text-sm font-medium text-gray-600">Номер</label>
                            <input type="text" name="license_plate" id="license_plate" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-600">Статус</label>
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="available">Доступен</option>
                                <option value="in_use">В использовании</option>
                                <option value="maintenance">На обслуживании</option>
                                <option value="out_of_service">Списан</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="mileage" class="block text-sm font-medium text-gray-600">Пробег (км)</label>
                            <input type="number" name="mileage" id="mileage" class="mt-1 block w-full border-gray-300 rounded-md" min="0">
                        </div>
                        <div class="mb-4">
                            <label for="fuel_type" class="block text-sm font-medium text-gray-600">Тип топлива</label>
                            <select name="fuel_type" id="fuel_type" class="mt-1 block w-full border-gray-300 rounded-md" onchange="toggleFuelFields()">
                                <option value="">—</option>
                                <option value="petrol">Бензин</option>
                                <option value="diesel">Дизель</option>
                                <option value="electric">Электро</option>
                                <option value="hybrid">Гибрид</option>
                            </select>
                        </div>
                        <div class="mb-4 col-span-2 fuel-extra-fields" id="petrol-fields">
                            <label for="fuel_grade" class="block text-sm font-medium text-gray-600">Тип бензина</label>
                            <select name="fuel_grade" id="fuel_grade" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="92">92</option>
                                <option value="95">95</option>
                                <option value="100">100</option>
                            </select>
                            <label for="fuel_consumption" class="block text-sm font-medium text-gray-600 mt-2">Расход (л/100 км)</label>
                            <input type="number" name="fuel_consumption" id="fuel_consumption" class="mt-1 block w-full border-gray-300 rounded-md" min="0" step="0.1">
                        </div>
                        <div class="mb-4 col-span-2 fuel-extra-fields" id="diesel-fields">
                            <label for="diesel_consumption" class="block text-sm font-medium text-gray-600">Расход (л/100 км)</label>
                            <input type="number" name="diesel_consumption" id="diesel_consumption" class="mt-1 block w-full border-gray-300 rounded-md" min="0" step="0.1">
                        </div>
                        <div class="mb-4 col-span-2 fuel-extra-fields" id="electric-fields">
                            <label for="battery_capacity" class="block text-sm font-medium text-gray-600">Мощность батареи (кВт·ч)</label>
                            <input type="number" name="battery_capacity" id="battery_capacity" class="mt-1 block w-full border-gray-300 rounded-md" min="0" step="0.1">
                            <label for="range" class="block text-sm font-medium text-gray-600 mt-2">Запас хода (км)</label>
                            <input type="number" name="range" id="range" class="mt-1 block w-full border-gray-300 rounded-md" min="0">
                        </div>
                        <div class="mb-4 col-span-2 fuel-extra-fields" id="hybrid-fields">
                            <label for="hybrid_consumption" class="block text-sm font-medium text-gray-600">Расход (л/100 км)</label>
                            <input type="number" name="hybrid_consumption" id="hybrid_consumption" class="mt-1 block w-full border-gray-300 rounded-md" min="0" step="0.1">
                            <label for="hybrid_range" class="block text-sm font-medium text-gray-600 mt-2">Запас хода (км)</label>
                            <input type="number" name="hybrid_range" id="hybrid_range" class="mt-1 block w-full border-gray-300 rounded-md" min="0">
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="comment" class="block text-sm font-medium text-gray-600">Комментарий</label>
                            <textarea name="comment" id="comment" class="mt-1 block w-full border-gray-300 rounded-md" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" onclick="closeModal('createVehicleModal')" class="mr-2 bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Отмена</button>
                        <button type="submit" id="submitVehicle" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Добавить</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Модальное окно для путеводного листа -->
        <div id="createTripSheetModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg">
                <h2 id="tripSheetModalTitle" class="text-xl font-semibold text-gray-800 mb-4">Добавить путеводный лист</h2>
                <form id="createTripSheetForm" action="{{ route('tripSheets.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="trip_sheet_id">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="date_time" class="block text-sm font-medium text-gray-600">Дата и время</label>
                            <input type="datetime-local" name="date_time" id="date_time" class="mt-1 block w-full border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label for="location_id" class="block text-sm font-medium text-gray-600">Площадка</label>
                            <select name="location_id" id="location_id" class="mt-1 block w-full border-gray-300 rounded-md" onchange="updateAddress()">
                                <option value="">—</option>
                                @foreach ($locations ?? [] as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-600">Адрес</label>
                            <input type="text" name="address" id="address" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="Введите адрес, если площадка не выбрана">
                        </div>
                        <div class="mb-4">
                            <label for="vehicle_id" class="block text-sm font-medium text-gray-600">Транспорт</label>
                            <select name="vehicle_id" id="vehicle_id" class="mt-1 block w-full border-gray-300 rounded-md" onchange="calculateTripCost()">
                                <option value="">—</option>
                                @foreach ($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->license_plate }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="driver_id" class="block text-sm font-medium text-gray-600">Водитель</label>
                            <select name="driver_id" id="driver_id" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="">—</option>
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="distance" class="block text-sm font-medium text-gray-600">Расстояние (км)</label>
                            <input type="number" name="distance" id="distance" class="mt-1 block w-full border-gray-300 rounded-md" min="0" step="0.1" onchange="calculateTripCost()" required>
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="status" class="block text-sm font-medium text-gray-600">Статус</label>
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md" required>
                                <option value="in_progress">В работе</option>
                                <option value="completed">Выполнено</option>
                                <option value="canceled">Отменено</option>
                            </select>
                        </div>
                        <div class="mb-4 col-span-2">
                            <label class="block text-sm font-medium text-gray-600">Стоимость (RUB)</label>
                            <input type="text" id="cost" class="mt-1 block w-full border-gray-300 rounded-md" readonly>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" onclick="closeModal('createTripSheetModal')" class="mr-2 bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Отмена</button>
                        <button type="submit" id="submitTripSheet" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Добавить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    let vehicles = @json($vehicles->items());
    let tripSheets = @json($tripSheets->items());
    const createUrl = '{{ route('vehicles.store') }}';
    const updateUrlTemplate = '{{ route('vehicles.update', ['vehicle' => ':id']) }}'.replace(':id', 'VEHICLE_ID');
    const destroyUrlTemplate = '{{ route('vehicles.destroy', ['vehicle' => 'VEHICLE_ID']) }}';
    const tripSheetUrl = '{{ route('tripSheets.store') }}';
    const tripSheetUpdateUrlTemplate = '{{ route('tripSheets.update', ['tripSheet' => 'TRIP_SHEET_ID']) }}';
    const tripSheetDestroyUrlTemplate = '{{ route('tripSheets.destroy', ['tripSheet' => 'TRIP_SHEET_ID']) }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const fuelPrices = {
        '92': 56.62,
        '95': 61.87,
        '100': 84.10,
        'diesel': 71.36,
        'electric': 5.86
    };

    function openTab(tabName) {
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.querySelector(`.tab-button[onclick="openTab('${tabName}')"]`).classList.add('active');
        document.getElementById(`${tabName}Tab`).classList.add('active');
    }

    function openCreate() {
        document.getElementById('createVehicleForm').reset();
        document.getElementById('createVehicleModal').classList.remove('hidden');
        document.getElementById('modalTitle').textContent = 'Добавить транспорт';
        document.getElementById('vehicle_id').value = '';
        document.getElementById('submitVehicle').textContent = 'Добавить';
        document.getElementById('createVehicleForm').action = createUrl;
        toggleFuelFields();
    }

    function openEdit(vehicleId) {
        const vehicle = vehicles.find(v => v.id === vehicleId);
        if (!vehicle) return alert('Транспорт не найден');
        document.getElementById('createVehicleModal').classList.remove('hidden');
        document.getElementById('modalTitle').textContent = 'Редактировать транспорт';
        document.getElementById('vehicle_id').value = vehicle.id;
        document.getElementById('brand').value = vehicle.brand || '—';
        document.getElementById('model').value = vehicle.model || '—';
        document.getElementById('year').value = vehicle.year || 0;
        document.getElementById('license_plate').value = vehicle.license_plate || '—';
        document.getElementById('status').value = vehicle.status || 'available';
        document.getElementById('mileage').value = vehicle.mileage || 0;
        document.getElementById('fuel_type').value = vehicle.fuel_type || '';
        document.getElementById('fuel_grade').value = vehicle.fuel_grade || '95';
        document.getElementById('fuel_consumption').value = vehicle.fuel_consumption || 0;
        document.getElementById('diesel_consumption').value = vehicle.diesel_consumption || 0;
        document.getElementById('battery_capacity').value = vehicle.battery_capacity || 0;
        document.getElementById('range').value = vehicle.range || 0;
        document.getElementById('hybrid_consumption').value = vehicle.hybrid_consumption || 0;
        document.getElementById('hybrid_range').value = vehicle.hybrid_range || 0;
        document.getElementById('comment').value = vehicle.comment || '';
        document.getElementById('submitVehicle').textContent = 'Сохранить';
        document.getElementById('createVehicleForm').action = updateUrlTemplate.replace('VEHICLE_ID', vehicle.id);
        let methodInput = document.querySelector('#createVehicleForm input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            document.getElementById('createVehicleForm').appendChild(methodInput);
        }
        methodInput.value = 'PUT';
        toggleFuelFields();
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function toggleFuelFields() {
        const fuelType = document.getElementById('fuel_type').value;
        const fields = {
            'petrol': 'petrol-fields',
            'diesel': 'diesel-fields',
            'electric': 'electric-fields',
            'hybrid': 'hybrid-fields'
        };
        for (let key in fields) {
            document.getElementById(fields[key]).classList.remove('active');
        }
        if (fields[fuelType]) {
            document.getElementById(fields[fuelType]).classList.add('active');
        }
    }

    function destroy(vehicleId) {
        if (!confirm('Уверены?')) return;
        const url = destroyUrlTemplate.replace('VEHICLE_ID', vehicleId);
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                vehicles = vehicles.filter(v => v.id !== vehicleId);
                updateTable();
            } else {
                alert('Ошибка удаления');
            }
        }).catch(() => alert('Ошибка сети'));
    }

    function updateTable() {
        const tbody = document.getElementById('vehiclesTableBody');
        tbody.innerHTML = vehicles.map(vehicle => `
                <tr class="hover:bg-gray-50" data-id="${vehicle.id}">
                    <td class="px-6 py-4">${vehicle.brand || '—'}</td>
                    <td class="px-6 py-4">${vehicle.model || '—'}</td>
                    <td class="px-6 py-4">${vehicle.year || '—'}</td>
                    <td class="px-6 py-4">${vehicle.license_plate || '—'}</td>
                    <td class="px-6 py-4">${vehicle.status || '—'}</td>
                    <td class="px-6 py-4">${vehicle.mileage || '—'}</td>
                    <td class="px-6 py-4">${vehicle.fuel_type || '—'}</td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="openEdit(${vehicle.id})" class="text-blue-600 hover:underline mr-4">Ред.</button>
                        <button onclick="destroy(${vehicle.id})" class="text-red-600 hover:underline">Удалить</button>
                    </td>
                </tr>
            `).join('') || '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-600">Нет транспорта</td></tr>';
    }

    function openCreateTripSheet() {
        document.getElementById('createTripSheetForm').reset();
        document.getElementById('createTripSheetModal').classList.remove('hidden');
        document.getElementById('tripSheetModalTitle').textContent = 'Добавить путеводный лист';
        document.getElementById('trip_sheet_id').value = '';
        document.getElementById('submitTripSheet').textContent = 'Добавить';
        document.getElementById('createTripSheetForm').action = tripSheetUrl;
        updateAddress();
        calculateTripCost();
    }

    function openEditTripSheet(tripSheetId) {
        const tripSheet = tripSheets.find(ts => ts.id === tripSheetId);
        if (!tripSheet) return alert('Путеводный лист не найден');
        document.getElementById('createTripSheetModal').classList.remove('hidden');
        document.getElementById('tripSheetModalTitle').textContent = 'Редактировать путеводный лист';
        document.getElementById('trip_sheet_id').value = tripSheet.id;
        document.getElementById('date_time').value = tripSheet.date_time ? tripSheet.date_time.replace(' ', 'T') : '';
        document.getElementById('location_id').value = tripSheet.location_id || '';
        document.getElementById('address').value = tripSheet.address || '';
        document.getElementById('vehicle_id').value = tripSheet.vehicle_id || '';
        document.getElementById('driver_id').value = tripSheet.driver_id || '';
        document.getElementById('distance').value = tripSheet.distance || '';
        document.getElementById('status').value = tripSheet.status || 'in_progress';
        document.getElementById('cost').value = tripSheet.cost || '';
        document.getElementById('submitTripSheet').textContent = 'Сохранить';
        document.getElementById('createTripSheetForm').action = tripSheetUpdateUrlTemplate.replace('TRIP_SHEET_ID', tripSheet.id);
        let methodInput = document.querySelector('#createTripSheetForm input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            document.getElementById('createTripSheetForm').appendChild(methodInput);
        }
        methodInput.value = 'PUT';
        updateAddress();
        calculateTripCost();
    }

    function updateAddress() {
        const locationId = document.getElementById('location_id').value;
        const location = @json($locations ?? [])?.find(l => l.id == locationId);
        document.getElementById('address').value = location?.address || '';
        calculateTripCost();
    }

    function calculateTripCost() {
        const vehicleId = document.getElementById('vehicle_id').value;
        const distance = parseFloat(document.getElementById('distance').value) || 0;
        if (!vehicleId || !distance) {
            document.getElementById('cost').value = '';
            return;
        }
        const vehicle = vehicles.find(v => v.id == vehicleId);
        if (!vehicle) return;

        const consumption = vehicle.fuel_consumption || vehicle.diesel_consumption || vehicle.hybrid_consumption || 0;
        const fuelPrice = fuelPrices[vehicle.fuel_grade || vehicle.fuel_type || '95'] || 61.87;
        const cost = (distance / 100) * consumption * fuelPrice;
        document.getElementById('cost').value = cost.toFixed(2);
    }

    document.getElementById('createVehicleForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('_token', csrfToken);
        if (document.getElementById('vehicle_id').value) {
            formData.append('_method', 'PUT');
        }

        const url = this.action;
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            const data = await response.json();
            if (data.id) {
                let vehicleIndex = vehicles.findIndex(v => v.id === data.id);
                if (vehicleIndex !== -1) {
                    vehicles[vehicleIndex] = data;
                } else {
                    vehicles.unshift(data);
                }
                updateTable();
                closeModal('createVehicleModal');
            }
        } else {
            const error = await response.json();
            alert('Ошибка сохранения: ' + (error.message || 'Неизвестная ошибка'));
        }
    });

    document.getElementById('createTripSheetForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('_token', csrfToken);
        if (document.getElementById('trip_sheet_id').value) {
            formData.append('_method', 'PUT');
        }

        const address = formData.get('address');
        if (!formData.get('location_id') && !address) {
            alert('Укажите адрес, если площадка не выбрана!');
            return;
        }

        const url = this.action;
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            const data = await response.json();
            if (data.id) {
                let tripSheetIndex = tripSheets.findIndex(ts => ts.id === data.id);
                if (tripSheetIndex !== -1) {
                    tripSheets[tripSheetIndex] = data;
                } else {
                    tripSheets.unshift(data);
                }
                updateTripSheetTable();
                closeModal('createTripSheetModal');
            }
        } else {
            const error = await response.json();
            alert('Ошибка сохранения: ' + (error.message || 'Неизвестная ошибка'));
        }
    });

    function destroyTripSheet(tripSheetId) {
        if (!confirm('Уверены?')) return;
        const url = tripSheetDestroyUrlTemplate.replace('TRIP_SHEET_ID', tripSheetId);
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                tripSheets = tripSheets.filter(ts => ts.id !== tripSheetId);
                updateTripSheetTable();
            } else {
                alert('Ошибка удаления');
            }
        }).catch(() => alert('Ошибка сети'));
    }

    function updateTripSheetTable() {
        const tbody = document.getElementById('tripSheetsTableBody');
        tbody.innerHTML = tripSheets.map(tripSheet => `
                <tr class="hover:bg-gray-50" data-id="${tripSheet.id}">
                    <td class="px-6 py-4">${tripSheet.date_time}</td>
                    <td class="px-6 py-4">${tripSheet.location ? tripSheet.location.name : tripSheet.address}</td>
                    <td class="px-6 py-4">${tripSheet.vehicle ? tripSheet.vehicle.brand + ' ' + tripSheet.vehicle.model : '—'}</td>
                    <td class="px-6 py-4">${tripSheet.driver ? tripSheet.driver.name : '—'}</td>
                    <td class="px-6 py-4">${tripSheet.distance ?? '—'}</td>
                    <td class="px-6 py-4">${tripSheet.cost ?? '—'}</td>
                    <td class="px-6 py-4">${ucfirst(tripSheet.status) ?? '—'}</td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="openEditTripSheet(${tripSheet.id})" class="text-blue-600 hover:underline mr-4">Ред.</button>
                        <button onclick="destroyTripSheet(${tripSheet.id})" class="text-red-600 hover:underline">Удалить</button>
                    </td>
                </tr>
            `).join('') || '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-600">Нет путеводных листов</td></tr>';
    }

    function sortTable(field = null) {
        if (field) {
            document.getElementById('sort_field').value = field;
        }
        const sortField = document.getElementById('sort_field').value;
        const sortDirection = document.getElementById('sort_direction').value;
        window.location.href = `{{ url()->current() }}?sort_field=${sortField}&sort_direction=${sortDirection}&search=${encodeURIComponent(document.getElementById('search').value)}`;
    }

    function searchTable() {
        const search = document.getElementById('search').value;
        const sortField = document.getElementById('sort_field').value;
        const sortDirection = document.getElementById('sort_direction').value;
        window.location.href = `{{ url()->current() }}?search=${encodeURIComponent(search)}&sort_field=${sortField}&sort_direction=${sortDirection}`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateTable();
        updateTripSheetTable();
        toggleFuelFields();
    });
</script>
</body>
</html>
