<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Формирование отчёта — Контроль оборудования</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@include('layouts.navigation')

<div class="container mx-auto p-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">
        Формирование отчёта — Контроль оборудования
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
                
                <!-- Период -->
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-600 mb-2">
                        Период
                    </label>
                    <select id="period" 
                            name="period"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите период</option>
                        <option value="day">День</option>
                        <option value="week">Неделя</option>
                        <option value="month">Месяц</option>
                    </select>
                </div>

                <!-- Оборудование -->
                <div>
                    <label for="equipment_type" class="block text-sm font-medium text-gray-600 mb-2">
                        Оборудование
                    </label>
                    <select id="equipment_type" 
                            name="equipment_type"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите тип оборудования</option>
                        <option value="active">Задействованное</option>
                        <option value="all">Всё</option>
                        <option value="free">Свободное</option>
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
                        <option value="all">Все</option>
                        <option value="reserved">Только бронь</option>
                        <option value="in_work">В работе</option>
                    </select>
                </div>

                <!-- Показать вернувшееся с площадки -->
                <div>
                    <label for="returned" class="block text-sm font-medium text-gray-600 mb-2">
                        Показать вернувшееся с площадки
                    </label>
                    <select id="returned" 
                            name="returned"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Выберите опцию</option>
                        <option value="yes">Да</option>
                        <option value="no">Нет</option>
                    </select>
                </div>
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
