<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Сотрудники</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .no-spin::-webkit-outer-spin-button,
        .no-spin::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .no-spin {
            -moz-appearance: textfield;
        }
    </style>
</head>
<body class="antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')
    <div class="container mx-auto p-6">
        <!-- Кнопка добавления сотрудника -->
        <div class="flex justify-end mb-6">
            <button onclick="openCreateModal()" class="inline-block bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                Добавить сотрудника
        </button>
        </div>

        <!-- Таблица сотрудников -->
        <div class="bg-white rounded-lg shadow overflow-hidden min-h-[600px] max-h-[800px]">
            <div class="overflow-x-auto h-full">
                <table class="min-w-full h-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <!-- Имя -->
                            <th class="px-6 py-3 text-left w-48">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900">Имя</span>
                                    <button onclick="sortTable('name')" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </button>
                                </div>
                                <input type="text" id="search-name" placeholder="Поиск по имени..."
                                       class="mt-2 w-full px-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            </th>

                            <!-- Email -->
                            <th class="px-6 py-3 text-left w-64">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900">Email</span>
                                    <button onclick="sortTable('email')" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </button>
                                </div>
                                <input type="text" id="search-email" placeholder="Поиск по email..."
                                       class="mt-2 w-full px-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            </th>

                            <!-- Роль -->
                            <th class="px-6 py-3 text-left w-32">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900">Роль</span>
                                    <button onclick="sortTable('role')" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </button>
                                </div>
                                <select id="search-role" class="mt-2 w-full px-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                                    <option value="">Все роли</option>
                                    <option value="нет специальности">нет специальности</option>
                                    <option value="admin">admin</option>
                                    <option value="manager">manager</option>
                                    <option value="driver">driver</option>
                                </select>
                            </th>

                            <!-- Телефон -->
                            <th class="px-6 py-3 text-left w-32">
                                <span class="text-sm font-medium text-gray-900">Телефон</span>
                            </th>

                            <!-- Статус -->
                            <th class="px-6 py-3 text-left w-48">
                                <span class="text-sm font-medium text-gray-900">Статус</span>
                            </th>

                            <!-- Действия -->
                            <th class="px-6 py-3 text-left w-40">
                                <span class="text-sm font-medium text-gray-900">Действия</span>
                            </th>
                </tr>
                </thead>
                    <tbody id="employees-tbody" class="min-h-[500px] max-h-[600px]">
                        @foreach ($employees as $employee)
                            <tr class="border-t border-gray-200 hover:bg-gray-50 group">
                                <td class="px-6 py-4 text-sm text-gray-900 truncate">
                                    <a href="/employees/{{ $employee->id }}" class="text-blue-800 hover:underline cursor-pointer" title="Перейти в профиль">
                                        {{ $employee->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 truncate">{{ $employee->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 truncate">
                                    {{ $employee->roles->first() ? $employee->roles->first()->name : 'Не назначена' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 truncate">{{ $employee->phone ?? '-' }}</td>
                                <td class="px-6 py-4 w-48">
                                    @php
                                        $status = $employee->employeeStatus?->status ?? 'free';
                                        $statusConfig = [
                                            'free' => ['text' => 'Свободен', 'class' => 'bg-green-100 text-green-800'],
                                            'unavailable' => ['text' => 'Недоступен', 'class' => 'bg-red-100 text-red-800'],
                                            'assigned' => ['text' => 'Назначен на проекты', 'class' => 'bg-blue-100 text-blue-800']
                                        ];
                                        $config = $statusConfig[$status] ?? $statusConfig['free'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['class'] }}">
                                        {{ $config['text'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <!-- Динамическая иконка в зависимости от статуса -->
                                        @if(($employee->employeeStatus?->status ?? 'free') === 'free')
                                            <!-- Свободен → синий "+" -->
                                            <button onclick="openChangeStatusModal({{ $employee->id }})"
                                                    class="text-blue-600 hover:text-blue-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                    title="Сменить статус / назначить">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </button>
                                        @elseif(($employee->employeeStatus?->status ?? 'free') === 'unavailable')
                                            <!-- Недоступен → красный "!" -->
                                            <button onclick="openStatusCommentModal({{ $employee->id }})"
                                                    class="text-red-600 hover:text-red-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                    title="Причина недоступности">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                </svg>
                                            </button>
                                        @elseif(($employee->employeeStatus?->status ?? 'free') === 'assigned')
                                            <!-- Назначен на проекты → синяя "i" -->
                                            <button onclick="showAssignments({{ $employee->id }})"
                                                    class="text-blue-600 hover:text-blue-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                    title="Назначен на проекты">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </button>
                                        @endif

                                        <!-- Редактировать (карандаш) -->
                                        <button onclick="openEditModal({{ $employee->id }})"
                                                class="text-gray-600 hover:text-gray-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                title="Редактировать">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>

                                        <!-- Профиль -->
                                        <a href="/employees/{{ $employee->id }}"
                                           class="text-gray-600 hover:text-gray-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
                                           title="Профиль">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </a>

                                        <!-- Удалить (корзина) -->
                                        <button onclick="deleteEmployee({{ $employee->id }})"
                                                class="text-red-600 hover:text-red-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                title="Удалить сотрудника">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                    </tr>
                @endforeach

                        <!-- Пустое состояние внутри таблицы (всегда присутствует, но скрыто) -->
                        <tr id="empty-state-row" class="hidden">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет сотрудников</h3>
                                <p class="mt-1 text-sm text-gray-500">Начните с добавления первого сотрудника.</p>
                            </td>
                        </tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>
        </div>

<!-- Модальное окно создания сотрудника -->
<div id="createEmployeeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Добавить сотрудника</h2>
        <form id="createEmployeeForm">
                    @csrf
                    <div class="mb-4">
                <label for="create-name" class="block text-sm font-medium text-gray-600">Имя *</label>
                <input type="text" name="name" id="create-name" class="mt-1 block w-full border-gray-300 rounded-md font-normal" required>
                    </div>
                    <div class="mb-4">
                <label for="create-phone" class="block text-sm font-medium text-gray-600">Телефон</label>
                <input type="text" name="phone" id="create-phone" class="mt-1 block w-full border-gray-300 rounded-md font-normal">
                    </div>
                    <div class="mb-4">
                <label for="create-role" class="block text-sm font-medium text-gray-600">Роль *</label>
                <select name="role" id="create-role" class="mt-1 block w-full border-gray-300 rounded-md font-normal" required>
                    <option value="">Выберите роль</option>
                    <option value="нет специальности">нет специальности</option>
                    <option value="admin">admin</option>
                    <option value="manager">manager</option>
                    <option value="driver">driver</option>
                </select>
                    </div>
                    <div class="mb-4">
                <label for="create-email" class="block text-sm font-medium text-gray-600">Email *</label>
                <input type="email" name="email" id="create-email" class="mt-1 block w-full border-gray-300 rounded-md font-normal" required>
                    </div>
                    <div class="mb-4">
                <label for="create-password" class="block text-sm font-medium text-gray-600">Пароль *</label>
                <div class="relative">
                    <input type="password" name="password" id="create-password" class="mt-1 block w-full pr-10 border-gray-300 rounded-md font-normal" required>
                    <button type="button" onclick="togglePasswordVisibility('create-password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg id="create-password-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="mb-6">
                <label for="create-password-confirmation" class="block text-sm font-medium text-gray-600">Подтверждение пароля *</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="create-password-confirmation" class="mt-1 block w-full pr-10 border-gray-300 rounded-md font-normal" required>
                    <button type="button" onclick="togglePasswordVisibility('create-password-confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg id="create-password-confirmation-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
                    </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('createEmployeeModal')" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Отмена</button>
                        <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Создать</button>
                    </div>
                </form>
            </div>
</div>

<!-- Модальное окно редактирования сотрудника -->
<div id="editEmployeeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Редактировать сотрудника</h2>
        <form id="editEmployeeForm">
            @csrf
            <input type="hidden" id="edit-employee-id">
            <div class="mb-4">
                <label for="edit-name" class="block text-sm font-medium text-gray-600">Имя *</label>
                <input type="text" name="name" id="edit-name" class="mt-1 block w-full border-gray-300 rounded-md font-normal" required>
            </div>
            <div class="mb-4">
                <label for="edit-phone" class="block text-sm font-medium text-gray-600">Телефон</label>
                <input type="text" name="phone" id="edit-phone" class="mt-1 block w-full border-gray-300 rounded-md font-normal">
            </div>
            <div class="mb-6">
                <label for="edit-role" class="block text-sm font-medium text-gray-600">Роль *</label>
                <select name="role" id="edit-role" class="mt-1 block w-full border-gray-300 rounded-md font-normal" required>
                    <option value="">Выберите роль</option>
                    <option value="нет специальности">нет специальности</option>
                    <option value="admin">admin</option>
                    <option value="manager">manager</option>
                    <option value="driver">driver</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editEmployeeModal')" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Отмена</button>
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно добавления в проект -->
<div id="addToProjectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">
        <!-- Крестик закрытия -->
        <button onclick="closeModal('addToProjectModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Добавление персонала на мероприятие</h2>
            <p class="text-sm text-gray-500">Выберите проект для назначения сотрудника</p>
        </div>

        <form id="addToProjectForm">
            <input type="hidden" id="add-employee-id">

            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-3">Проект</label>
                <div class="relative">
                    <input type="text" id="add-project-search"
                           placeholder="Начните вводить название проекта..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-normal transition-all duration-200"
                           autocomplete="off">
                    <input type="hidden" id="add-project-id" required>

                    <!-- Выпадающий список автодополнения -->
                    <div id="project-suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-2 max-h-60 overflow-y-auto hidden">
                        <!-- Заглушка "Загружаются проекты" -->
                        <div id="project-loading" class="px-4 py-6 text-center text-gray-500 hidden">
                            <div class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Загружаются проекты...
                            </div>
                        </div>

                        <!-- Сообщение "Нет подходящих проектов" -->
                        <div id="project-no-results" class="px-6 py-8 text-center hidden">
                            <div class="mb-4">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <div class="text-base font-medium text-gray-900 mb-2">Проекты не найдены</div>
                            <div class="text-sm text-gray-500">Попробуйте изменить поисковый запрос</div>
                        </div>

                        <!-- Список проектов -->
                        <div id="project-suggestions-list"></div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between">
                <button
                    type="button"
                    onclick="closeModal('addToProjectModal')"
                    class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 hover:shadow-md transition-all duration-200 text-sm font-medium"
                >
                    Закрыть
                </button>
                <button
                    type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 hover:shadow-md transition-all duration-200 font-medium flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>Добавить</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно комментария к статусу -->
<div id="statusCommentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">
        <!-- Крестик закрытия -->
        <button onclick="closeModal('statusCommentModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Комментарий к статусу</h2>
            <p class="text-sm text-gray-500">Укажите причину недоступности сотрудника</p>
        </div>

        <form id="statusCommentForm">
            @csrf
            <input type="hidden" id="comment-employee-id">

            <div class="mb-8">
                <label for="status-comment" class="block text-sm font-medium text-gray-700 mb-3">Комментарий (причина недоступности) *</label>
                <textarea
                    name="comment"
                    id="status-comment"
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-normal resize-none transition-all duration-200"
                    placeholder="Комментарий (причина недоступности)"
                    required
                ></textarea>
            </div>

            <div class="flex justify-between">
                <button
                    type="button"
                    onclick="closeModal('statusCommentModal')"
                    class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 hover:shadow-md transition-all duration-200 text-sm font-medium"
                >
                    Отмена
                </button>
                <div class="flex space-x-3">
                    <button
                        type="button"
                        onclick="saveStatusComment()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 hover:shadow-md transition-all duration-200 text-sm font-medium"
                        title="Сохранить"
                    >
                        Сохранить
                    </button>
                    <button
                        type="button"
                        onclick="deleteStatusComment()"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 hover:shadow-md transition-all duration-200 text-sm font-medium"
                        title="Удалить комментарий"
                    >
                        Удалить
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно смены статуса -->
<div id="changeStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">
        <!-- Крестик закрытия -->
        <button onclick="closeModal('changeStatusModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Сменить статус</h2>
            <p class="text-sm text-gray-400">Выберите действие для сотрудника</p>
        </div>

        <input type="hidden" id="change-status-employee-id">

        <div class="space-y-3 mb-8 flex flex-col items-center">
            <button
                onclick="makeUnavailable()"
                class="w-4/5 bg-red-600 text-white py-3 px-6 rounded-lg hover:bg-red-700 hover:shadow-lg hover:scale-105 transition-all duration-200 text-sm font-medium flex items-center justify-center cursor-pointer active:scale-95"
            >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                Сделать недоступным
            </button>

            <button
                onclick="assignToProject()"
                class="w-4/5 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 hover:shadow-lg hover:scale-105 transition-all duration-200 text-sm font-medium flex items-center justify-center cursor-pointer active:scale-95"
            >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Назначить на проект
            </button>
        </div>

        <div class="flex justify-start">
            <button
                onclick="closeModal('changeStatusModal')"
                class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 hover:shadow-md transition-all duration-200 text-sm font-medium"
            >
                Отмена
            </button>
        </div>
    </div>
</div>

<!-- Модальное окно назначений -->
<div id="assignmentsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md max-h-[80vh] flex flex-col">
        <input type="hidden" id="assignments-employee-id">

        <!-- Крестик закрытия -->
        <button onclick="closeModal('assignmentsModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Назначен на проекты</h2>
        </div>

        <div id="assignments-list" class="flex-1 overflow-y-auto mb-6">
            <!-- Список назначений будет загружен динамически -->
        </div>

        <div class="border-t pt-4">
            <div class="flex justify-between">
                <button
                    onclick="closeModal('assignmentsModal')"
                    class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 hover:shadow-md transition-all duration-200 text-sm font-medium"
                >
                    Отмена
                </button>
                <button
                    onclick="assignMoreFromAssignments()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 hover:shadow-md transition-all duration-200 text-sm font-medium flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Назначить ещё
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для подтверждения удаления сотрудника -->
<div id="deleteConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center h-full w-full hidden z-50">
    <div class="bg-white rounded-lg shadow-lg border max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Подтверждение удаления</h3>
                <button onclick="closeModal('deleteConfirmModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="mb-6">
                <p class="text-gray-700">Вы уверены, что хотите удалить этого сотрудника?</p>
                <p class="text-sm text-gray-500 mt-2">Это действие нельзя будет отменить.</p>
            </div>

            <div class="flex justify-end space-x-3">
                <button onclick="closeModal('deleteConfirmModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                    Отмена
                </button>
                <button onclick="confirmDeleteEmployee()" class="px-4 py-2 text-sm font-medium text-white bg-red-500 border border-transparent rounded-md hover:bg-red-600">
                    Удалить
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для подтверждения удаления назначения -->
<div id="deleteAssignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center h-full w-full hidden z-50">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">
        <!-- Крестик закрытия -->
        <button onclick="closeModal('deleteAssignmentModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Удалить назначение</h2>
            <p class="text-sm text-gray-500">Вы уверены, что хотите удалить это назначение?</p>
        </div>

        <div class="mb-8">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-700">
                    <span class="font-medium">Проект:</span>
                    <span id="delete-assignment-project-name" class="text-gray-900"></span>
                </p>
                <p class="text-gray-700 mt-1">
                    <span class="font-medium">Сотрудник:</span>
                    <span id="delete-assignment-employee-name" class="text-gray-900"></span>
                </p>
            </div>
        </div>

        <div class="flex justify-between">
            <button
                onclick="closeModal('deleteAssignmentModal')"
                class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 hover:shadow-md transition-all duration-200 text-sm font-medium"
            >
                Отмена
            </button>
            <button
                onclick="confirmDeleteAssignment()"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 hover:shadow-md transition-all duration-200 font-medium flex items-center space-x-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                <span>Удалить</span>
            </button>
        </div>
    </div>
</div>

<!-- Toast уведомления -->
<div id="toast" class="fixed top-4 right-0 bg-green-500 text-white px-6 py-3 rounded-l-md shadow-lg transform translate-x-full transition-transform duration-300 z-50">
    <span id="toast-message"></span>
</div>

<script>
// Глобальные переменные
let currentSort = { field: 'name', order: 'asc' };
let allEmployees = []; // Массив всех сотрудников для клиентской фильтрации
let searchTimeout = null; // Таймаут для поиска
let projectSearchTimeout = null;
let selectedProjectIndex = -1;
let allProjects = []; // Массив всех загруженных проектов

// Инициализация
document.addEventListener('DOMContentLoaded', function() {
    setupSearchDebounce();
    setupFormSubmissions();
    loadEmployees(); // Загружаем всех сотрудников при инициализации
});

// Настройка debounce для поиска
function setupSearchDebounce() {
    const searchInputs = ['search-name', 'search-email'];

    searchInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterEmployees();
                }, 300);
            });
        }
    });

    // Отдельная обработка для выпадающего списка ролей
    const roleSelect = document.getElementById('search-role');
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            filterEmployees();
        });
    }
}

// Настройка отправки форм
function setupFormSubmissions() {
    // Форма создания
    document.getElementById('createEmployeeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createEmployee();
    });

    // Форма редактирования
    document.getElementById('editEmployeeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateEmployee();
    });

    // Форма добавления в проект
    document.getElementById('addToProjectForm').addEventListener('submit', function(e) {
        e.preventDefault();
        addToProject();
    });

    // Форма комментария к статусу
    document.getElementById('statusCommentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveStatusComment();
    });
}

// Функции модальных окон
function openCreateModal() {
    document.getElementById('createEmployeeModal').classList.remove('hidden');
}

function openEditModal(employeeId) {
    console.log('openEditModal вызвана, employeeId:', employeeId);

    // Загрузить данные сотрудника
    fetch(`/managers/${employeeId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Данные сотрудника загружены:', data);
            document.getElementById('edit-employee-id').value = employeeId;
            document.getElementById('edit-name').value = data.name;
            document.getElementById('edit-phone').value = data.phone || '';
            document.getElementById('edit-role').value = data.roles[0]?.name || '';
            document.getElementById('editEmployeeModal').classList.remove('hidden');
        });
}

function openAddToProjectModal(employeeId) {
    document.getElementById('add-employee-id').value = employeeId;
    document.getElementById('addToProjectModal').classList.remove('hidden');

    // Очищаем поля
    document.getElementById('add-project-search').value = '';
    document.getElementById('add-project-id').value = '';
    document.getElementById('project-suggestions').classList.add('hidden');

    // Инициализируем автодополнение
    setTimeout(() => {
        initProjectAutocomplete();
    }, 100);
}

function openStatusCommentModal(employeeId) {
    document.getElementById('comment-employee-id').value = employeeId;

    // Находим сотрудника в массиве allEmployees
    const employee = allEmployees.find(emp => emp.id === employeeId);

    // Если у сотрудника есть комментарий к статусу, показываем его, иначе очищаем поле
    const commentField = document.getElementById('status-comment');
    if (employee && employee.employee_status && employee.employee_status.status_comment) {
        commentField.value = employee.employee_status.status_comment;
    } else {
        commentField.value = '';
    }

    document.getElementById('statusCommentModal').classList.remove('hidden');
}

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');

    // Очищаем поле комментария при закрытии модалки статуса
    if (modalId === 'statusCommentModal') {
        document.getElementById('status-comment').value = '';
    }
}

// API функции
function createEmployee() {
    const formData = new FormData(document.getElementById('createEmployeeForm'));

    console.log('Отправляем данные для создания сотрудника:', Object.fromEntries(formData));

    fetch('/managers', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Ответ сервера:', response.status, response.statusText);
        return response.json();
    })
    .then(data => {
        console.log('Данные ответа:', data);
        if (data.success) {
            showToast('Сохранено');
            closeModal('createEmployeeModal');
            document.getElementById('createEmployeeForm').reset();
            loadEmployees(); // Обновляем таблицу без перезагрузки
        } else {
            showToast('Ошибка при создании сотрудника: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка при создании сотрудника:', error);
        showToast('Ошибка при создании сотрудника');
    });
}

function updateEmployee() {
    const employeeId = document.getElementById('edit-employee-id').value;
    const formData = new FormData(document.getElementById('editEmployeeForm'));

    // Добавляем _method для эмуляции PATCH через POST
    formData.append('_method', 'PATCH');

    console.log('updateEmployee вызвана, employeeId:', employeeId);
    console.log('URL запроса:', `/managers/${employeeId}`);

    fetch(`/managers/${employeeId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Ответ сервера:', response.status, response.statusText);
        return response.json();
    })
    .then(data => {
        console.log('Данные ответа:', data);
        if (data.success) {
            showToast('Сохранено');
            closeModal('editEmployeeModal');
            loadEmployees();
        } else {
            showToast('Ошибка при обновлении сотрудника: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка при обновлении сотрудника:', error);
        showToast('Ошибка при обновлении сотрудника');
    });
}

let deleteEmployeeId = null;

function deleteEmployee(employeeId) {
    deleteEmployeeId = employeeId;
    openModal('deleteConfirmModal');
}

function confirmDeleteEmployee() {
    console.log('confirmDeleteEmployee вызвана, deleteEmployeeId:', deleteEmployeeId);
    if (deleteEmployeeId) {
        const url = `/managers/${deleteEmployeeId}`;
        console.log('Отправляем DELETE запрос на:', url);

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('Ответ сервера:', response.status, response.statusText);
            return response.json();
        })
        .then(data => {
            console.log('Данные ответа:', data);
            if (data.success) {
                showToast('Удалено');
                closeModal('deleteConfirmModal');
                loadEmployees(); // Обновляем таблицу без перезагрузки
            } else {
                showToast('Ошибка при удалении сотрудника: ' + (data.message || 'Неизвестная ошибка'));
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showToast('Ошибка при удалении сотрудника');
        });

        deleteEmployeeId = null;
    } else {
        console.error('deleteEmployeeId не установлен!');
    }
}



function addToProject() {
    const employeeId = document.getElementById('add-employee-id').value;
    const projectId = document.getElementById('add-project-id').value;

    // Валидация
    if (!projectId) {
        showToast('Выберите проект');
        return;
    }

    fetch('/assignments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            employee_id: employeeId,
            project_id: projectId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Назначен');
            closeModal('addToProjectModal');
            // Очищаем форму
            document.getElementById('addToProjectForm').reset();
            loadEmployees(); // Обновляем таблицу без перезагрузки
        } else {
            showToast('Ошибка при добавлении на проект: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showToast('Ошибка при добавлении на проект');
    });
}

function saveStatusComment() {
    const employeeId = document.getElementById('comment-employee-id').value;
    const comment = document.getElementById('status-comment').value;

    fetch(`/managers/${employeeId}/status-comment`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ comment: comment })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Сохранено');
            closeModal('statusCommentModal');
            loadEmployees(); // Обновляем таблицу без перезагрузки
        } else {
            showToast('Ошибка');
        }
    })
    .catch(error => {
        console.error('Ошибка при сохранении комментария:', error);
        showToast('Ошибка при сохранении комментария');
    });
}

function showAssignments(employeeId) {
    // Сохраняем ID сотрудника для кнопки "Назначить ещё"
    document.getElementById('assignments-employee-id').value = employeeId;

    // Находим сотрудника в массиве allEmployees для получения имени
    const employee = allEmployees.find(emp => emp.id === employeeId);
    const employeeName = employee ? employee.name : 'Сотрудник';

    fetch(`/managers/${employeeId}/assignments`)
        .then(response => response.json())
        .then(assignments => {
            const list = document.getElementById('assignments-list');
            if (assignments.length === 0) {
                list.innerHTML = '<div class="text-center py-12"><p class="text-gray-500 text-lg">Нет назначений</p></div>';
                // Если назначений нет, не открываем модалку
                return;
            } else {
                list.innerHTML = assignments.map((assignment, index) => {
                    const hasUrl = assignment.project_url && assignment.project_url !== '';
                    const employeeId = document.getElementById('assignments-employee-id').value;

                    return `
                        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 truncate" title="${assignment.project_name}">
                                        ${assignment.project_name}
                                    </h3>
                                </div>
                                <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                                    ${hasUrl
                                        ? `<button onclick="window.location.href='${assignment.project_url}'" class="p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-all duration-200 cursor-pointer" title="Перейти в проект">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </button>`
                                        : `<div class="p-2 text-gray-400 cursor-not-allowed" title="Ссылка недоступна">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </div>`
                                    }
                                    <button
                                        onclick="removeAssignment(${employeeId}, ${assignment.project_id}, '${assignment.project_name}', '${employeeName}', ${assignments.length})"
                                        class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all duration-200 cursor-pointer"
                                        title="Удалить назначение"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }
            document.getElementById('assignmentsModal').classList.remove('hidden');
        });
}

// Функции поиска и сортировки
function filterEmployees() {
    const name = document.getElementById('search-name').value.toLowerCase();
    const email = document.getElementById('search-email').value.toLowerCase();
    const role = document.getElementById('search-role').value;

    console.log('Фильтруем сотрудников:', { name, email, role });

    // Фильтруем сотрудников на клиенте
    let filteredEmployees = allEmployees.filter(employee => {
        const employeeName = employee.name.toLowerCase();
        const employeeEmail = employee.email.toLowerCase();
        const employeeRole = employee.roles[0] ? employee.roles[0].name : '';

        const nameMatch = !name || employeeName.includes(name);
        const emailMatch = !email || employeeEmail.includes(email);
        const roleMatch = !role || employeeRole === role;

        return nameMatch && emailMatch && roleMatch;
    });

    console.log('Отфильтрованные сотрудники:', filteredEmployees.length);

    // Применяем сортировку
    if (currentSort.field) {
        filteredEmployees.sort((a, b) => {
            let aValue, bValue;

            switch (currentSort.field) {
                case 'name':
                    aValue = a.name.toLowerCase();
                    bValue = b.name.toLowerCase();
                    break;
                case 'email':
                    aValue = a.email.toLowerCase();
                    bValue = b.email.toLowerCase();
                    break;
                case 'role':
                    aValue = a.roles[0] ? a.roles[0].name.toLowerCase() : '';
                    bValue = b.roles[0] ? b.roles[0].name.toLowerCase() : '';
                    break;
                default:
                    aValue = a.name.toLowerCase();
                    bValue = b.name.toLowerCase();
            }

            if (currentSort.order === 'asc') {
                return aValue.localeCompare(bValue);
            } else {
                return bValue.localeCompare(aValue);
            }
        });
    }

    // Обновляем таблицу
    updateTable(filteredEmployees);
}

function sortTable(field) {
    if (currentSort.field === field) {
        currentSort.order = currentSort.order === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.field = field;
        currentSort.order = 'asc';
    }

    filterEmployees();
}

function updateTable(employees) {
    const tbody = document.getElementById('employees-tbody');
    const emptyStateRow = document.getElementById('empty-state-row');

    // Очищаем tbody, но сохраняем пустое состояние
    const existingRows = tbody.querySelectorAll('tr:not(#empty-state-row)');
    existingRows.forEach(row => row.remove());

    if (employees.length === 0) {
        // Показываем пустое состояние
        emptyStateRow.classList.remove('hidden');
    } else {
        // Скрываем пустое состояние и добавляем данные
        emptyStateRow.classList.add('hidden');

        employees.forEach(employee => {
            const row = document.createElement('tr');
            row.className = 'border-t border-gray-200 hover:bg-gray-50 group';
            row.innerHTML = `
                                                <td class="px-6 py-4 text-sm text-gray-900 truncate">
                                                    <a href="/employees/${employee.id}" class="text-blue-800 hover:underline cursor-pointer" title="Перейти в профиль">
                                                        ${employee.name}
                                                    </a>
                                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 truncate">${employee.email}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 truncate">${employee.roles[0] ? employee.roles[0].name : 'Не назначена'}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 truncate">${employee.phone || '-'}</td>
                <td class="px-6 py-4 w-48">
                    ${getStatusPill(employee)}
                </td>
                <td class="px-6 py-4 w-40">
                    <div class="flex items-center space-x-3">
                        ${getDynamicIcon(employee)}
                        <button onclick="openEditModal(${employee.id})"
                                class="text-gray-600 hover:text-gray-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
                                title="Редактировать">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <a href="/employees/${employee.id}"
                           class="text-gray-600 hover:text-gray-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
                           title="Профиль">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </a>
                        <button onclick="deleteEmployee(${employee.id})"
                                class="text-red-600 hover:text-red-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
                                title="Удалить сотрудника">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
}

// Функция загрузки всех сотрудников (только при первой загрузке)
function loadEmployees() {
    console.log('Загружаем всех сотрудников...');

    fetch('/managers?_=' + Date.now(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Ответ сервера:', response.status, response.statusText);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Полученные данные:', data);
        // Сохраняем всех сотрудников в глобальную переменную
        allEmployees = data;
        // Применяем фильтрацию
        filterEmployees();
    })
    .catch(error => {
        console.error('Ошибка при загрузке сотрудников:', error);
        showToast('Ошибка при загрузке данных');
    });
}

// Функция для получения пилюли статуса
function getStatusPill(employee) {
    const status = employee.employee_status && employee.employee_status.status ? employee.employee_status.status : 'free';

    const statusConfig = {
        'free': { text: 'Свободен', class: 'bg-green-100 text-green-800' },
        'unavailable': { text: 'Недоступен', class: 'bg-red-100 text-red-800' },
        'assigned': { text: 'Назначен на проекты', class: 'bg-blue-100 text-blue-800' }
    };

    const config = statusConfig[status] || statusConfig['free'];

    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${config.class}">
        ${config.text}
    </span>`;
}

// Функция для получения динамической иконки в зависимости от статуса
function getDynamicIcon(employee) {
    const status = employee.employee_status && employee.employee_status.status ? employee.employee_status.status : 'free';

    if (status === 'free') {
        // Свободен → синий "+"
        return `<button onclick="openChangeStatusModal(${employee.id})"
                class="text-blue-600 hover:text-blue-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
                title="Сменить статус / назначить">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
        </button>`;
    } else if (status === 'unavailable') {
        // Недоступен → красный "!"
        return `<button onclick="openStatusCommentModal(${employee.id})"
                class="text-red-600 hover:text-red-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
                title="Причина недоступности">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
        </button>`;
    } else if (status === 'assigned') {
        // Назначен на проекты → синяя "i"
        return `<button onclick="showAssignments(${employee.id})"
                class="text-blue-600 hover:text-blue-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
                title="Назначен на проекты">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </button>`;
    }

    // По умолчанию возвращаем синий "+"
    return `<button onclick="openChangeStatusModal(${employee.id})"
            class="text-blue-600 hover:text-blue-800 transition-all duration-200 hover:scale-105 hover:shadow-md"
            title="Сменить статус / назначить">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
    </button>`;
}

// Функция открытия модалки смены статуса
function openChangeStatusModal(employeeId) {
    document.getElementById('change-status-employee-id').value = employeeId;
    document.getElementById('changeStatusModal').classList.remove('hidden');
}

// Функция "Сделать недоступным"
function makeUnavailable() {
    const employeeId = document.getElementById('change-status-employee-id').value;
    closeModal('changeStatusModal');
    openStatusCommentModal(employeeId);
}

// Функция "Назначить на проект"
function assignToProject() {
    const employeeId = document.getElementById('change-status-employee-id').value;
    closeModal('changeStatusModal');
    openAddToProjectModal(employeeId);
}

// Функция удаления комментария к статусу
function deleteStatusComment() {
    const employeeId = document.getElementById('comment-employee-id').value;

    fetch(`/managers/${employeeId}/status-comment`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Статус снят');
            closeModal('statusCommentModal');
            loadEmployees(); // Обновляем таблицу
        } else {
            showToast('Ошибка');
        }
    })
    .catch(error => {
        console.error('Ошибка при удалении комментария:', error);
        showToast('Ошибка при удалении комментария');
    });
}

// Функция "Назначить ещё" из модалки назначений
function assignMoreFromAssignments() {
    const employeeId = document.getElementById('assignments-employee-id').value;
    closeModal('assignmentsModal');
    openAddToProjectModal(employeeId);
}

// Глобальные переменные для удаления назначения
let deleteAssignmentData = {
    employeeId: null,
    projectId: null,
    projectName: null,
    employeeName: null
};

// Функция удаления назначения
function removeAssignment(employeeId, projectId, projectName, employeeName, totalProjects) {
    // Сохраняем данные для модалки
    deleteAssignmentData = {
        employeeId: employeeId,
        projectId: projectId,
        projectName: projectName,
        employeeName: employeeName,
        totalProjects: totalProjects
    };

    // Заполняем модалку данными
    document.getElementById('delete-assignment-project-name').textContent = projectName;
    document.getElementById('delete-assignment-employee-name').textContent = employeeName;

    // Открываем модалку
    openModal('deleteAssignmentModal');
}

// Функция подтверждения удаления назначения
function confirmDeleteAssignment() {
    const { employeeId, projectId, totalProjects } = deleteAssignmentData;

    fetch(`/projects/${projectId}/staff/${employeeId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Назначение удалено');
            closeModal('deleteAssignmentModal');

            // Если это был последний проект, закрываем модалку "Назначен на проекты"
            if (totalProjects === 1) {
                closeModal('assignmentsModal');
            } else {
                // Обновляем список назначений
                showAssignments(employeeId);
            }

            // Обновляем основную таблицу сотрудников
            loadEmployees();
        } else {
            showToast('Ошибка');
        }
    })
    .catch(error => {
        console.error('Ошибка при удалении назначения:', error);
        showToast('Ошибка');
    });
}

// Функция переключения видимости пароля
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const eyeIcon = document.getElementById(inputId + '-eye');

    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
        `;
    } else {
        input.type = 'password';
        eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        `;
    }
}

// Утилиты
function showToast(message) {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    toastMessage.textContent = message;
    toast.classList.remove('translate-x-full');

    setTimeout(() => {
        toast.classList.add('translate-x-full');
    }, 3000);
}



// Инициализация автодополнения при открытии модалки
function initProjectAutocomplete() {
    console.log('initProjectAutocomplete вызвана');

    const searchInput = document.getElementById('add-project-search');
    const suggestionsDiv = document.getElementById('project-suggestions');
    const loadingDiv = document.getElementById('project-loading');
    const noResultsDiv = document.getElementById('project-no-results');
    const suggestionsListDiv = document.getElementById('project-suggestions-list');

    console.log('Элементы найдены:', {
        searchInput: !!searchInput,
        suggestionsDiv: !!suggestionsDiv,
        loadingDiv: !!loadingDiv,
        noResultsDiv: !!noResultsDiv,
        suggestionsListDiv: !!suggestionsListDiv
    });

    if (!searchInput) {
        console.error('searchInput не найден!');
        return;
    }

    // Обработчик фокуса на поле поиска (показываем все проекты)
    searchInput.addEventListener('focus', function() {
        console.log('Фокус на поле поиска - загружаем все проекты');
        loadAllProjects();
    });

    // Обработчик ввода в поле поиска
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        console.log('Ввод в поле поиска:', query);

        // Очищаем предыдущий таймаут
        if (projectSearchTimeout) {
            clearTimeout(projectSearchTimeout);
        }

        // Если поиск пустой, показываем все проекты
        if (query.length < 1) {
            console.log('Поиск пустой, показываем все проекты');
            displayProjects(allProjects);
            return;
        }

        // Фильтруем проекты локально
        const filteredProjects = allProjects.filter(project => {
            const title = project.title.toLowerCase();
            const location = (project.location || '').toLowerCase();
            const search = query.toLowerCase();

            return title.includes(search) || location.includes(search);
        });

        console.log('Отфильтрованные проекты:', filteredProjects);
        displayProjects(filteredProjects);
    });

    // Обработчик клавиш для навигации
    searchInput.addEventListener('keydown', function(event) {
        const suggestions = document.querySelectorAll('.project-suggestion-item');

        if (!suggestions.length) return;

        switch(event.key) {
            case 'ArrowDown':
                event.preventDefault();
                selectedProjectIndex = Math.min(selectedProjectIndex + 1, suggestions.length - 1);
                updateProjectSelection();
                break;
            case 'ArrowUp':
                event.preventDefault();
                selectedProjectIndex = Math.max(selectedProjectIndex - 1, -1);
                updateProjectSelection();
                break;
            case 'Enter':
                event.preventDefault();
                if (selectedProjectIndex >= 0 && suggestions[selectedProjectIndex]) {
                    const projectId = suggestions[selectedProjectIndex].getAttribute('data-project-id');
                    const projectTitle = suggestions[selectedProjectIndex].getAttribute('data-project-title');
                    selectProject(projectId, projectTitle);
                }
                break;
            case 'Escape':
                suggestionsDiv.classList.add('hidden');
                selectedProjectIndex = -1;
                break;
        }
    });

    // Обработчик клика вне области поиска
    document.addEventListener('click', function(event) {
        if (!searchInput.contains(event.target) && !suggestionsDiv.contains(event.target)) {
            suggestionsDiv.classList.add('hidden');
            selectedProjectIndex = -1;
        }
    });
}

// Обновление выделения в списке проектов
function updateProjectSelection() {
    const suggestions = document.querySelectorAll('.project-suggestion-item');
    suggestions.forEach((item, index) => {
        if (index === selectedProjectIndex) {
            item.classList.add('bg-blue-50');
        } else {
            item.classList.remove('bg-blue-50');
        }
    });
}

// Отображение проектов в списке
function displayProjects(projects) {
    const suggestionsDiv = document.getElementById('project-suggestions');
    const loadingDiv = document.getElementById('project-loading');
    const noResultsDiv = document.getElementById('project-no-results');
    const suggestionsListDiv = document.getElementById('project-suggestions-list');

    // Показываем список
    suggestionsDiv.classList.remove('hidden');
    loadingDiv.classList.add('hidden');
    selectedProjectIndex = -1;

    if (projects.length === 0) {
        // Показываем сообщение "Нет результатов"
        noResultsDiv.classList.remove('hidden');
        suggestionsListDiv.innerHTML = '';
    } else {
        // Скрываем сообщение "Нет результатов"
        noResultsDiv.classList.add('hidden');

        // Отображаем список проектов
        suggestionsListDiv.innerHTML = projects.map((project, index) => `
            <div class="px-3 py-2 cursor-pointer hover:bg-gray-100 border-b border-gray-100 last:border-b-0 project-suggestion-item"
                 data-project-id="${project.id}"
                 data-project-title="${project.title}"
                 data-index="${index}"
                 onclick="selectProject(${project.id}, '${project.title.replace(/'/g, "\\'")}')">
                <div class="font-medium">${project.title}</div>
                <div class="text-sm text-gray-500">${project.location || ''}</div>
                <div class="text-xs text-gray-400">${project.date}</div>
            </div>
        `).join('');
    }
}

// Загрузка всех проектов (без поиска)
async function loadAllProjects() {
    const suggestionsDiv = document.getElementById('project-suggestions');
    const loadingDiv = document.getElementById('project-loading');
    const noResultsDiv = document.getElementById('project-no-results');
    const suggestionsListDiv = document.getElementById('project-suggestions-list');

    // Показываем заглушку загрузки
    suggestionsDiv.classList.remove('hidden');
    loadingDiv.classList.remove('hidden');
    noResultsDiv.classList.add('hidden');
    suggestionsListDiv.innerHTML = '';
    selectedProjectIndex = -1;

    try {
        const response = await fetch('/managers/projects/autocomplete?q=', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.ok) {
            const data = await response.json();
            console.log('Все проекты загружены:', data);

            const suggestions = Array.isArray(data.suggestions) ? data.suggestions : [];
            console.log('Все проекты (массив):', suggestions);

            // Сохраняем все проекты в глобальную переменную
            allProjects = suggestions;

            // Отображаем проекты
            displayProjects(suggestions);
        }
    } catch (error) {
        console.error('Ошибка загрузки всех проектов:', error);
        loadingDiv.classList.add('hidden');
        noResultsDiv.classList.remove('hidden');
    }
}

// Выбор проекта из списка
function selectProject(projectId, projectTitle) {
    document.getElementById('add-project-search').value = projectTitle;
    document.getElementById('add-project-id').value = projectId;
    document.getElementById('project-suggestions').classList.add('hidden');
    selectedProjectIndex = -1;
}

</script>
</body>
</html>
