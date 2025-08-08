<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Клиенты</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Важно: Alpine подключается через Vite (в app.js). CDN убирать, чтобы не было дубля. -->

    <style>
        .table-scroll {
            overflow-x: auto;
            max-width: 100%;
        }
        #clientsTable {
            table-layout: fixed;
            width: 100%;
        }
        #clientsTable th,
        #clientsTable td {
            min-width: 0;
            word-wrap: break-word;
        }
        /* @apply в <style> не работает. Заменяем на обычный CSS */
        #clientsTable th {
            padding: 0.75rem 1rem; /* py-3 px-4 */
            text-align: center;
            font-size: 0.75rem;    /* text-xs */
            font-weight: 500;      /* font-medium */
            color: #6b7280;        /* text-gray-500 */
            text-transform: uppercase;
            letter-spacing: 0.05em; /* tracking-wider */
        }
        #clientsTable td {
            padding: 1rem 1rem;    /* py-4 px-4 */
            white-space: nowrap;
            text-align: center;
            font-size: 0.875rem;   /* text-sm */
            color: #111827;        /* text-gray-900 */
        }
        #toast {
            z-index: 9999;
        }
        /* Правильное скрытие до инициализации Alpine */
        [x-cloak] {
            display: none !important;
        }
        @media (max-width: 640px) {
            #clientsTable th:nth-child(n+4):nth-child(-n+7),
            #clientsTable td:nth-child(n+4):nth-child(-n+7) {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="bg-white shadow rounded-lg" x-data="clientManager()" x-init="init()">
    @include('layouts.navigation')

    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
            <h1 class="text-lg sm:text-xl font-medium text-gray-900">Каталог клиентов</h1>
            <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                <input type="text" x-model.debounce.500ms="search" @input="fetchClients()"
                       placeholder="Поиск по имени, телефону или email..."
                       class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm">
                <button @click="openModal()" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md hover:bg-blue-600">
                    Добавить клиента
                </button>
            </div>
        </div>
    </div>

    <div class="px-4 sm:px-6 py-4 sm:py-6">
        <div class="table-scroll">
            <table class="divide-y divide-gray-200 w-full" id="clientsTable">
                <thead class="bg-gray-50">
                <tr>
                    <th class="w-1/6 min-w-[150px] cursor-pointer" @click="sortBy('name')">Имя</th>
                    <th class="w-1/6 min-w-[120px] cursor-pointer" @click="sortBy('phone')">Телефон</th>
                    <th class="w-1/6 min-w-[150px] cursor-pointer" @click="sortBy('email')">Email</th>
                    <th class="w-1/6 min-w-[120px] cursor-pointer" @click="sortBy('discount_equipment')">Скидка (оборуд.)</th>
                    <th class="w-1/6 min-w-[120px] cursor-pointer" @click="sortBy('discount_services')">Скидка (услуги)</th>
                    <th class="w-1/6 min-w-[120px] cursor-pointer" @click="sortBy('discount_materials')">Скидка (материалы)</th>
                    <th class="w-1/6 min-w-[120px] cursor-pointer" @click="sortBy('blacklisted')">Черный список</th>
                    <th class="w-1/6 min-w-[120px]">Действия</th>
                </tr>
                </thead>
                <tbody id="clients-table" class="bg-white divide-y divide-gray-200">
                @include('clients.partials.table')
                </tbody>
            </table>
        </div>
    </div>

    <!-- Модальное окно -->
    <div
        x-show="isOpen"
        x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
        @click.self="closeModal()"
        @keydown.window.escape="closeModal()"
        x-transition.opacity
    >
        <div
            class="bg-white rounded-lg shadow-lg border w-11/12 max-w-md max-h-[90vh] overflow-y-auto"
            x-transition.scale.origin.top.duration.200ms
            x-transition.opacity.duration.200ms
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" x-text="clientId ? 'Редактировать клиента' : 'Добавить клиента'"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600" type="button" aria-label="Закрыть">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form :action="clientId ? '/clients/' + clientId : '/clients'" method="POST">
                    @csrf
                    <input
                        type="hidden"
                        :name="clientId ? '_method' : null"
                        :value="clientId ? 'PUT' : null"
                    >

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Имя</label>
                        <input type="text" name="name" x-model="form.name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Описание</label>
                        <textarea name="description" x-model="form.description" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" rows="3"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
                        <input type="text" name="phone" x-model="form.phone" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" x-model="form.email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Скидка на оборудование (%)</label>
                        <input type="number" name="discount_equipment" x-model="form.discount_equipment" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Скидка на услуги (%)</label>
                        <input type="number" name="discount_services" x-model="form.discount_services" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Скидка на материалы (%)</label>
                        <input type="number" name="discount_materials" x-model="form.discount_materials" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="blacklisted" x-model="form.blacklisted" class="mr-2 rounded border-gray-300 text-blue-500 focus:ring-blue-500">
                            Черный список
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
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

    function clientManager() {
        return {
            isOpen: false,
            clientId: null,
            search: '',
            sort: 'name',
            direction: 'asc',
            form: {
                name: '',
                description: '',
                phone: '',
                email: '',
                discount_equipment: 0,
                discount_services: 0,
                discount_materials: 0,
                blacklisted: false,
            },
            init() {
                this.isOpen = false;
                console.log('Инициализация clientManager, isOpen:', this.isOpen);
            },
            openModal(client = null) {
                this.isOpen = true;
                console.log('Открытие модального окна, client:', client, 'isOpen:', this.isOpen);
                if (client) {
                    this.clientId = client.id;
                    this.form = { ...client };
                } else {
                    this.clientId = null;
                    this.form = {
                        name: '',
                        description: '',
                        phone: '',
                        email: '',
                        discount_equipment: 0,
                        discount_services: 0,
                        discount_materials: 0,
                        blacklisted: false,
                    };
                }
            },
            closeModal() {
                this.isOpen = false;
                console.log('Закрытие модального окна, isOpen:', this.isOpen);
            },
            fetchClients() {
                console.log('Поиск клиентов, search:', this.search, 'sort:', this.sort, 'direction:', this.direction);
                fetch(`/clients?search=${encodeURIComponent(this.search)}&sort=${this.sort}&direction=${this.direction}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Ответ сервера:', data);
                        const tableBody = document.getElementById('clients-table');
                        if (tableBody) {
                            tableBody.innerHTML = data.view || '<tr><td colspan="8">Нет данных</td></tr>';
                        } else {
                            console.error('Элемент clients-table не найден');
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка при поиске:', error);
                        this.showToast('Ошибка при загрузке данных: ' + error.message);
                    });
            },
            sortBy(column) {
                if (this.sort === column) {
                    this.direction = this.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sort = column;
                    this.direction = 'asc';
                }
                console.log('Сортировка по:', column, 'направление:', this.direction);
                this.fetchClients();
            },
            showToast(message) {
                const toast = document.getElementById('toast');
                const toastMessage = document.getElementById('toastMessage');
                toastMessage.textContent = message;
                toast.classList.remove('translate-y-full');
                setTimeout(() => {
                    toast.classList.add('translate-y-full');
                }, 2000);
            }
        };
    }
</script>
</body>
</html>
