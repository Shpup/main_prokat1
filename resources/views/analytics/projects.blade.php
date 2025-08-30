<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Формирование отчёта — Проекты</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@include('layouts.navigation')

<div class="container mx-auto p-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">
        Формирование отчёта — Проекты
    </h1>

    <form class="space-y-8">
        <!-- Карточка ограниченной ширины как в Продажах -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Карточка "Основные параметры" -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">
                    Основные параметры
                </h2>
                <div class="space-y-6">
                
                <!-- Период с -->
                <div>
                    <label for="period_from" class="block text-sm font-medium text-gray-600 mb-2">
                        Период с
                    </label>
                    <input type="datetime-local" 
                           id="period_from" 
                           name="period_from"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Период по -->
                <div>
                    <label for="period_to" class="block text-sm font-medium text-gray-600 mb-2">
                        Период по
                    </label>
                    <input type="datetime-local" 
                           id="period_to" 
                           name="period_to"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Мои фирмы -->
                <div>
                    <label for="companies" class="block text-sm font-medium text-gray-600 mb-2">
                        Мои фирмы
                    </label>
                    <select id="companies" 
                            name="companies"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите компанию</option>
                        <option value="all">Все компании</option>
                        <option value="company1">Компания 1</option>
                        <option value="company2">Компания 2</option>
                    </select>
                </div>

                <!-- Менеджер -->
                <div>
                    <label for="manager" class="block text-sm font-medium text-gray-600 mb-2">
                        Менеджер
                    </label>
                    <select id="manager" 
                            name="manager"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите менеджера</option>
                        <option value="all">Все менеджеры</option>
                        <option value="manager1">Менеджер 1</option>
                        <option value="manager2">Менеджер 2</option>
                    </select>
                </div>

                <!-- Бригадир -->
                <div>
                    <label for="foreman" class="block text-sm font-medium text-gray-600 mb-2">
                        Бригадир
                    </label>
                    <select id="foreman" 
                            name="foreman"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите бригадира</option>
                        <option value="all">Все бригадиры</option>
                        <option value="foreman1">Бригадир 1</option>
                        <option value="foreman2">Бригадир 2</option>
                    </select>
                </div>

                <!-- Статус -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-600 mb-2">
                        Статус
                    </label>
                    <select id="status" 
                            name="status"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите статус</option>
                        <option value="all">Все статусы</option>
                        <option value="active">Активные</option>
                        <option value="completed">Завершённые</option>
                        <option value="cancelled">Отменённые</option>
                    </select>
                </div>

                <!-- Счёт -->
                <div>
                    <label for="invoice" class="block text-sm font-medium text-gray-600 mb-2">
                        Счёт
                    </label>
                    <select id="invoice" 
                            name="invoice"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите счёт</option>
                        <option value="all">Все счета</option>
                        <option value="paid">Оплаченные</option>
                        <option value="unpaid">Неоплаченные</option>
                    </select>
                </div>

                <!-- Контрагент -->
                <div>
                    <label for="counterparty" class="block text-sm font-medium text-gray-600 mb-2">
                        Контрагент
                    </label>
                    <input type="text" 
                           id="counterparty" 
                           name="counterparty"
                           placeholder="Введите название контрагента"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Площадка -->
                <div>
                    <label for="site" class="block text-sm font-medium text-gray-600 mb-2">
                        Площадка
                    </label>
                    <input type="text" 
                           id="site" 
                           name="site"
                           placeholder="Введите название площадки"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Клиент -->
                <div>
                    <label for="client" class="block text-sm font-medium text-gray-600 mb-2">
                        Клиент
                    </label>
                    <input type="text" 
                           id="client" 
                           name="client"
                           placeholder="Введите название клиента"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                </div>
            </div>
        </div>

        <!-- Кнопка формирования отчёта -->
        <div class="flex justify-center pt-6">
            <button type="submit" 
                    class="bg-blue-600 text-white py-3 px-8 rounded-md hover:bg-blue-700 transition-colors duration-200 font-medium text-lg">
                Сформировать отчёт
            </button>
        </div>
    </form>
</div>
