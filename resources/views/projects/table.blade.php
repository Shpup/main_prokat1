<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Список проектов</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .table-scroll {
            overflow-x: auto;
            max-width: 100%;
        }
        #projectsTable {
            table-layout: fixed;
            width: 100%;
        }
        #projectsTable th, #projectsTable td {
            min-width: 0;
            word-wrap: break-word;
        }
        #projectsTable th {
            padding: 0.75rem 1rem;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        #projectsTable td {
            padding: 1rem 1rem;
            white-space: nowrap;
            text-align: center;
            font-size: 0.875rem;
            color: #111827;
        }
        #toast {
            z-index: 9999;
        }
        [x-cloak] {
            display: none !important;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        #calendarTab {
            width: 100%;
            height: calc(100vh - 9rem);
            overflow: hidden;
            box-sizing: border-box;
        }
        #calendar {
            width: 100%;
            height: 100%;
            box-sizing: border-box;
        }
        .fc {
            width: 100% !important;
            height: 100% !important;
        }
        .fc-event-title {
            white-space: normal !important;
            word-wrap: break-word;
            line-height: 1.2;
            padding: 2px 0;
            max-height: 3.6em; /* Ограничиваем высоту для 2 строк, можно настроить */
            overflow: hidden; /* Скрываем лишний текст, если нужно */
        }

        .fc-scroller {
            overflow-y: auto !important;
        }
        .bg-white.shadow.rounded-lg {
            height: 100vh;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        @media (max-width: 640px) {
            #projectsTable th:nth-child(n+3):nth-child(-n+5),
            #projectsTable td:nth-child(n+3):nth-child(-n+5) {
                display: none;
            }
            #calendarTab {
                height: calc(100vh - 10rem);
            }
        }
        select.status-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            border: none;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 9999px;
            text-align: center;
            cursor: pointer;
            width: 100px;
            display: inline-block;
            background-image: url('data:image/svg+xml;utf8,<svg fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1em;
        }
        select.status-select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="bg-white shadow rounded-lg" x-data="projectManager()" x-init="init()" @open-project-modal.window="openProjectModal({ start_date: $event.detail.date })" @update-status.window="updateStatus($event.detail.projectId, $event.detail.status, $event.detail.element)">
    @include('layouts.navigation')

    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
            <div class="px-4 sm:px-6 py-4">
                <button
                    class="tab-button px-4 py-2 text-sm font-medium rounded-md focus:outline-none"
                    :class="{ 'bg-blue-500 text-white': activeTab === 'calendar', 'bg-gray-200 text-gray-700': activeTab !== 'calendar' }"
                    @click="openTab('calendar')"
                >
                    Календарь
                </button>
                <button
                    class="tab-button px-4 py-2 text-sm font-medium rounded-md focus:outline-none"
                    :class="{ 'bg-blue-500 text-white': activeTab === 'table', 'bg-gray-200 text-gray-700': activeTab !== 'table' }"
                    @click="openTab('table')"
                >
                    Таблица проектов
                </button>
            </div>

        </div>
    </div>

    <!-- Вкладка таблицы проектов -->
    <div id="projectsTab" class="tab-content px-4 sm:px-6 py-4 sm:py-6">
        <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
            <input type="text" x-model="filters.name" @input="applyFilters()"
                   placeholder="Поиск по названию..." class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm">
            <input type="text" x-model="filters.description" @input="applyFilters()"
                   placeholder="Поиск по описанию..." class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm">
            <input type="text" x-model="filters.manager" @input="applyFilters()"
                   placeholder="Поиск по менеджеру..." class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm">
            <input type="date" x-model="filters.start_date" @input="applyFilters()"
                   class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm">
            <input type="date" x-model="filters.end_date" @input="applyFilters()"
                   class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm">
            <select x-model="filters.status" @change="applyFilters()" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-sm">
                <option value="">Все статусы</option>
                <option value="new">Новый</option>
                <option value="active">Активен</option>
                <option value="completed">Завершен</option>
                <option value="cancelled">Отменен</option>
            </select>
            <button @click="openProjectModal()" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md hover:bg-blue-600">
                Добавить проект
            </button>
        </div>
        <div class="table-scroll">
            <table class="divide-y divide-gray-200 w-full" id="projectsTable">
                <thead class="bg-gray-50">
                <tr>
                    <th class="w-1/7 min-w-[150px] cursor-pointer" @click="sortBy('name')">Название</th>
                    <th class="w-1/7 min-w-[120px] cursor-pointer" @click="sortBy('description')">Описание</th>
                    <th class="w-1/7 min-w-[100px] cursor-pointer" @click="sortBy('manager_name')">Менеджер</th>
                    <th class="w-1/7 min-w-[120px] cursor-pointer" @click="sortBy('start_date')">Дата начала</th>
                    <th class="w-1/7 min-w-[100px] cursor-pointer" @click="sortBy('end_date')">Дата окончания</th>
                    <th class="w-1/7 min-w-[100px] cursor-pointer" @click="sortBy('status')">Статус</th>
                    <th class="w-1/7 min-w-[120px]">Действия</th>
                </tr>
                </thead>
                <tbody id="projects-table" class="bg-white divide-y divide-gray-200">
                <template x-for="project in filteredProjects" :key="project.id">
                    <tr>
                        <td x-text="project.name"></td>
                        <td x-text="project.description"></td>
                        <td x-text="project.manager_name"></td>
                        <td x-text="project.start_date"></td>
                        <td x-text="project.end_date || ''"></td>
                        <td>
                            <select
                                class="status-select"
                                :class="{
                                        'bg-green-100 text-green-800': project.status === 'active',
                                        'bg-yellow-100 text-yellow-800': project.status === 'new',
                                        'bg-blue-100 text-blue-800': project.status === 'completed',
                                        'bg-red-100 text-red-800': project.status === 'cancelled',
                                        'bg-gray-100 text-gray-800': !['active', 'new', 'completed', 'cancelled'].includes(project.status)
                                    }"
                                @change="updateStatus(project.id, $event.target.value, $event.target)"
                            >
                                <option value="new" :selected="project.status === 'new'">Новый</option>
                                <option value="active" :selected="project.status === 'active'">Активен</option>
                                <option value="completed" :selected="project.status === 'completed'">Завершен</option>
                                <option value="cancelled" :selected="project.status === 'cancelled'">Отменен</option>
                            </select>
                        </td>
                        <td>
                            <a :href="'{{ route('projects.show', '') }}/' + project.id" class="text-blue-600 hover:text-blue-800 mr-2">Перейти</a>
                            <button @click="deleteProject(project.id)" class="text-red-600 hover:text-red-800">Удалить</button>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Вкладка календаря -->
    <div id="calendarTab" class="tab-content active px-4 sm:px-6 py-4 sm:py-6">
        <div id="calendar" class="bg-white rounded-lg shadow p-4"></div>
    </div>

    <!-- Модальное окно для создания проекта -->
    <div
        x-show="isProjectModalOpen"
        x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
        @click.self="closeProjectModal()"
        @keydown.window.escape="closeProjectModal()"
        x-transition.opacity
    >
        <div
            class="bg-white rounded-lg shadow-lg border w-11/12 max-w-md max-h-[90vh] overflow-y-auto"
            x-transition.scale.origin.top.duration.200ms
            x-transition.opacity.duration.200ms
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Добавить проект</h3>
                </div>
                <form
                    action="{{ route('projects.store') }}"
                    @submit="submitProjectForm"
                    method="POST"
                >
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-600">Название</label>
                        <input type="text" name="name" id="name" x-model="projectForm.name" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-600">Описание</label>
                        <textarea name="description" id="description" x-model="projectForm.description" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
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
                        <input type="date" name="start_date" id="start_date" x-model="projectForm.start_date" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="end_date" class="block text-sm font-medium text-gray-600">Дата окончания</label>
                        <input type="date" name="end_date" id="end_date" x-model="projectForm.end_date" class="mt-1 block w-full border-gray-300 rounded-md">
                    </div>
                    <div class="mb-4">
                        <input type="hidden" name="status" x-model="projectForm.status" value="new">
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="closeProjectModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Отмена
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600">
                            Создать
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div
        x-show="isTaskModalOpen"
        x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
        @click.self="closeTaskModal()"
        @keydown.window.escape="closeTaskModal()"
        x-transition.opacity
    >
        <div
            class="bg-white rounded-lg shadow-lg border w-11/12 max-w-md max-h-[90vh] overflow-y-auto"
            x-transition.scale.origin.top.duration.200ms
            x-transition.opacity.duration.200ms
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Добавить задачу</h3>
                </div>
                <form
                    action="{{ route('tasks.store') }}"
                    @submit="submitTaskForm"
                    method="POST"
                >
                    @csrf
                    <div class="mb-4">
                        <label for="task_name" class="block text-sm font-medium text-gray-600">Название</label>
                        <input type="text" name="name" id="task_name" x-model="taskForm.name" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="task_comment" class="block text-sm font-medium text-gray-600">Комментарий</label>
                        <textarea name="comment" id="task_comment" x-model="taskForm.comment" class="mt-1 block w-full border-gray-300 rounded-md" rows="5"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="task_start_date" class="block text-sm font-medium text-gray-600">Дата начала</label>
                        <input type="date" name="start_date" id="task_start_date" x-model="taskForm.start_date" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="task_end_date" class="block text-sm font-medium text-gray-600">Дата окончания</label>
                        <input type="date" name="task_end_date" id="task_end_date" x-model="taskForm.end_date" class="mt-1 block w-full border-gray-300 rounded-md">
                    </div>
                    <div class="mb-4">
                        <label for="task_priority" class="block text-sm font-medium text-gray-600">Приоритет</label>
                        <select name="priority" id="task_priority" x-model="taskForm.priority" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            <option value="low">Низкий</option>
                            <option value="medium">Средний</option>
                            <option value="high">Высокий</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="closeTaskModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Отмена
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600">
                            Создать
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div
        x-show="isActionChoiceModalOpen"
        x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
        @click.self="closeActionChoiceModal()"
        @keydown.window.escape="closeActionChoiceModal()"
        x-transition.opacity
    >
        <div
            class="bg-white rounded-lg shadow-lg border w-11/12 max-w-md"
            x-transition.scale.origin.top.duration.200ms
            x-transition.opacity.duration.200ms
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Выберите действие</h3>
                </div>
                <div class="flex flex-col space-y-4">
                    <button
                        @click="openProjectModalFromChoice()"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600"
                    >
                        Создать проект
                    </button>
                    <button
                        @click="openTaskModalFromChoice()"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-500 rounded-md hover:bg-green-600"
                    >
                        Создать задачу
                    </button>
                    <button
                        @click="closeActionChoiceModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                    >
                        Отмена
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div
        x-show="isTaskViewModalOpen"
        x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
        @click.self="closeTaskViewModal()"
        @keydown.window.escape="closeTaskViewModal()"
        x-transition.opacity
    >
        <div
            class="bg-white rounded-lg shadow-lg border w-11/12 max-w-md max-h-[90vh] overflow-y-auto"
            x-transition.scale.origin.top.duration.200ms
            x-transition.opacity.duration.200ms
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Редактировать задачу</h3>
                </div>
                <form @submit.prevent="updateTask">
                    <div class="mb-4">
                        <label for="task_view_name" class="block text-sm font-medium text-gray-600">Название</label>
                        <input
                            type="text"
                            id="task_view_name"
                            x-model="taskViewForm.name"
                            class="mt-1 block w-full border-gray-300 rounded-md"
                            required
                        >
                    </div>
                    <div class="mb-4">
                        <label for="task_view_comment" class="block text-sm font-medium text-gray-600">Комментарий</label>
                        <textarea
                            id="task_view_comment"
                            x-model="taskViewForm.comment"
                            class="mt-1 block w-full border-gray-300 rounded-md"
                            rows="5"
                        ></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="task_view_priority" class="block text-sm font-medium text-gray-600">Приоритет</label>
                        <select
                            id="task_view_priority"
                            x-model="taskViewForm.priority"
                            class="mt-1 block w-full border-gray-300 rounded-md"
                            required
                        >
                            <option value="low">Низкий</option>
                            <option value="medium">Средний</option>
                            <option value="high">Высокий</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button
                            type="button"
                            @click="deleteTask(taskViewForm.id)"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-md hover:bg-red-600"
                        >
                            Удалить
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600"
                        >
                            Сохранить
                        </button>
                        <button
                            type="button"
                            @click="closeTaskViewModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                        >
                            Закрыть
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Тост для уведомлений -->
    <div id="toast" class="fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded-md transform translate-y-full transition-transform duration-300" x-cloak>
        <span id="toastMessage"></span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        if (calendarEl) {
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                eventTimeFormat: false,
                events: [
                        @foreach ($projects as $project)
                    {
                        title: "{{ $project->id }}: {{ $project->name }}",
                        adminName: "{{ $project->admin ? $project->admin->name : 'Не указан' }}",
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
                        @foreach (\App\Models\Task::where('admin_id', auth()->id())->get() as $task)
                    {
                        id: "task-{{ $task->id }}", // Убедимся, что id формируется корректно
                        title: "Задача: {{ $task->id }}: {{ $task->name }}",
                        adminName: "{{ $task->admin ? $task->admin->name : 'Не указан' }}",
                        start: "{{ $task->start_date }}",
                        end: "{{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->addDay()->toDateString() : null }}",
                        allDay: true,
                        extendedProps: {
                            comment: "{{ $task->comment ?? '' }}",
                            priority: "{{ $task->priority }}",
                            taskId: {{ $task->id }} // Добавляем числовой taskId явно
                        },
                        color: "{{ match ($task->priority) {
                        'low'    => 'rgba(107,114,128,0.5)',
                        'medium' => 'rgba(255,147,0,0.5)',
                        'high'   => 'rgba(255,87,87,0.5)',
                        default  => '#9ca3af'
                    } }}"
                    },
                    @endforeach
                ],
                dateClick: function(info) {
                    @can('create projects')
                    window.dispatchEvent(new CustomEvent('open-action-choice-modal', {
                        detail: { date: info.dateStr }
                    }));
                    @endcan
                },
                eventClick: function(info) {
                    if (info.event.id.startsWith('task-')) {
                        @can('create projects')
                        // Извлекаем числовой ID из extendedProps.taskId
                        const taskId = parseInt(info.event.extendedProps.taskId, 10);
                        if (isNaN(taskId)) {
                            console.error('Invalid task ID:', info.event.id, info.event.extendedProps);
                            return;
                        }
                        console.log('Task clicked, ID:', taskId);
                        window.dispatchEvent(new CustomEvent('open-task-view-modal', {
                            detail: {
                                id: taskId,
                                name: info.event.title.replace(/^Задача: \d+: /, ''),
                                comment: info.event.extendedProps.comment || '',
                                priority: info.event.extendedProps.priority || 'low'
                            }
                        }));
                        info.jsEvent.preventDefault(); // Предотвращаем переход по url, если он есть
                        @endcan
                    }
                },
                eventContent: function(arg) {
                    return {
                        html: arg.event.title + '<br>' + (arg.event.extendedProps.adminName || '')
                    };
                }
            });

            calendar.render();
            console.log('Calendar initialized:', calendar);
        } else {
            console.error('Element #calendar not found');
        }
    });

    function projectManager() {
        return {
            activeTab: 'calendar',
            projects: [],
            filteredProjects: [],
            sort: 'name',
            direction: 'asc',
            isProjectModalOpen: false,
            projectForm: {
                name: '',
                description: '',
                manager_id: '',
                start_date: '',
                end_date: '',
                status: 'new'
            },
            filters: {
                name: '',
                description: '',
                manager: '',
                start_date: '',
                end_date: '',
                status: ''
            },
            openTab(tab) {
                this.activeTab = tab;
                document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
                document.getElementById(tab === 'table' ? 'projectsTab' : 'calendarTab').classList.add('active');
                if (tab === 'calendar' && window.calendar) {
                    this.refreshCalendar();
                    window.calendar.render();
                }
            },
            isTaskModalOpen: false,
            isActionChoiceModalOpen: false,
            isTaskViewModalOpen: false,
            taskForm: {
                name: '',
                comment: '',
                start_date: '',
                end_date: '',
                priority: 'low'
            },
            taskViewForm: {
                id: null,
                name: '',
                comment: '',
                priority: 'low'
            },
            selectedDate: '',
            init() {
                console.log('projectManager initialized');
                this.fetchData();
                this.openTab('calendar');
                window.addEventListener('open-task-modal', (e) => {
                    this.openTaskModal({ start_date: e.detail.date });
                });
                window.addEventListener('open-action-choice-modal', (e) => {
                    this.openActionChoiceModal(e.detail.date);
                });
                window.addEventListener('open-task-view-modal', (e) => {
                    this.openTaskViewModal(e.detail);
                });
            },
            openActionChoiceModal(date) {
                this.selectedDate = date;
                this.isActionChoiceModalOpen = true;
            },
            closeActionChoiceModal() {
                this.isActionChoiceModalOpen = false;
            },
            openProjectModalFromChoice() {
                this.isActionChoiceModalOpen = false;
                this.openProjectModal({ start_date: this.selectedDate });
            },
            openTaskModalFromChoice() {
                this.isActionChoiceModalOpen = false;
                this.openTaskModal({ start_date: this.selectedDate });
            },
            openTaskModal(task = null) {
                console.log('openTaskModal called:', task);
                this.taskForm = task ? { ...task, priority: 'low' } : {
                    name: '',
                    comment: '',
                    start_date: task?.start_date || '',
                    end_date: '',
                    priority: 'low'
                };
                this.isTaskModalOpen = true;
            },
            closeTaskModal() {
                this.isTaskModalOpen = false;
            },
            openTaskViewModal(task) {
                console.log('openTaskViewModal called:', task);
                const taskId = parseInt(task.id, 10);
                if (isNaN(taskId)) {
                    console.error('Invalid task ID in openTaskViewModal:', task.id);
                    this.showToast('Ошибка: Неверный идентификатор задачи');
                    return;
                }
                this.taskViewForm = {
                    id: taskId,
                    name: task.name,
                    comment: task.comment || '',
                    priority: task.priority
                };
                console.log('taskViewForm initialized:', this.taskViewForm);
                this.isTaskViewModalOpen = true;
            },
            closeTaskViewModal() {
                this.isTaskViewModalOpen = false;
            },
            async updateTask() {
                console.log('updateTask called:', this.taskViewForm);
                if (isNaN(this.taskViewForm.id)) {
                    console.error('Invalid task ID for update:', this.taskViewForm.id);
                    this.showToast('Ошибка: Неверный идентификатор задачи');
                    return;
                }
                const response = await fetch(`/tasks/${this.taskViewForm.id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: this.taskViewForm.name,
                        comment: this.taskViewForm.comment,
                        priority: this.taskViewForm.priority
                    }),
                });
                if (response.ok) {
                    this.showToast('Задача обновлена');
                    this.closeTaskViewModal();
                    if (window.calendar) {
                        const event = window.calendar.getEventById(`task-${this.taskViewForm.id}`);
                        if (event) {
                            event.setProp('title', `Задача: ${this.taskViewForm.id}: ${this.taskViewForm.name.trim()}`);
                            event.setProp('color', this.taskViewForm.priority === 'low' ? 'rgba(107,114,128,0.5)' :
                                this.taskViewForm.priority === 'medium' ? 'rgba(255,147,0,0.5)' :
                                    this.taskViewForm.priority === 'high' ? 'rgba(255,87,87,0.5)' : '#9ca3af');
                            event.setExtendedProp('comment', this.taskViewForm.comment || '');
                            event.setExtendedProp('priority', this.taskViewForm.priority);
                        }
                    }
                } else {
                    const error = await response.json();
                    console.log('Update task error:', error);
                    this.showToast('Ошибка: ' + (error.message || Object.values(error.errors || {}).flat().join('\n')));
                }
            },
            async deleteTask(taskId) {
                if (!confirm('Удалить задачу?')) return;
                console.log('deleteTask called:', taskId);
                const parsedTaskId = parseInt(taskId, 10);
                if (isNaN(parsedTaskId)) {
                    console.error('Invalid task ID for delete:', taskId);
                    this.showToast('Ошибка: Неверный идентификатор задачи');
                    return;
                }
                const response = await fetch(`/tasks/${parsedTaskId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                });
                if (response.ok) {
                    this.showToast('Задача удалена');
                    this.closeTaskViewModal();
                    if (window.calendar) {
                        const event = window.calendar.getEventById(`task-${parsedTaskId}`);
                        if (event) event.remove();
                    }
                } else {
                    const error = await response.json();
                    console.log('Delete task error:', error);
                    this.showToast('Ошибка: ' + (error.message || Object.values(error.errors || {}).flat().join('\n')));
                }
            },
            async submitTaskForm(event) {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);

                // Отладка: логируем данные формы
                const formDataEntries = Object.fromEntries(formData);
                console.log('Task form data before submission:', formDataEntries);

                // Убедимся, что end_date передаётся, если taskForm.end_date заполнено
                if (this.taskForm.end_date && !formData.has('end_date')) {
                    formData.set('end_date', this.taskForm.end_date);
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                if (response.ok) {
                    const data = await response.json();
                    console.log('Task creation response:', data);
                    this.showToast(data.success || 'Задача добавлена');
                    this.closeTaskModal();
                    if (window.calendar && data.task) {
                        const taskId = parseInt(data.task.id, 10);
                        if (isNaN(taskId)) {
                            console.error('Invalid task ID from server:', data.task.id);
                            this.showToast('Ошибка: Неверный идентификатор задачи');
                            return;
                        }
                        window.calendar.addEvent({
                            id: `task-${taskId}`,
                            title: `Задача: ${taskId}: ${data.task.name.trim()}`,
                            start: data.task.start_date,
                            end: data.task.end_date ? new Date(new Date(data.task.end_date).setDate(new Date(data.task.end_date).getDate() + 1)).toISOString().split('T')[0] : null,
                            allDay: true,
                            extendedProps: {
                                comment: data.task.comment || '',
                                priority: data.task.priority,
                                taskId: taskId // Явно передаём числовой ID
                            },
                            color: data.task.priority === 'low' ? 'rgba(107,114,128,0.5)' :
                                data.task.priority === 'medium' ? 'rgba(255,147,0,0.5)' :
                                    data.task.priority === 'high' ? 'rgba(255,87,87,0.5)' : '#9ca3af'
                        });
                    }
                } else {
                    const error = await response.json();
                    console.log('Task creation error:', error);
                    this.showToast('Ошибка: ' + (error.message || Object.values(error.errors || {}).flat().join('\n')));
                }
            },
            openProjectModal(project = null) {
                console.log('openProjectModal called:', project);
                this.projectForm = project ? { ...project, status: 'new' } : {
                    name: '',
                    description: '',
                    manager_id: '',
                    start_date: '',
                    end_date: '',
                    status: 'new'
                };
                this.isProjectModalOpen = true;
            },
            closeProjectModal() {
                this.isProjectModalOpen = false;
            },
            async submitProjectForm(event) {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);
                console.log('Submitting project form:', Object.fromEntries(formData));
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                if (response.ok) {
                    const data = await response.json();
                    this.showToast(data.success || 'Проект добавлен');
                    this.closeProjectModal();
                    this.fetchData(); // Перезагружаем данные с сервера
                    if (window.calendar && data.project) {
                        window.calendar.addEvent({
                            id: data.project.id,
                            title: data.project.name.trim(),
                            start: data.project.start_date,
                            end: data.project.end_date ? new Date(new Date(data.project.end_date).setDate(new Date(data.project.end_date).getDate() + 1)).toISOString().split('T')[0] : null,
                            url: "{{ route('projects.show', '') }}/" + data.project.id,
                            allDay: true,
                            color: data.project.status === 'active' ? 'rgba(34,197,94,0.71)' :
                                data.project.status === 'new' ? 'rgba(248,233,95,0.72)' :
                                    data.project.status === 'completed' ? 'rgba(105,159,255,0.74)' :
                                        data.project.status === 'cancelled' ? 'rgba(255,87,87,0.76)' : '#9ca3af'
                        });
                    }
                } else {
                    const error = await response.json();
                    this.showToast('Ошибка: ' + (error.message || Object.values(error.errors || {}).flat().join('\n')));
                }
            },
            async updateStatus(projectId, status, element) {
                console.log('updateStatus called:', { projectId, status });
                const response = await fetch(`/projects/${projectId}/status`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status }),
                });
                const responseData = await response.json();
                console.log('updateStatus response:', responseData);
                if (response.ok) {
                    console.log('Status updated successfully');
                    this.showToast('Статус обновлен');
                    this.projects = this.projects.map(p => p.id === projectId ? { ...p, status } : p);
                    this.applyFilters();
                    if (window.calendar) {
                        const event = window.calendar.getEventById(projectId);
                        if (event) {
                            event.setProp('color', status === 'active' ? 'rgba(34,197,94,0.71)' :
                                status === 'new' ? 'rgba(248,233,95,0.72)' :
                                    status === 'completed' ? 'rgba(105,159,255,0.74)' :
                                        status === 'cancelled' ? 'rgba(255,87,87,0.76)' : '#9ca3af');
                        }
                    }
                    if (element) {
                        element.className = 'status-select ' + ({
                            'active': 'bg-green-100 text-green-800',
                            'new': 'bg-yellow-100 text-yellow-800',
                            'completed': 'bg-blue-100 text-blue-800',
                            'cancelled': 'bg-red-100 text-red-800'
                        }[status] || 'bg-gray-100 text-gray-800');
                    }
                } else {
                    console.error('Status update failed:', responseData);
                    this.showToast('Ошибка: ' + (responseData.error || 'Не удалось обновить статус'));
                }
            },
            async deleteProject(id) {
                if (!confirm('Удалить проект?')) return;
                console.log('deleteProject called:', id);
                const response = await fetch(`/projects/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                });
                if (response.ok) {
                    this.showToast('Проект удален');
                    this.projects = this.projects.filter(p => p.id !== id);
                    this.applyFilters();
                    if (window.calendar) {
                        const event = window.calendar.getEventById(id);
                        if (event) event.remove();
                    }
                } else {
                    this.showToast('Ошибка удаления');
                }
            },
            async fetchData() {
                console.log('fetchData called');
                const response = await fetch(`/projects/table`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (response.ok) {
                    const data = await response.json();
                    this.projects = data.projects;
                    this.applyFilters();
                } else {
                    this.showToast('Ошибка при загрузке данных');
                }
            },
            applyFilters() {
                let filtered = [...this.projects];

                // Фильтрация
                if (this.filters.name) {
                    filtered = filtered.filter(p => p.name && p.name.toLowerCase().includes(this.filters.name.toLowerCase()));
                }
                if (this.filters.description) {
                    filtered = filtered.filter(p => p.description && p.description.toLowerCase().includes(this.filters.description.toLowerCase()));
                }
                if (this.filters.manager) {
                    filtered = filtered.filter(p => p.manager_name && p.manager_name.toLowerCase().includes(this.filters.manager.toLowerCase()));
                }
                if (this.filters.start_date) {
                    filtered = filtered.filter(p => p.start_date === this.filters.start_date);
                }
                if (this.filters.end_date) {
                    filtered = filtered.filter(p => p.end_date === this.filters.end_date);
                }
                if (this.filters.status) {
                    filtered = filtered.filter(p => p.status === this.filters.status);
                }

                // Сортировка
                filtered.sort((a, b) => {
                    let valueA = a[this.sort] || '';
                    let valueB = b[this.sort] || '';
                    if (this.sort === 'manager_name') {
                        valueA = a.manager_name || '';
                        valueB = b.manager_name || '';
                    }
                    if (typeof valueA === 'string') valueA = valueA.toLowerCase();
                    if (typeof valueB === 'string') valueB = valueB.toLowerCase();
                    if (valueA < valueB) return this.direction === 'asc' ? -1 : 1;
                    if (valueA > valueB) return this.direction === 'asc' ? 1 : -1;
                    return 0;
                });

                this.filteredProjects = filtered;
            },
            async refreshCalendar() {
                var calendarEl = document.getElementById('calendar');
                if (calendarEl) {
                    calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        eventTimeFormat: false,
                        events: [
                                @foreach ($projects as $project)
                            {
                                title: "{{ $project->id }}: {{ $project->name }}",
                                adminName: "{{ $project->admin ? $project->admin->name : 'Не указан' }}",
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
                                @foreach (\App\Models\Task::where('admin_id', auth()->id())->get() as $task)
                            {
                                id: "task-{{ $task->id }}", // Убедимся, что id формируется корректно
                                title: "Задача: {{ $task->id }}: {{ $task->name }}",
                                adminName: "{{ $task->admin ? $task->admin->name : 'Не указан' }}",
                                start: "{{ $task->start_date }}",
                                end: "{{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->addDay()->toDateString() : null }}",
                                allDay: true,
                                extendedProps: {
                                    comment: "{{ $task->comment ?? '' }}",
                                    priority: "{{ $task->priority }}",
                                    taskId: {{ $task->id }} // Добавляем числовой taskId явно
                                },
                                color: "{{ match ($task->priority) {
                        'low'    => 'rgba(107,114,128,0.5)',
                        'medium' => 'rgba(255,147,0,0.5)',
                        'high'   => 'rgba(255,87,87,0.5)',
                        default  => '#9ca3af'
                    } }}"
                            },
                            @endforeach
                        ],
                        dateClick: function(info) {
                            @can('create projects')
                            window.dispatchEvent(new CustomEvent('open-action-choice-modal', {
                                detail: { date: info.dateStr }
                            }));
                            @endcan
                        },
                        eventClick: function(info) {
                            if (info.event.id.startsWith('task-')) {
                                @can('create projects')
                                // Извлекаем числовой ID из extendedProps.taskId
                                const taskId = parseInt(info.event.extendedProps.taskId, 10);
                                if (isNaN(taskId)) {
                                    console.error('Invalid task ID:', info.event.id, info.event.extendedProps);
                                    return;
                                }
                                console.log('Task clicked, ID:', taskId);
                                window.dispatchEvent(new CustomEvent('open-task-view-modal', {
                                    detail: {
                                        id: taskId,
                                        name: info.event.title.replace(/^Задача: \d+: /, ''),
                                        comment: info.event.extendedProps.comment || '',
                                        priority: info.event.extendedProps.priority || 'low'
                                    }
                                }));
                                info.jsEvent.preventDefault(); // Предотвращаем переход по url, если он есть
                                @endcan
                            }
                        },
                        eventContent: function(arg) {
                            return {
                                html: arg.event.title + '<br>' + (arg.event.extendedProps.adminName || '')
                            };
                        }
                    });

                    calendar.render();
                    console.log('Calendar initialized:', calendar);
                } else {
                    console.error('Element #calendar not found');
                }
            },
            sortBy(column) {
                if (this.sort === column) {
                    this.direction = this.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sort = column;
                    this.direction = 'asc';
                }
                this.applyFilters();
            },
            showToast(message) {
                const toast = document.getElementById('toast');
                const toastMessage = document.getElementById('toastMessage');
                toastMessage.textContent = message;
                toast.classList.remove('translate-y-full');
                setTimeout(() => {
                    toast.classList.add('translate-y-full');
                }, 2000);
            }
        };
    }
</script>
</body>
</html>
