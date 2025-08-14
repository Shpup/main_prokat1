<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Управление пользователями</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            z-index: 1000;
            transform: translateY(100%);
            transition: transform 0.3s ease-in-out;
        }
        #toast.show {
            transform: translateY(0);
        }
        #toast.error {
            background-color: #ef4444;
        }
        #toast.success {
            background-color: #22c55e;
        }
        .permission-chip {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .permission-chip.active {
            background-color: #22c55e;
            color: white;
        }
        .permission-chip.inactive {
            background-color: #e5e7eb;
            color: #4b5563;
        }
        .sort-icon::after {
            content: '';
            display: inline-block;
            width: 0;
            height: 0;
            margin-left: 0.25rem;
            vertical-align: middle;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
        }
        .sort-icon.asc::after {
            border-bottom: 4px solid #4b5563;
        }
        .sort-icon.desc::after {
            border-top: 4px solid #4b5563;
        }
    </style>
</head>
<body class="antialiased">
<div class="min-h-screen bg-white">
    @include('layouts.navigation')
    <div class="p-6 w-full">
        <div id="toast" class="hidden">
            <span id="toastMessage"></span>
        </div>
        <!-- Фильтры -->
        <div class="mb-6 bg-white rounded-lg p-4">
            <form id="filterForm" action="{{ route('users.index') }}" method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label for="name" class="block text-sm font-medium text-gray-700">Имя</label>
                    <input type="text" name="name" id="name" value="{{ request('name') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Поиск по имени">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="text" name="email" id="email" value="{{ request('email') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Поиск по email">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="role" class="block text-sm font-medium text-gray-700">Роль</label>
                    <select name="role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Все роли</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Фильтровать</button>
                    <a href="{{ route('users.index') }}" class="bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Сбросить</a>
                </div>
            </form>
        </div>
        <!-- Таблица -->
        <div class="bg-white rounded-lg overflow-hidden w-full">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ route('users.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => $sortColumn === 'id' && $sortDirection === 'asc' ? 'desc' : 'asc'])) }}" class="sort-icon {{ $sortColumn === 'id' ? $sortDirection : '' }}">ID</a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ route('users.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $sortColumn === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc'])) }}" class="sort-icon {{ $sortColumn === 'name' ? $sortDirection : '' }}">Имя</a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ route('users.index', array_merge(request()->query(), ['sort' => 'email', 'direction' => $sortColumn === 'email' && $sortDirection === 'asc' ? 'desc' : 'asc'])) }}" class="sort-icon {{ $sortColumn === 'email' ? $sortDirection : '' }}">Email</a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ route('users.index', array_merge(request()->query(), ['sort' => 'roles_count', 'direction' => $sortColumn === 'roles_count' && $sortDirection === 'asc' ? 'desc' : 'asc'])) }}" class="sort-icon {{ $sortColumn === 'roles_count' ? $sortDirection : '' }}">Роли</a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Разрешения</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @foreach ($user->roles as $role)
                                <span class="inline-block bg-gray-200 rounded-full px-2 py-1 text-xs font-medium text-gray-700 mr-1">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="flex flex-wrap gap-2" id="permissions-{{ $user->id }}">
                                @foreach ($permissions as $permission)
                                    <span
                                        class="permission-chip inline-block rounded-full px-3 py-1 text-xs font-medium {{ $user->hasPermissionTo($permission->name) ? 'active' : 'inactive' }}"
                                        data-user-id="{{ $user->id }}"
                                        data-permission="{{ $permission->name }}"
                                        onclick="togglePermission({{ $user->id }}, '{{ $permission->name }}')"
                                    >
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Нет пользователей, привязанных к вашему аккаунту.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function showToast(message, isError = false) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        toastMessage.textContent = message;
        toast.className = `show ${isError ? 'error' : 'success'}`;
        setTimeout(() => {
            toast.className = 'hidden';
        }, 3000);
    }

    function togglePermission(userId, permission) {
        const chip = document.querySelector(`#permissions-${userId} [data-permission="${permission}"]`);
        if (!chip) {
            console.error('Чип разрешения не найден:', { userId, permission });
            showToast('Ошибка: Разрешение не найдено', true);
            return;
        }

        const isActive = chip.classList.contains('active');
        const permissions = Array.from(document.querySelectorAll(`#permissions-${userId} .permission-chip.active`))
            .map(chip => chip.dataset.permission)
            .filter(p => p !== permission);
        if (!isActive) {
            permissions.push(permission);
        }

        chip.classList.toggle('active');
        chip.classList.toggle('inactive');

        console.log('Sending updatePermissions request', { userId, permissions });

        fetch(`/users/${userId}/permissions`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ permissions })
        })
            .then(response => {
                if (!response.ok) {
                    chip.classList.toggle('active');
                    chip.classList.toggle('inactive');
                    throw new Error(response.status === 403 ? 'У вас нет прав для изменения разрешений' : 'Ошибка при обновлении разрешений');
                }
                return response.json();
            })
            .then(data => {
                console.log('updatePermissions response', data);
                showToast(data.success || 'Разрешения обновлены');
            })
            .catch(error => {
                console.error('Ошибка:', error);
                showToast('Ошибка: ' + error.message, true);
                chip.classList.toggle('active');
                chip.classList.toggle('inactive');
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Инициализация фильтров
        const filterForm = document.getElementById('filterForm');
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(filterForm);
            const query = new URLSearchParams(formData).toString();
            window.location.href = `{{ route('users.index') }}?${query}`;
        });
    });
</script>
</body>
</html>
