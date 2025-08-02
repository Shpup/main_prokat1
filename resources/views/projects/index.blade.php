<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Календарь проектов</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
</head>
<body class="antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Календарь проектов</h1>
        <div id="calendar" class="bg-white rounded-lg shadow p-4"></div>

        <!-- Модальное окно для создания проекта -->
        <div id="createProjectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Создать проект</h2>
                <form id="createProjectForm" action="{{ route('projects.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-600">Название</label>
                        <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-600">Описание</label>
                        <textarea name="description" id="description" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="manager_id" class="block text-sm font-medium text-gray-600">Менеджер</label>
                        <select name="manager_id" id="manager_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            @foreach (\App\Models\User::role('manager')->get() as $manager)
                                <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="start_date" class="block text-sm font-medium text-gray-600">Дата начала</label>
                        <input type="date" name="start_date" id="start_date" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="end_date" class="block text-sm font-medium text-gray-600">Дата окончания</label>
                        <input type="date" name="end_date" id="end_date" class="mt-1 block w-full border-gray-300 rounded-md">
                    </div>
                    <div class="flex justify-end">
                        <button type="button" onclick="closeModal('createProjectModal')" class="mr-2 bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Отмена</button>
                        <button type="submit" class="bg-blue-600 text-black py-2 px-4 rounded-md hover:bg-blue-700">Создать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: [
                    @foreach ($projects as $project)
                {
                    title: "{{ $project->name }}",
                    start: "{{ $project->start_date }}",
                    end: "{{ $project->end_date }}",
                    url: "{{ route('projects.show', $project->id) }}"
                },
                @endforeach
            ],
            dateClick: function(info) {
                @can('create projects')
                document.getElementById('start_date').value = info.dateStr;
                document.getElementById('createProjectModal').classList.remove('hidden');
                @endcan
            }
        });
        calendar.render();
    });

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
</script>
</body>
</html>
