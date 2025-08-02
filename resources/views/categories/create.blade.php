<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Добавить категорию</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Добавить категорию</h1>
        <form action="{{ route('categories.store') }}" method="POST" class="bg-white p-6 rounded-lg shadow-md max-w-lg">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-600">Название</label>
                <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="parent_id" class="block text-sm font-medium text-gray-600">Родительская категория</label>
                <select name="parent_id" id="parent_id" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option value="">Нет</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Создать</button>
        </form>
    </div>
</div>
</body>
</html>
