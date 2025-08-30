<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Финансовый отчёт</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@include('layouts.navigation')

<div class="container mx-auto p-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">
        Финансовый отчёт
    </h1>

    <!-- Карточка с заглушкой -->
    <div class="bg-white rounded-lg shadow-lg p-12">
        <div class="text-center">
            <!-- Иконка -->
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>

            <!-- Текст заглушки -->
            <div class="text-lg text-gray-600">
                👉 Здесь пока ничего нет. В дальнейшем появится функционал.
            </div>

            <!-- Дополнительное описание -->
            <div class="mt-4 text-sm text-gray-500">
                Раздел находится в разработке. Скоро здесь будут доступны детальные финансовые отчёты.
            </div>
        </div>
    </div>
</div>
