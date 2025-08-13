<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Компании</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .table-scroll {
            overflow-x: auto;
            max-width: 100%;
        }
        #companiesTable {
            table-layout: fixed;
            width: 100%;
        }
        #companiesTable th, #companiesTable td {
            min-width: 0;
            word-wrap: break-word;
        }
        #companiesTable th {
            padding: 0.75rem 1rem;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        #companiesTable td {
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
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        @media (max-width: 640px) {
            #companiesTable th:nth-child(n+4):nth-child(-n+6),
            #companiesTable td:nth-child(n+4):nth-child(-n+6) {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="bg-white shadow rounded-lg" x-data="companyManager()" x-init="init()">
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
                        @click="openCompanyModal()"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Добавить компанию
                </button>
            </div>
        </div>
    </div>

    <div class="tab-content active" id="companiesTab">
        <div class="table-scroll p-4">
            <table id="companiesTable" class="min-w-full bg-white">
                <thead>
                <tr class="bg-gray-100">
                    <th @click="sortBy('name')">Название
                        <span x-show="sort === 'name' && direction === 'asc'">↑</span>
                        <span x-show="sort === 'name' && direction === 'desc'">↓</span>
                    </th>
                    <th @click="sortBy('type')">Тип
                        <span x-show="sort === 'type' && direction === 'asc'">↑</span>
                        <span x-show="sort === 'type' && direction === 'desc'">↓</span>
                    </th>
                    <th @click="sortBy('country')">Страна
                        <span x-show="sort === 'country' && direction === 'asc'">↑</span>
                        <span x-show="sort === 'country' && direction === 'desc'">↓</span>
                    </th>
                    <th @click="sortBy('tax_rate')">Ставка, %
                        <span x-show="sort === 'tax_rate' && direction === 'asc'">↑</span>
                        <span x-show="sort === 'tax_rate' && direction === 'desc'">↓</span>
                    </th>
                    <th @click="sortBy('accounting_method')">Учет в смете
                        <span x-show="sort === 'accounting_method' && direction === 'asc'">↑</span>
                        <span x-show="sort === 'accounting_method' && direction === 'desc'">↓</span>
                    </th>
                    <th>Комментарий</th>
                    <th @click="sortBy('is_default')">По умолчанию
                        <span x-show="sort === 'is_default' && direction === 'asc'">↑</span>
                        <span x-show="sort === 'is_default' && direction === 'desc'">↓</span>
                    </th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody id="companies-table">
                @include('companies.partials.table')
                </tbody>
            </table>
        </div>
    </div>

    <!-- Модальное окно для добавления/редактирования компании -->
    <div
            x-show="isCompanyModalOpen"
            x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <form
                    x-bind:action="companyId ? '/companies/' + companyId : '/companies'"
                    @submit.prevent="submitCompanyForm"
                    enctype="multipart/form-data"
            >
                <h2 class="text-xl font-semibold mb-4" x-text="companyId ? 'Редактировать компанию' : 'Добавить компанию'"></h2>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Название</label>
                    <input
                            type="text"
                            name="name"
                            x-model="companyForm.name"
                            required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            x-on:input="console.log('Name input:', companyForm.name)"
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Тип</label>
                    <select
                            name="type"
                            x-model="companyForm.type"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Выберите тип</option>
                        <option value="ip">ИП</option>
                        <option value="ur">Юр. лицо</option>
                        <option value="fl">Физ. лицо</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Страна</label>
                    <input
                            type="text"
                            name="country"
                            x-model="companyForm.country"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ставка, %</label>
                    <input
                            type="number"
                            name="tax_rate"
                            x-model.number="companyForm.tax_rate"
                            step="0.01"
                            min="0"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Учет в смете</label>
                    <select
                            name="accounting_method"
                            x-model="companyForm.accounting_method"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Выберите метод</option>
                        <option value="osn_inclusive">ОСН, налог в стоимости</option>
                        <option value="osn_exclusive">ОСН, налог сверху</option>
                        <option value="usn_inclusive">УСН, налог в стоимости</option>
                        <option value="usn_exclusive">УСН, налог сверху</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Комментарий</label>
                    <textarea
                            name="comment"
                            x-model="companyForm.comment"
                            rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    ></textarea>
                </div>
                <div class="mb-4 flex items-center">
                    <input
                            type="checkbox"
                            name="is_default"
                            x-model="companyForm.is_default"
                            value="1"
                            class="mr-2"
                    >
                    <label class="text-sm font-medium text-gray-700">Использовать по умолчанию</label>
                </div>
                <div class="flex justify-end space-x-2">
                    <button
                            type="button"
                            @click="closeCompanyModal"
                            class="px-4 py-2 border rounded hover:bg-gray-100"
                    >
                        Отмена
                    </button>
                    <button
                            type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                            :disabled="submitDisabled || !companyForm.name"
                    >
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
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
        Alpine.data('companyManager', () => ({
            activeTab: 'companies',
            search: '',
            sort: 'name',
            direction: 'asc',
            isCompanyModalOpen: false,
            companyId: null,
            submitDisabled: false,
            companyForm: {
                name: '',
                type: '',
                country: '',
                tax_rate: null,
                accounting_method: '',
                comment: '',
                is_default: false,
            },
            companies: @json($companies ?? []),
            init() {
                console.log('Инициализация companyManager, companies:', this.companies);
                this.fetchData();
            },
            openTab(tab) {
                this.activeTab = tab;
                document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
                document.getElementById(tab + 'Tab').classList.add('active');
                document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('bg-blue-500', 'text-white'));
                document.querySelector(`.tab-button[onclick*="${tab}"]`)?.classList.add('bg-blue-500', 'text-white');
                this.fetchData();
            },
            openCompanyModal(company = null) {
                this.isCompanyModalOpen = true;
                if (company) {
                    this.companyId = company.id;
                    this.companyForm = {
                        name: company.name || '',
                        type: company.type || '',
                        country: company.country || '',
                        tax_rate: company.tax_rate || null,
                        accounting_method: company.accounting_method || '',
                        comment: company.comment || '',
                        is_default: company.is_default || false,
                    };
                } else {
                    this.companyId = null;
                    this.companyForm = {
                        name: '',
                        type: '',
                        country: '',
                        tax_rate: null,
                        accounting_method: '',
                        comment: '',
                        is_default: false,
                    };
                }
                console.log('Company modal opened, companyForm:', this.companyForm);
            },
            closeCompanyModal() {
                this.isCompanyModalOpen = false;
            },
            async submitCompanyForm(event) {
                event.preventDefault();
                if (!this.companyForm.name) {
                    this.showToast('Название обязательно для заполнения');
                    return;
                }
                this.submitDisabled = true;
                const form = event.target;
                const formData = new FormData(form);
                formData.set('name', this.companyForm.name); // Явно задаем name
                const url = this.companyId ? `/companies/${this.companyId}` : '/companies';
                const method = this.companyId ? 'POST' : 'POST'; // Используем POST для обоих случаев
                if (this.companyId) {
                    formData.append('_method', 'PUT'); // Явно добавляем _method для PUT
                }
                console.log('Submitting companyForm:', Object.fromEntries(formData));
                try {
                    const response = await fetch(url, {
                        method: 'POST', // Всегда POST, так как _method обрабатывает PUT
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    if (response.ok) {
                        this.showToast(this.companyId ? 'Компания обновлена' : 'Компания добавлена');
                        this.closeCompanyModal();
                        this.fetchData();
                    } else {
                        const error = await response.json();
                        console.error('Server error:', error);
                        this.showToast('Ошибка: ' + (error.message || 'Неизвестная ошибка'));
                    }
                } catch (e) {
                    console.error('Error submitting form:', e);
                    this.showToast('Ошибка при сохранении');
                } finally {
                    this.submitDisabled = false;
                }
            },
            async deleteCompany(id) {
                if (!confirm('Удалить компанию?')) return;
                try {
                    const response = await fetch(`/companies/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                    });
                    if (response.ok) {
                        this.showToast('Компания удалена');
                        this.fetchData();
                    } else {
                        this.showToast('Ошибка удаления');
                    }
                } catch (e) {
                    console.error('Error deleting company:', e);
                    this.showToast('Ошибка при удалении');
                }
            },
            async fetchData() {
                const response = await fetch(`/companies?search=${encodeURIComponent(this.search)}&sort=${this.sort}&direction=${this.direction}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (response.ok) {
                    const data = await response.json();
                    const tableBody = document.getElementById('companies-table');
                    tableBody.innerHTML = data.view || '<tr><td colspan="8">Нет данных</td></tr>';
                    this.companies = data.companies || [];
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
            }
        }));
    });
</script>
</body>
</html>
