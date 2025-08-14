<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Контрагенты</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .table-scroll {
            overflow-x: auto;
            max-width: 100%;
        }
        #counterpartiesTable {
            table-layout: fixed;
            width: 100%;
        }
        #counterpartiesTable th, #counterpartiesTable td {
            min-width: 0;
            word-wrap: break-word;
        }
        #counterpartiesTable th {
            padding: 0.75rem 1rem;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        #counterpartiesTable td {
            padding: 1rem 1rem;
            white-space: nowrap;
            text-align: center;
            font-size: 0.875rem;
            color: #111827;
        }
        #toast {
            z-index: 9999;
        }
        [x-cloak] {
            display: none !important;
        }
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
            justify-content: center;
            align-items: center;
        }
        .modal.hidden {
            display: none;
        }
        .modal:not(.hidden) {
            display: flex;
        }
        .modal-content {
            background-color: white;
            padding: 24px;
            border-radius: 8px;
            max-width: 600px;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        @media (max-width: 640px) {
            #counterpartiesTable th:nth-child(n+4):nth-child(-n+6),
            #counterpartiesTable td:nth-child(n+4):nth-child(-n+6) {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="bg-white shadow rounded-lg" x-data="counterparties()" x-init="init()">
    @include('layouts.navigation')

    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
            <div class="flex items-center space-x-4">
                <input
                    type="text"
                    x-model.debounce.500="search"
                    @input="fetchData"
                    placeholder="Поиск..."
                    class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                <button
                    @click="openModal()"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Добавить контрагента
                </button>
            </div>
        </div>
    </div>

    <!-- Модальное окно для создания -->
    <div class="modal hidden" x-bind:class="{ 'hidden': !isModalOpen }" @click="closeModal">
        <div class="modal-content" @click.stop>
            <h2 class="text-lg font-medium mb-4">Добавить контрагента</h2>
            <form @submit.prevent="submitForm" enctype="multipart/form-data">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Наименование</label>
                        <input
                            type="text"
                            x-model="form.name"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Менеджер</label>
                        <select
                            x-model="form.manager_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Выберите менеджера</option>
                            @foreach ($managers as $manager)
                                <option :value="{{ $manager->id }}">{{ $manager->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Код</label>
                        <input
                            type="text"
                            x-model="form.code"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Статус</label>
                        <select
                            x-model="form.status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Выберите статус</option>
                            <option value="new">Новый</option>
                            <option value="verified">Проверенный</option>
                            <option value="dangerous">Опасный</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Фактический адрес</label>
                        <input
                            type="text"
                            x-model="form.actual_address"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Комментарий</label>
                        <textarea
                            x-model="form.comment"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Доступен для субаренды</label>
                        <input
                            type="checkbox"
                            x-model="form.is_available_for_sublease"
                            class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 border rounded-md hover:bg-gray-100"
                    >
                        Закрыть
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        :disabled="submitDisabled"
                    >
                        Создать
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица контрагентов -->
    <div class="table-scroll p-4 sm:p-6">
        <div x-html="tableContent"></div>
    </div>

    <!-- Тост-уведомление -->
    <div
        id="toast"
        x-cloak
        class="fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded shadow-lg transform transition-transform translate-y-full"
    >
        <span id="toastMessage"></span>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('counterparties', () => ({
            form: {
                name: '',
                manager_id: '',
                code: '',
                status: '',
                actual_address: '',
                comment: '',
                is_available_for_sublease: false,
            },
            search: '',
            sortField: 'name',
            sortDirection: 'asc',
            submitDisabled: false,
            tableContent: '',
            isModalOpen: false,
            init() {
                console.log('Alpine initialized for counterparties');
                this.fetchData();
            },
            openModal() {
                console.log('Opening modal for create');
                this.isModalOpen = true;
            },
            closeModal() {
                console.log('Closing modal');
                this.isModalOpen = false;
                this.form = {
                    name: '',
                    manager_id: '',
                    code: '',
                    status: '',
                    actual_address: '',
                    comment: '',
                    is_available_for_sublease: false,
                };
            },
            async fetchData() {
                console.log('Fetching data with sort:', this.sortField, this.sortDirection);
                try {
                    const response = await fetch(`/counterparties?search=${encodeURIComponent(this.search)}&sort=${this.sortField}&direction=${this.sortDirection}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const data = await response.json();
                    this.tableContent = data.view || '<tr><td colspan="8">Нет данных</td></tr>';
                } catch (e) {
                    console.error('Error loading counterparties:', e);
                    this.showToast('Ошибка при загрузке контрагентов: ' + e.message);
                }
            },
            sort(field) {
                console.log('Sorting by:', field);
                if (this.sortField === field) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortField = field;
                    this.sortDirection = 'asc';
                }
                this.fetchData();
            },
            async submitForm(event) {
                event.preventDefault();
                this.submitDisabled = true;
                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    if (key === 'is_available_for_sublease') {
                        formData.append(key, this.form[key] ? '1' : '0');
                    } else {
                        formData.append(key, this.form[key] || '');
                    }
                });
                console.log('Submitting form:', Object.fromEntries(formData));
                try {
                    const response = await fetch('/counterparties', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    if (response.ok) {
                        this.showToast('Контрагент создан');
                        this.closeModal();
                        this.fetchData();
                    } else {
                        const error = await response.json();
                        console.error('Server error:', error);
                        this.showToast('Ошибка: ' + (error.message || 'Неизвестная ошибка'));
                    }
                } catch (e) {
                    console.error('Error submitting form:', e);
                    this.showToast('Ошибка при создании');
                } finally {
                    this.submitDisabled = false;
                }
            },
            async deleteCounterparty(id) {
                if (!confirm('Вы уверены, что хотите удалить контрагента?')) return;
                try {
                    const response = await fetch(`/counterparties/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                    });
                    if (response.ok) {
                        this.showToast('Контрагент удален');
                        this.fetchData();
                    } else {
                        const error = await response.json();
                        console.error('Server error:', error);
                        this.showToast('Ошибка: ' + (error.message || 'Неизвестная ошибка'));
                    }
                } catch (e) {
                    console.error('Error deleting counterparty:', e);
                    this.showToast('Ошибка при удалении');
                }
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
        }));
    });
</script>
</body>
</html>
