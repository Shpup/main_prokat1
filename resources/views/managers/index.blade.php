<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Менеджеры</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Менеджеры</h1>
        <button onclick="document.getElementById('createManagerModal').classList.remove('hidden')" class="mb-4 inline-block bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
            Добавить менеджера
        </button>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Имя</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Email</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Подписка</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($managers as $manager)
                    <tr class="border-t">
                        <td class="px-6 py-4">{{ $manager->name }}</td>
                        <td class="px-6 py-4">{{ $manager->email }}</td>
                        <td class="px-6 py-4">{{ $manager->hasActiveSubscription() ? 'Активна' : 'Не активна' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Модальное окно для создания менеджера -->
        <div id="createManagerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Добавить менеджера</h2>
                <form id="createManagerForm" action="{{ route('managers.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-600">Имя</label>
                        <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-600">Email</label>
                        <input type="email" name="email" id="email" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-600">Пароль</label>
                        <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-600">Подтверждение пароля</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" onclick="closeModal('createManagerModal')" class="mr-2 bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Отмена</button>
                        <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Создать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
</script>
</body>
</html>
