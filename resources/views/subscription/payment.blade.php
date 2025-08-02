<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Оплата подписки</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white p-6 rounded-lg shadow-md max-w-md w-full">
        <h1 class="text-xl font-semibold text-gray-800 mb-4">Оформите подписку</h1>
        <p class="text-gray-600 mb-6">Для доступа к платформе необходимо активировать подписку.</p>
        <form action="{{ route('subscription.activate') }}" method="POST">
            @csrf
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">
                Активировать подписку (тест)
            </button>
        </form>
    </div>
</div>
</body>
</html>
