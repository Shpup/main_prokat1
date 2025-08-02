<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Управление пользователями</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .user-table { width: 100%; border-collapse: collapse; }
        .user-table th, .user-table td { padding: 0.5rem; border: 1px solid #ddd; text-align: left; }
        .user-table th { background-color: #f3f4f6; }
        .permission-checkbox { margin: 0.25rem; }
    </style>
</head>
<body class="antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Управление пользователями</h1>
        <table class="user-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Email</th>
                <th>Роли</th>
                <th>Разрешения</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @foreach ($user->roles as $role)
                            <span class="inline-block bg-gray-200 rounded-full px-2 py-1 text-sm mr-1">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        <form id="permissionForm-{{ $user->id }}" class="permission-form">
                            @csrf
                            @foreach ($permissions as $permission)
                                <label class="permission-checkbox">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                        {{ $user->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                    {{ $permission->name }}
                                </label>
                            @endforeach
                        </form>
                    </td>
                    <td>
                        <button onclick="savePermissions({{ $user->id }})" class="bg-blue-600 text-white py-1 px-2 rounded-md hover:bg-blue-700">Сохранить</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    // Глобальная функция savePermissions
    function savePermissions(userId) {
        const form = document.getElementById(`permissionForm-${userId}`);
        if (!form) {
            console.error('Форма не найдена для userId:', userId);
            alert('Ошибка: Форма не найдена');
            return;
        }
        const formData = new FormData(form);
        const token = document.querySelector('meta[name="csrf-token"]').content;

        fetch(`/users/${userId}/permissions`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка при обновлении разрешений');
                }
                return response.json();
            })
            .then(data => {
                alert(data.success);
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Ошибка: ' + error.message);
            });
    }

    // Инициализация при загрузке страницы (опционально)
    document.addEventListener('DOMContentLoaded', function() {
        // Здесь можно добавить дополнительную логику, если потребуется
    });
</script>
</body>
</html>
