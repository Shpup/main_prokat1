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
                <label for="description" class="block text-sm font-medium text-gray-600">Описание</label>
                <textarea name="description" id="description" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
            </div>
            @can('view prices')
                <div class="mb-4">
                    <label for="price" class="block text-sm font-medium text-gray-600">Цена</label>
                    <input type="number" step="0.01" name="price" id="price" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
            @endcan
            <div class="mb-4">
                <label for="specifications" class="block text-sm font-medium text-gray-600">Характеристики (JSON)</label>
                <textarea name="specifications" id="specifications" class="mt-1 block w-full border-gray-300 rounded-md" placeholder='{"weight": "10kg", "size": "100x50cm"}'></textarea>
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
