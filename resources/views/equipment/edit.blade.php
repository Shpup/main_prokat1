<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Редактировать оборудование</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Редактировать оборудование</h1>
        <form action="{{ route('equipment.update', $equipment) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md max-w-lg">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-600">Название</label>
                <input type="text" name="name" id="name" value="{{ $equipment->name }}" class="mt-1 block w-full border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="category_id" class="block text-sm font-medium text-gray-600">Категория</label>
                <select name="category_id" id="category_id" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option value="">Нет</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ $equipment->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-600">Описание</label>
                <textarea name="description" id="description" class="mt-1 block w-full border-gray-300 rounded-md">{{ $equipment->description }}</textarea>
            </div>
            @can('view prices')
                <div class="mb-4">
                    <label for="price" class="block text-sm font-medium text-gray-600">Цена</label>
                    <input type="number" step="0.01" name="price" id="price" value="{{ $equipment->price }}" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
            @endcan
            <div class="mb-4">
                <label for="specifications" class="block text-sm font-medium text-gray-600">Характеристики (JSON)</label>
                <textarea name="specifications" id="specifications" class="mt-1 block w-full border-gray-300 rounded-md">{{ json_encode($equipment->specifications) }}</textarea>
            </div>
            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-gray-600">Изображение</label>
                @if ($equipment->image)
                    <img src="{{ Storage::url($equipment->image) }}" alt="{{ $equipment->name }}" class="w-32 h-32 object-cover mb-2">
                @endif
                <input type="file" name="image" id="image" class="mt-1 block w-full border-gray-300 rounded-md">
            </div>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Обновить</button>
        </form>
    </div>
</div>
</body>
</html>
