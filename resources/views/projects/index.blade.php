<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Календарь проектов</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
</head>
<body class="antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')
    <div class="container mx-auto p-6">
        <div id="calendar" class="bg-white rounded-lg shadow p-4"></div>

        <!-- Модальное окно для создания проекта -->
        <div id="createProjectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Создать проект</h2>
                <form id="createProjectForm" action="{{ route('projects.store') }}" onsubmit="createProject(event)">
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
                            <option value="{{ auth()->id() }}">{{ auth()->user()->name }} (я)</option>
                            @foreach(\App\Models\User::role('manager')->where('admin_id', auth()->id())->get() as $manager)
                                @if($manager->id !== auth()->id())
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @endif
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
                    <div class="mb-4">
                        <input type="hidden" name="status" value="new">
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
    let calendar;

    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        window.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            eventTimeFormat: false,
            events: [
                    @foreach ($projects as $project)
                {
                    title: "{{ $project->id }} - {{ $project->name }} - {{ $project->admin ? $project->admin->name : 'Не указан' }}",
                    start: "{{ $project->start_date }}",
                    end: "{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->addDay()->toDateString() : null }}",
                    url: "{{ route('projects.show', $project->id) }}",
                    allDay: true,
                    color: "{{ match ($project->status) {
                    'active'    => 'rgba(34,197,94,0.71)',
                    'new'       => 'rgba(248,233,95,0.72)',
                    'completed' => 'rgba(105,159,255,0.74)',
                    'cancelled' => 'rgba(255,87,87,0.76)',
                    default     => '#9ca3af'
                } }}"
                },
                @endforeach
            ],
            dateClick: function(info) {
                @can('create projects')
                window.dispatchEvent(new CustomEvent('open-project-modal', {
                    detail: { date: info.dateStr }
                }));
                @endcan
            },});

        console.log('Calendar events:', window.calendar.getEvents()); // Отладка
        window.calendar.render();
    });

    function createProject(event) {
        event.preventDefault();
        const form = document.getElementById('createProjectForm');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            alert('Ошибка: CSRF-токен не найден.');
            return;
        }
        fetch("{{ route('projects.store') }}", {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 422) {
                        return response.json().then(data => {
                            throw new Error(Object.values(data.errors).flat().join('\n'));
                        });
                    }
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.success);
                    closeModal('createProjectModal');
                    if (calendar) {
                        console.log('Adding event with title:', data.project.id + ' - ' + data.project.name + ' - ' + (data.project.admin_name || 'Не указан'));
                        console.log('Event data:', {
                            title: data.project.id + ' - ' + data.project.name.trim() + ' - ' + (data.project.admin_name || 'Не указан'),
                            start: data.project.start_date,
                            end: data.project.end_date ? new Date(new Date(data.project.end_date).setDate(new Date(data.project.end_date).getDate() + 1)).toISOString().split('T')[0] : null
                        });
                        calendar.addEvent({
                            title: data.project.id + ' - ' + data.project.name.trim() + ' - ' + (data.project.admin_name || 'Не указан'),
                            start: data.project.start_date,
                            end: data.project.end_date ? new Date(new Date(data.project.end_date).setDate(new Date(data.project.end_date).getDate() + 1)).toISOString().split('T')[0] : null,
                            url: "{{ route('projects.show', '') }}/" + data.project.id,
                            allDay: true,
                            color: data.project.status === 'active' ? 'rgba(34,197,94,0.71)' :
                                data.project.status === 'new' ? 'rgba(248,233,95,0.72)' :
                                    data.project.status === 'completed' ? 'rgba(105,159,255,0.74)' :
                                        data.project.status === 'cancelled' ? 'rgba(255,87,87,0.76)' : '#9ca3af'
                        });
                    } else {
                        alert('Ошибка: Календарь не инициализирован.');
                    }
                } else {
                    alert('Ошибка: ' + (data.error || 'Не удалось создать проект'));
                }
            })
            .catch(error => alert('Ошибка: ' + error.message));
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
</script>
</body>
</html>
