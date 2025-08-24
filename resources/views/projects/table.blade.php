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
            height: calc(100vh - 8rem);
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
                ],
                dateClick: function(info) {
                    @can('create projects')
                    window.dispatchEvent(new CustomEvent('open-project-modal', {
                        detail: { date: info.dateStr }
                    }));
                    @endcan
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
            init() {
                console.log('projectManager initialized');
                this.fetchData();
                this.openTab('calendar');
            },
            openTab(tab) {
                this.activeTab = tab;
                document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
                document.getElementById(tab === 'table' ? 'projectsTab' : 'calendarTab').classList.add('active');
                if (tab === 'calendar' && window.calendar) {
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
                            ],
                            dateClick: function(info) {
                                @can('create projects')
                                window.dispatchEvent(new CustomEvent('open-project-modal', {
                                    detail: { date: info.dateStr }
                                }));
                                @endcan
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
