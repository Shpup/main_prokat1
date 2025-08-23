<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Добавить оборудование</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Добавить оборудование</h1>
        <form action="{{ route('equipment.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md max-w-lg">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-600">Название</label>
                <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="category_id" class="block text-sm font-medium text-gray-600">Категория</label>
                <select name="category_id" id="category_id" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option value="">Нет</option>
                    @foreach (\App\Models\Category::where('admin_id', auth()->user()->hasRole('admin') ? auth()->id() : auth()->user()->admin_id)->get() as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-600">Описание</label>
                <textarea name="description" id="description" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
            </div>
            @can('view prices')
                <div class="mb-4">
                    <label for="price" class="block text-sm font-medium text-gray-600">Цена</label>
                    <input type="number" step="0.01" name="price" id="price" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
            @endcan
            
            <!-- Характеристики -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-2">Характеристики</label>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="length_cm" class="block text-xs font-medium text-gray-500">Длина (см)</label>
                        <input type="text" name="length_cm" id="length_cm" placeholder="например: 120" class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label for="width_cm" class="block text-xs font-medium text-gray-500">Ширина (см)</label>
                        <input type="text" name="width_cm" id="width_cm" placeholder="например: 60" class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label for="height_cm" class="block text-xs font-medium text-gray-500">Высота (см)</label>
                        <input type="text" name="height_cm" id="height_cm" placeholder="например: 80" class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label for="weight_kg" class="block text-xs font-medium text-gray-500">Вес (кг)</label>
                        <input type="text" name="weight_kg" id="weight_kg" placeholder="например: 7.5" class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label for="power_w" class="block text-xs font-medium text-gray-500">Мощность (Вт)</label>
                        <input type="text" name="power_w" id="power_w" placeholder="например: 350" class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label for="current_a" class="block text-xs font-medium text-gray-500">Ток (А)</label>
                        <input type="text" name="current_a" id="current_a" placeholder="например: 1.2" class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-gray-600">Изображение</label>
                <input type="file" name="image" id="image" class="mt-1 block w-full border-gray-300 rounded-md">
            </div>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Добавить</button>
        </form>
    </div>
</div>
</body>
</html>
