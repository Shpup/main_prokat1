<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Проект: {{ $project->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Проект: {{ $project->name }}</h1>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Детали проекта</h2>
            <p><strong>Описание:</strong> {{ $project->description ?? 'Не указано' }}</p>
            <p><strong>Менеджер:</strong> {{ $project->manager->name ?? 'Не назначен' }}</p>
            <p><strong>Даты:</strong> {{ $project->start_date }} - {{ $project->end_date ?? 'Не указано' }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Смета</h2>
            <table class="min-w-full">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Оборудование</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Статус</th>
                    @can('view prices')
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Цена</th>
                    @endcan
                </tr>
                </thead>
                <tbody>
                @foreach ($project->equipment as $item)
                    <tr class="border-t">
                        <td class="px-6 py-4">{{ $item->name }}</td>
                        <td class="px-6 py-4">{{ $item->pivot->status }}</td>
                        @can('view prices')
                            <td class="px-6 py-4">{{ $item->price ? number_format($item->price, 2) : 'Не указана' }}</td>
                        @endcan
                    </tr>
                @endforeach
                </tbody>
                @can('view prices')
                    <tfoot>
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-right font-semibold">Итого:</td>
                        <td class="px-6 py-4 font-semibold">
                            {{ number_format($project->equipment->sum('price'), 2) }}
                        </td>
                    </tr>
                    </tfoot>
                @endcan
            </table>
        </div>

        @can('edit projects')
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Добавить оборудование</h2>
                <form action="{{ route('projects.addEquipment', $project) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="equipment_id" class="block text-sm font-medium text-gray-600">Оборудование</label>
                        <select name="equipment_id" id="equipment_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            @foreach ($availableEquipment as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-600">Статус</label>
                        <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            <option value="on_stock">На складе</option>
                            <option value="assigned">Назначено</option>
                            <option value="used">Использовано</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                        Добавить
                    </button>
                </form>
            </div>
        @endcan
    </div>
</div>
</body>
</html>
