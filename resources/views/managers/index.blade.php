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
        <button onclick="openCreateModal()" class="mb-6 inline-block bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
            Добавить сотрудника
        </button>

        <!-- Таблица сотрудников -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <!-- Имя -->
                            <th class="px-6 py-3 text-left">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900">Имя</span>
                                    <button onclick="sortTable('name')" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </button>
                                </div>
                                <input type="text" id="search-name" placeholder="Поиск по имени..." 
                                       class="mt-2 w-full px-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </th>
                            
                            <!-- Email -->
                            <th class="px-6 py-3 text-left">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900">Email</span>
                                    <button onclick="sortTable('email')" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </button>
                                </div>
                                <input type="text" id="search-email" placeholder="Поиск по email..." 
                                       class="mt-2 w-full px-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </th>
                            
                            <!-- Роль -->
                            <th class="px-6 py-3 text-left">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900">Роль</span>
                                    <button onclick="sortTable('role')" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </button>
                                </div>
                                <input type="text" id="search-role" placeholder="Поиск по роли..." 
                                       class="mt-2 w-full px-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </th>
                            
                            <!-- Телефон -->
                            <th class="px-6 py-3 text-left">
                                <span class="text-sm font-medium text-gray-900">Телефон</span>
                            </th>
                            
                            <!-- Статус -->
                            <th class="px-6 py-3 text-left">
                                <span class="text-sm font-medium text-gray-900">Статус</span>
                            </th>
                            
                            <!-- Действия -->
                            <th class="px-6 py-3 text-left">
                                <span class="text-sm font-medium text-gray-900">Действия</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="employees-tbody">
                        @foreach ($employees as $employee)
                            <tr class="border-t border-gray-200 hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $employee->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $employee->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $employee->roles->first() ? $employee->roles->first()->name : 'Не назначена' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $employee->phone ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <select onchange="updateStatus({{ $employee->id }}, this.value)" 
                                            class="text-sm border border-gray-300 rounded-md px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[180px]">
                                        <option value="free" {{ ($employee->employeeStatus?->status ?? 'free') === 'free' ? 'selected' : '' }}>Свободен</option>
                                        <option value="unavailable" {{ ($employee->employeeStatus?->status ?? 'free') === 'unavailable' ? 'selected' : '' }}>Недоступен</option>
                                        <option value="assigned" {{ ($employee->employeeStatus?->status ?? 'free') === 'assigned' ? 'selected' : '' }}>Назначен на проекты</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <!-- Комментарий к статусу (красный восклицательный знак) -->
                                        @if(($employee->employeeStatus?->status ?? 'free') === 'unavailable')
                                            <button onclick="openStatusCommentModal({{ $employee->id }})" 
                                                    class="text-red-600 hover:text-red-800" title="Комментарий к статусу">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                            </button>
                                        @endif
                                        
                                        <!-- Назначен на проекты (смайлик) -->
                                        <button onclick="showAssignments({{ $employee->id }})" 
                                                class="text-blue-600 hover:text-blue-800" title="Назначен на проекты">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                        
                                        <!-- Редактировать (карандаш) -->
                                        <button onclick="openEditModal({{ $employee->id }})" 
                                                class="text-gray-600 hover:text-gray-800" title="Редактировать">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        
                                        <!-- Добавить в проект (плюс) -->
                                        <button onclick="openAddToProjectModal({{ $employee->id }})" 
                                                class="text-green-600 hover:text-green-800" title="Добавить в проект">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                        
                                        <!-- Удалить (корзина) -->
                                        <button onclick="deleteEmployee({{ $employee->id }})" 
                                                class="text-red-600 hover:text-red-800" title="Удалить">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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

        <!-- Пустое состояние -->
        @if($employees->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет сотрудников</h3>
                <p class="mt-1 text-sm text-gray-500">Начните с добавления первого сотрудника.</p>
            </div>
        @endif
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
                <input type="text" name="name" id="create-name" class="mt-1 block w-full border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="create-phone" class="block text-sm font-medium text-gray-600">Телефон</label>
                <input type="text" name="phone" id="create-phone" class="mt-1 block w-full border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
                <label for="create-role" class="block text-sm font-medium text-gray-600">Роль *</label>
                <select name="role" id="create-role" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    <option value="">Выберите роль</option>
                    <option value="нет специальности">Пользователь</option>
                    <option value="admin">Админ</option>
                    <option value="manager">Менеджер</option>
                    <option value="driver">Грузчик</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="create-email" class="block text-sm font-medium text-gray-600">Email *</label>
                <input type="email" name="email" id="create-email" class="mt-1 block w-full border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="create-password" class="block text-sm font-medium text-gray-600">Пароль *</label>
                <input type="password" name="password" id="create-password" class="mt-1 block w-full border-gray-300 rounded-md" required>
            </div>
            <div class="mb-6">
                <label for="create-password-confirmation" class="block text-sm font-medium text-gray-600">Подтверждение пароля *</label>
                <input type="password" name="password_confirmation" id="create-password-confirmation" class="mt-1 block w-full border-gray-300 rounded-md" required>
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
                <input type="text" name="name" id="edit-name" class="mt-1 block w-full border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="edit-phone" class="block text-sm font-medium text-gray-600">Телефон</label>
                <input type="text" name="phone" id="edit-phone" class="mt-1 block w-full border-gray-300 rounded-md">
            </div>
            <div class="mb-6">
                <label for="edit-role" class="block text-sm font-medium text-gray-600">Роль *</label>
                <select name="role" id="edit-role" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    <option value="">Выберите роль</option>
                    <option value="нет специальности">Пользователь</option>
                    <option value="admin">Админ</option>
                    <option value="manager">Менеджер</option>
                    <option value="driver">Грузчик</option>
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
    <div class="bg-white rounded-lg shadow-lg border w-11/12 max-w-lg">
        <div class="p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Добавление персонала на мероприятие</h3>
                <button onclick="closeModal('addToProjectModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="addToProjectForm">
                <input type="hidden" id="add-employee-id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Проект</label>
                    <select id="add-project" class="w-full rounded-md border border-gray-300 shadow-sm focus:outline-none" required>
                        <option value="">Выберите проект</option>
                        @foreach($projects ?? [] as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end space-x-3 pt-3">
                    <button type="button" onclick="closeModal('addToProjectModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
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

<!-- Модальное окно комментария к статусу -->
<div id="statusCommentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Комментарий к статусу</h2>
        <form id="statusCommentForm">
            @csrf
            <input type="hidden" id="comment-employee-id">
            <div class="mb-6">
                <label for="status-comment" class="block text-sm font-medium text-gray-600">Комментарий (причина недоступности) *</label>
                <textarea name="comment" id="status-comment" rows="4" class="mt-1 block w-full border-gray-300 rounded-md" required></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('statusCommentModal')" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Отмена</button>
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно назначений -->
<div id="assignmentsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[80vh] flex flex-col">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Назначен на проекты</h2>
        <div id="assignments-list" class="flex-1 overflow-y-auto mb-6">
            <!-- Список назначений будет загружен динамически -->
        </div>
        <div class="flex justify-end border-t pt-4">
            <button onclick="closeModal('assignmentsModal')" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Закрыть</button>
        </div>
    </div>
</div>

<!-- Модальное окно для подтверждения удаления -->
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

<!-- Toast уведомления -->
<div id="toast" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-md shadow-lg transform translate-x-full transition-transform duration-300 z-50">
    <span id="toast-message"></span>
</div>

<script>
// Глобальные переменные
let currentSort = { field: 'name', order: 'asc' };
let searchTimeout;

// Инициализация
document.addEventListener('DOMContentLoaded', function() {
    setupSearchDebounce();
    setupFormSubmissions();
});

// Настройка debounce для поиска
function setupSearchDebounce() {
    const searchInputs = ['search-name', 'search-email', 'search-role'];
    
    searchInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    performSearch();
                }, 300);
            });
        }
    });
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
    // Загрузить данные сотрудника
    fetch(`/managers/${employeeId}`)
        .then(response => response.json())
        .then(data => {
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
}

function openStatusCommentModal(employeeId) {
    document.getElementById('comment-employee-id').value = employeeId;
    document.getElementById('statusCommentModal').classList.remove('hidden');
}

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
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
            showToast(data.success);
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
    
    fetch(`/managers/${employeeId}`, {
        method: 'PATCH',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.success);
            closeModal('editEmployeeModal');
            loadEmployees(); // Обновляем таблицу без перезагрузки
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
                showToast(data.success);
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

function updateStatus(employeeId, status) {
    fetch(`/managers/${employeeId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.success);
            loadEmployees(); // Обновляем таблицу без перезагрузки
        } else {
            showToast('Ошибка при обновлении статуса: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка при обновлении статуса:', error);
        showToast('Ошибка при обновлении статуса');
    });
}

function addToProject() {
    const employeeId = document.getElementById('add-employee-id').value;
    const projectId = document.getElementById('add-project').value;
    
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
            showToast(data.success);
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
            showToast(data.success);
            closeModal('statusCommentModal');
            loadEmployees(); // Обновляем таблицу без перезагрузки
        } else {
            showToast('Ошибка при сохранении комментария: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка при сохранении комментария:', error);
        showToast('Ошибка при сохранении комментария');
    });
}

function showAssignments(employeeId) {
    fetch(`/managers/${employeeId}/assignments`)
        .then(response => response.json())
        .then(assignments => {
            const list = document.getElementById('assignments-list');
            if (assignments.length === 0) {
                list.innerHTML = '<p class="text-gray-500 text-center py-8">Нет назначений</p>';
            } else {
                list.innerHTML = assignments.map((assignment, index) => {
                    const hasUrl = assignment.project_url && assignment.project_url !== '';
                    const buttonClass = hasUrl 
                        ? 'bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600' 
                        : 'bg-gray-300 text-gray-500 px-3 py-1 rounded text-sm cursor-not-allowed';
                    
                    return `
                        <div class="border-b border-gray-200 pb-4 mb-4 last:border-b-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 truncate" title="${assignment.project_name}">
                                        ${assignment.project_name}
                                    </h3>
                                    <div class="mt-2 text-sm text-gray-600">
                                        <div>Начало: ${assignment.start_date || 'не установлено'}</div>
                                        <div>Конец: ${assignment.end_date || 'не установлен'}</div>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    ${hasUrl 
                                        ? `<a href="${assignment.project_url}" class="${buttonClass}">Перейти в проект</a>`
                                        : `<button class="${buttonClass}" title="Ссылка недоступна" disabled>Перейти в проект</button>`
                                    }
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
function performSearch() {
    const name = document.getElementById('search-name').value;
    const email = document.getElementById('search-email').value;
    const role = document.getElementById('search-role').value;
    
    const params = new URLSearchParams();
    if (name) params.append('query[name]', name);
    if (email) params.append('query[email]', email);
    if (role) params.append('query[role]', role);
    if (currentSort.field) params.append('sort', currentSort.field);
    if (currentSort.order) params.append('order', currentSort.order);
    
    fetch(`/managers?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        updateTable(data);
    });
}

function sortTable(field) {
    if (currentSort.field === field) {
        currentSort.order = currentSort.order === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.field = field;
        currentSort.order = 'asc';
    }
    
    performSearch();
}

function updateTable(employees) {
    const tbody = document.getElementById('employees-tbody');
    tbody.innerHTML = employees.map(employee => `
        <tr class="border-t border-gray-200 hover:bg-gray-50">
            <td class="px-6 py-4 text-sm text-gray-900">${employee.name}</td>
            <td class="px-6 py-4 text-sm text-gray-900">${employee.email}</td>
            <td class="px-6 py-4 text-sm text-gray-900">${employee.roles[0] ? employee.roles[0].name : 'Не назначена'}</td>
            <td class="px-6 py-4 text-sm text-gray-900">${employee.phone || '-'}</td>
            <td class="px-6 py-4">
                                    <select onchange="updateStatus(${employee.id}, this.value)"
                            class="text-sm border border-gray-300 rounded-md px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[180px]">
                        <option value="free" ${(employee.employee_status && employee.employee_status.status === 'free') ? 'selected' : ''}>Свободен</option>
                        <option value="unavailable" ${(employee.employee_status && employee.employee_status.status === 'unavailable') ? 'selected' : ''}>Недоступен</option>
                        <option value="assigned" ${(employee.employee_status && employee.employee_status.status === 'assigned') ? 'selected' : ''}>Назначен на проекты</option>
                    </select>
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center space-x-2">
                    ${(employee.employee_status && employee.employee_status.status === 'unavailable') ? `
                        <button onclick="openStatusCommentModal(${employee.id})"
                                class="text-red-600 hover:text-red-800" title="Комментарий к статусу">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    ` : ''}
                    <button onclick="showAssignments(${employee.id})" 
                            class="text-blue-600 hover:text-blue-800" title="Назначен на проекты">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <button onclick="openEditModal(${employee.id})" 
                            class="text-gray-600 hover:text-gray-800" title="Редактировать">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="openAddToProjectModal(${employee.id})" 
                            class="text-green-600 hover:text-green-800" title="Добавить в проект">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </button>
                    <button onclick="deleteEmployee(${employee.id})" 
                            class="text-red-600 hover:text-red-800" title="Удалить">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Функция загрузки сотрудников без перезагрузки
function loadEmployees() {
    const name = document.getElementById('search-name')?.value || '';
    const email = document.getElementById('search-email')?.value || '';
    const role = document.getElementById('search-role')?.value || '';
    
    const params = new URLSearchParams();
    if (name) params.append('query[name]', name);
    if (email) params.append('query[email]', email);
    if (role) params.append('query[role]', role);
    if (currentSort.field) params.append('sort', currentSort.field);
    if (currentSort.order) params.append('order', currentSort.order);
    
    console.log('Загружаем сотрудников с параметрами:', params.toString());
    
    fetch(`/managers?${params.toString()}`, {
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
        updateTable(data);
    })
    .catch(error => {
        console.error('Ошибка при загрузке сотрудников:', error);
        showToast('Ошибка при загрузке данных');
    });
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
</script>
</body>
</html>
