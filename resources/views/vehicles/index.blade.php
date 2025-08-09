<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Транспорт</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .table-scroll {
            overflow-x: auto;
            max-width: 100%;
        }
        #vehiclesTable, #tripSheetsTable {
            table-layout: fixed;
            width: 100%;
        }
        #vehiclesTable th, #tripSheetsTable th,
        #vehiclesTable td, #tripSheetsTable td {
            min-width: 0;
            word-wrap: break-word;
        }
        #vehiclesTable th, #tripSheetsTable th {
            padding: 0.75rem 1rem; /* py-3 px-4 */
            text-align: center;
            font-size: 0.75rem;    /* text-xs */
            font-weight: 500;      /* font-medium */
            color: #6b7280;        /* text-gray-500 */
            text-transform: uppercase;
            letter-spacing: 0.05em; /* tracking-wider */
        }
        #vehiclesTable td, #tripSheetsTable td {
            padding: 1rem 1rem;    /* py-4 px-4 */
            white-space: nowrap;
            text-align: center;
            font-size: 0.875rem;   /* text-sm */
            color: #111827;        /* text-gray-900 */
        }
        #toast {
            z-index: 9999;
        }
        [x-cloak] {
            display: none !important;
        }
        .fuel-extra-fields { display: none; margin-top: 0.5rem; }
        .fuel-extra-fields.active { display: block; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        @media (max-width: 640px) {
            #vehiclesTable th:nth-child(n+4):nth-child(-n+7),
            #vehiclesTable td:nth-child(n+4):nth-child(-n+7) {
                display: none;
            }
            #tripSheetsTable th:nth-child(n+4):nth-child(-n+7),
            #tripSheetsTable td:nth-child(n+4):nth-child(-n+7) {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="bg-white shadow rounded-lg" x-data="transportManager()" x-init="init()">
    @include('layouts.navigation')

    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
            <div class="px-4 sm:px-6 py-4">
                <button
                        class="tab-button px-4 py-2 text-sm font-medium rounded-md focus:outline-none"
                        :class="{ 'bg-blue-500 text-white': activeTab === 'vehicles', 'bg-gray-200 text-gray-700': activeTab !== 'vehicles' }"
                        @click="openTab('vehicles'); console.log('Switched to vehicles, activeTab:', activeTab)"
                >
                    Транспорт
                </button>
                <button
                        class="tab-button px-4 py-2 text-sm font-medium rounded-md focus:outline-none"
                        :class="{ 'bg-blue-500 text-white': activeTab === 'tripSheets', 'bg-gray-200 text-gray-700': activeTab !== 'tripSheets' }"
                        @click="openTab('tripSheets'); console.log('Switched to tripSheets, activeTab:', activeTab)"
                >
                    Путеводные листы
                </button>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                <input type="text" x-model.debounce.500ms="search" @input="fetchData()"
                       placeholder="Поиск по марке, модели или номеру..."
                       class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm">
                <button @click="openVehicleModal()" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md hover:bg-blue-600">
                    Добавить транспорт
                </button>
                <button @click="openTripSheetModal()" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md hover:bg-blue-600">
                    Добавить путеводный лист
                </button>
            </div>
        </div>
    </div>

    <!-- Вкладки -->


    <!-- Таблица транспорта -->
    <div id="vehiclesTab" class="tab-content active px-4 sm:px-6 py-4 sm:py-6">
        <div class="table-scroll">
            <table class="divide-y divide-gray-200 w-full" id="vehiclesTable">
                <thead class="bg-gray-50">
                <tr>
                    <th class="w-1/8 min-w-[150px] cursor-pointer" @click="sortBy('brand')">Марка</th>
                    <th class="w-1/8 min-w-[120px] cursor-pointer" @click="sortBy('model')">Модель</th>
                    <th class="w-1/8 min-w-[100px] cursor-pointer" @click="sortBy('year')">Год</th>
                    <th class="w-1/8 min-w-[120px] cursor-pointer" @click="sortBy('license_plate')">Номер</th>
                    <th class="w-1/8 min-w-[100px] cursor-pointer" @click="sortBy('status')">Статус</th>
                    <th class="w-1/8 min-w-[100px] cursor-pointer" @click="sortBy('mileage')">Пробег (км)</th>
                    <th class="w-1/8 min-w-[100px] cursor-pointer" @click="sortBy('fuel_type')">Тип топлива</th>
                    <th class="w-1/8 min-w-[120px]">Действия</th>
                </tr>
                </thead>
                <tbody id="vehicles-table" class="bg-white divide-y divide-gray-200">
                @include('vehicles.partials.table')
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $vehicles->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Таблица путеводных листов -->
    <div id="tripSheetsTab" class="tab-content px-4 sm:px-6 py-4 sm:py-6">
        <div class="table-scroll">
            <table class="divide-y divide-gray-200 w-full" id="tripSheetsTable">
                <thead class="bg-gray-50">
                <tr>
                    <th class="w-1/8 min-w-[150px] cursor-pointer" @click="sortBy('date_time')">Дата и время</th>
                    <th class="w-1/8 min-w-[150px] cursor-pointer" @click="sortBy('address')">Площадка/Адрес</th>
                    <th class="w-1/8 min-w-[120px] cursor-pointer" @click="sortBy('vehicle_id')">Транспорт</th>
                    <th class="w-1/8 min-w-[120px] cursor-pointer" @click="sortBy('driver_id')">Водитель</th>
                    <th class="w-1/8 min-w-[100px] cursor-pointer" @click="sortBy('distance')">Расстояние (км)</th>
                    <th class="w-1/8 min-w-[100px] cursor-pointer" @click="sortBy('cost')">Стоимость (RUB)</th>
                    <th class="w-1/8 min-w-[100px] cursor-pointer" @click="sortBy('status')">Статус</th>
                    <th class="w-1/8 min-w-[120px]">Действия</th>
                </tr>
                </thead>
                <tbody id="trip-sheets-table" class="bg-white divide-y divide-gray-200">
                @include('trip-sheets.partials.table')
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $tripSheets->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Модальное окно для транспорта -->
    <div
            x-show="isVehicleModalOpen"
            x-cloak
            class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeVehicleModal()"
            @keydown.window.escape="closeVehicleModal()"
            x-transition.opacity
    >
        <div
                class="bg-white rounded-lg shadow-lg border w-11/12 max-w-md max-h-[90vh] overflow-y-auto"
                x-transition.scale.origin.top.duration.200ms
                x-transition.opacity.duration.200ms
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" x-text="vehicleId ? 'Редактировать транспорт' : 'Добавить транспорт'"></h3>
                    <button @click="closeVehicleModal()" class="text-gray-400 hover:text-gray-600" type="button" aria-label="Закрыть">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form :action="vehicleId ? '/vehicles/' + vehicleId : '/vehicles'" method="POST" @submit="submitVehicleForm($event)">
                    @csrf
                    <input
                            type="hidden"
                            :name="vehicleId ? '_method' : null"
                            :value="vehicleId ? 'PUT' : null"
                    >
                    <input type="hidden" name="id" x-model="vehicleId">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Марка</label>
                            <input type="text" name="brand" x-model="vehicleForm.brand" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Модель</label>
                            <input type="text" name="model" x-model="vehicleForm.model" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Год</label>
                            <input type="number" name="year" x-model="vehicleForm.year" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Номер</label>
                            <input type="text" name="license_plate" x-model="vehicleForm.license_plate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                            <select name="status" x-model="vehicleForm.status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="available">Доступен</option>
                                <option value="in_use">В использовании</option>
                                <option value="maintenance">На обслуживании</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Пробег (км)</label>
                            <input type="number" name="mileage" x-model="vehicleForm.mileage" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Тип топлива</label>
                            <select name="fuel_type" x-model="vehicleForm.fuel_type" @change="toggleFuelFields" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Выберите тип</option>
                                <option value="petrol">Бензин</option>
                                <option value="diesel">Дизель</option>
                                <option value="electric">Электрический</option>
                                <option value="hybrid">Гибрид</option>
                            </select>
                        </div>
                    </div>
                    <div id="petrol-fields" class="fuel-extra-fields" x-show="vehicleForm.fuel_type === 'petrol'">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Марка топлива</label>
                            <select name="fuel_grade" x-model="vehicleForm.fuel_grade" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="92">92</option>
                                <option value="95">95</option>
                                <option value="98">98</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Расход топлива (л/100 км)</label>
                            <input type="number" name="fuel_consumption" x-model="vehicleForm.fuel_consumption" step="0.1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div id="diesel-fields" class="fuel-extra-fields" x-show="vehicleForm.fuel_type === 'diesel'">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Расход дизеля (л/100 км)</label>
                            <input type="number" name="diesel_consumption" x-model="vehicleForm.diesel_consumption" step="0.1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div id="electric-fields" class="fuel-extra-fields" x-show="vehicleForm.fuel_type === 'electric'">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Емкость батареи (кВт·ч)</label>
                            <input type="number" name="battery_capacity" x-model="vehicleForm.battery_capacity" step="0.1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Запас хода (км)</label>
                            <input type="number" name="range" x-model="vehicleForm.range" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div id="hybrid-fields" class="fuel-extra-fields" x-show="vehicleForm.fuel_type === 'hybrid'">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Расход топлива (л/100 км)</label>
                            <input type="number" name="hybrid_consumption" x-model="vehicleForm.hybrid_consumption" step="0.1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Запас хода (км)</label>
                            <input type="number" name="hybrid_range" x-model="vehicleForm.hybrid_range" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Комментарий</label>
                        <textarea name="comment" x-model="vehicleForm.comment" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" rows="3"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="closeVehicleModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                            Отмена
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md hover:bg-blue-600">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно для путеводных листов -->
    <div
            x-show="isTripSheetModalOpen"
            x-cloak
            class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeTripSheetModal()"
            @keydown.window.escape="closeTripSheetModal()"
            x-transition.opacity
    >
        <div
                class="bg-white rounded-lg shadow-lg border w-11/12 max-w-md max-h-[90vh] overflow-y-auto"
                x-transition.scale.origin.top.duration.200ms
                x-transition.opacity.duration.200ms
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" x-text="tripSheetId ? 'Редактировать путеводный лист' : 'Добавить путеводный лист'"></h3>
                    <button @click="closeTripSheetModal()" class="text-gray-400 hover:text-gray-600" type="button" aria-label="Закрыть">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form :action="tripSheetId ? '/trip-sheets/' + tripSheetId : '/trip-sheets'" method="POST" @submit="submitTripSheetForm($event)">
                    @csrf
                    <input
                            type="hidden"
                            :name="tripSheetId ? '_method' : null"
                            :value="tripSheetId ? 'PUT' : null"
                    >
                    <input type="hidden" name="id" x-model="tripSheetId">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Дата и время</label>
                        <input type="datetime-local" name="date_time" x-model="tripSheetForm.date_time" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Площадка</label>
                        <select name="location_id" x-model="tripSheetForm.location_id" @change="updateAddress" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Выберите площадку</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Адрес</label>
                        <input type="text" name="address" x-model="tripSheetForm.address" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Транспорт</label>
                        <select name="vehicle_id" x-model="tripSheetForm.vehicle_id" @change="calculateTripCost" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Выберите транспорт</option>
                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->brand }} {{ $vehicle->model }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Водитель</label>
                        <select name="driver_id" x-model="tripSheetForm.driver_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Без водителя</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Расстояние (км)</label>
                        <input type="number" name="distance" x-model="tripSheetForm.distance" @input="calculateTripCost" step="0.1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Стоимость (RUB)</label>
                        <input type="number" name="cost" x-model="tripSheetForm.cost" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" readonly>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                        <select name="status" x-model="tripSheetForm.status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="in_progress">В процессе</option>
                            <option value="completed">Завершено</option>
                            <option value="cancelled">Отменено</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="closeTripSheetModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                            Отмена
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md hover:bg-blue-600">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast уведомления -->
    <div id="toast" class="fixed bottom-4 right-4 bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-y-full transition-transform duration-300">
        <div class="flex items-center">
            <span id="toastMessage"></span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        console.log('Alpine.js инициализирован');
    });

    function transportManager() {
        return {
            isVehicleModalOpen: false,
            isTripSheetModalOpen: false,
            vehicleId: null,
            tripSheetId: null,
            search: '',
            sort: 'brand',
            direction: 'asc',
            activeTab: 'vehicles',
            vehicleForm: {
                brand: '',
                model: '',
                year: 0,
                license_plate: '',
                status: 'available',
                mileage: 0,
                fuel_type: '',
                fuel_grade: '95',
                fuel_consumption: 0,
                diesel_consumption: 0,
                battery_capacity: 0,
                range: 0,
                hybrid_consumption: 0,
                hybrid_range: 0,
                comment: '',
            },
            tripSheetForm: {
                date_time: '',
                location_id: '',
                address: '',
                vehicle_id: '',
                driver_id: '',
                distance: 0,
                cost: 0,
                status: 'in_progress',
            },
            locations: @json($locations ?? []),
            vehicles: @json($vehicles->items() ?? []),
            fuelPrices: {
                '92': 58.50,
                '95': 61.87,
                '98': 65.30,
                'diesel': 62.10,
            },
            init() {
                this.isVehicleModalOpen = false;
                this.isTripSheetModalOpen = false;
                this.openTab(this.activeTab);
                console.log('Инициализация transportManager, activeTab:', this.activeTab);
            },
            openTab(tab) {
                this.activeTab = tab;
                document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
                document.getElementById(tab + 'Tab').classList.add('active');
                document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('active'));
                document.querySelector(`.tab-button[onclick*="${tab}"]`)?.classList.add('active');
                this.fetchData();
            },
            openVehicleModal(vehicle = null) {
                this.isVehicleModalOpen = true;
                if (vehicle) {
                    this.vehicleId = vehicle.id;
                    this.vehicleForm = { ...vehicle };
                } else {
                    this.vehicleId = null;
                    this.vehicleForm = {
                        brand: '',
                        model: '',
                        year: 0,
                        license_plate: '',
                        status: 'available',
                        mileage: 0,
                        fuel_type: '',
                        fuel_grade: '95',
                        fuel_consumption: 0,
                        diesel_consumption: 0,
                        battery_capacity: 0,
                        range: 0,
                        hybrid_consumption: 0,
                        hybrid_range: 0,
                        comment: '',
                    };
                }
            },
            closeVehicleModal() {
                this.isVehicleModalOpen = false;
            },
            openTripSheetModal(tripSheet = null) {
                this.isTripSheetModalOpen = true;
                if (tripSheet) {
                    this.tripSheetId = tripSheet.id;
                    this.tripSheetForm = {
                        ...tripSheet,
                        date_time: tripSheet.date_time ? tripSheet.date_time.replace(' ', 'T') : '',
                        site_id: tripSheet.site_id || '',
                        address: tripSheet.address || '',
                        vehicle_id: tripSheet.vehicle_id || '',
                        driver_id: tripSheet.driver_id || '', // Allow empty driver_id
                        distance: tripSheet.distance || 0,
                        cost: tripSheet.cost || 0,
                        status: tripSheet.status || 'in_progress',
                    };
                    this.updateAddress();
                    this.calculateTripCost();
                } else {
                    this.tripSheetId = null;
                    this.tripSheetForm = {
                        date_time: '',
                        site_id: '',
                        address: '',
                        vehicle_id: '',
                        driver_id: '', // Allow empty driver_id
                        distance: 0,
                        cost: 0,
                        status: 'in_progress',
                    };
                }
            },
            closeTripSheetModal() {
                this.isTripSheetModalOpen = false;
            },
            toggleFuelFields() {
                // Поля автоматически показываются через x-show в шаблоне
            },
            updateAddress() {
                const location = this.locations.find(l => l.id == this.tripSheetForm.location_id);
                this.tripSheetForm.address = location?.address || '';
                this.calculateTripCost();
            },
            calculateTripCost() {
                const vehicleId = this.tripSheetForm.vehicle_id;
                const distance = parseFloat(this.tripSheetForm.distance) || 0;
                if (!vehicleId) {
                    this.tripSheetForm.cost = '0.00';
                    return;
                }
                const vehicle = this.vehicles.find(v => v.id == vehicleId);
                if (!vehicle) {
                    this.tripSheetForm.cost = '0.00';
                    return;
                }
                const consumption = vehicle.fuel_consumption || vehicle.diesel_consumption || vehicle.hybrid_consumption || 0;
                const fuelPrice = this.fuelPrices[vehicle.fuel_grade || vehicle.fuel_type || '95'] || 61.87;
                const cost = (distance / 100) * consumption * fuelPrice;
                this.tripSheetForm.cost = cost.toFixed(2);
            },
            async submitVehicleForm(event) {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                if (response.ok) {
                    this.showToast(this.vehicleId ? 'Транспорт обновлен' : 'Транспорт добавлен');
                    this.closeVehicleModal();
                    this.fetchData();
                } else {
                    const error = await response.json();
                    this.showToast('Ошибка: ' + (error.message || 'Неизвестная ошибка'));
                }
            },
            async submitTripSheetForm(event) {
                event.preventDefault();
                if (!this.tripSheetForm.site_id && !this.tripSheetForm.address) {
                    this.showToast('Укажите адрес или выберите площадку!');
                    return;
                }
                const form = event.target;
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                if (response.ok) {
                    this.showToast(this.tripSheetId ? 'Путеводный лист обновлен' : 'Путеводный лист добавлен');
                    this.closeTripSheetModal();
                    this.fetchData();
                } else {
                    const error = await response.json();
                    this.showToast('Ошибка: ' + (error.message || 'Неизвестная ошибка'));
                }
            },
            async deleteVehicle(id) {
                if (!confirm('Удалить транспорт?')) return;
                const response = await fetch(`/vehicles/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                });
                if (response.ok) {
                    this.showToast('Транспорт удален');
                    this.fetchData();
                } else {
                    this.showToast('Ошибка удаления');
                }
            },
            async deleteTripSheet(id) {
                if (!confirm('Удалить путеводный лист?')) return;
                const response = await fetch(`/trip-sheets/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                });
                if (response.ok) {
                    this.showToast('Путеводный лист удален');
                    this.fetchData();
                } else {
                    this.showToast('Ошибка удаления');
                }
            },
            async fetchData() {
                const response = await fetch(`/vehicles?search=${encodeURIComponent(this.search)}&sort=${this.sort}&direction=${this.direction}&tab=${this.activeTab}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (response.ok) {
                    const data = await response.json();
                    const tableBody = document.getElementById(this.activeTab === 'vehicles' ? 'vehicles-table' : 'trip-sheets-table');
                    tableBody.innerHTML = data.view || '<tr><td colspan="8">Нет данных</td></tr>';
                    this.vehicles = data.vehicles || [];
                } else {
                    this.showToast('Ошибка при загрузке данных');
                }
            },
            sortBy(column) {
                if (this.sort === column) {
                    this.direction = this.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sort = column;
                    this.direction = 'asc';
                }
                this.fetchData();
            },
            showToast(message) {
                const toast = document.getElementById('toast');
                const toastMessage = document.getElementById('toastMessage');
                toastMessage.textContent = message;
                toast.classList.remove('translate-y-full');
                setTimeout(() => {
                    toast.classList.add('translate-y-full');
                }, 2000);
            },
        };
    }
</script>
</body>
</html>
