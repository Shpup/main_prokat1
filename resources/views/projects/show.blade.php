<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Склад оборудования</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@include('layouts.navigation')
<div class="container mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">
            Проект: {{ $project->name }}
        </h1>

        <div class="flex items-center gap-3">
            {{-- Бейдж текущего статуса --}}
            @php
                $status = $project->status ?? 'new';
                $statusMap = [
                    'new' => ['label' => 'Новый', 'class' => 'bg-yellow-100 text-yellow-800'],
                    'active' => ['label' => 'В работе', 'class' => 'bg-green-100 text-green-800'],
                    'completed' => ['label' => 'Завершён', 'class' => 'bg-blue-100 text-blue-800'],
                    'cancelled' => ['label' => 'Отменён', 'class' => 'bg-red-100 text-red-800'],
                ];
                $badge = $statusMap[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-gray-100 text-gray-800'];
            @endphp
            <span class="px-3 py-1 text-sm rounded-full font-medium {{ $badge['class'] }}">
                    {{ $badge['label'] }}
                </span>

            @can('edit projects')
                <button
                    type="button"
                    onclick="openModal('changeStatusModal')"
                    class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700"
                >
                    Сменить статус
                </button>
            @endcan
            @can('edit projects')
                <!-- Модалка смены статусаа -->
                <div id="changeStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
                    <div class="bg-white rounded-lg p-6 w-full max-w-md">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Сменить статус проекта</h2>
                        <form action="{{ route('projects.updateStatus', $project) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="mb-4">
                                <label for="project_status" class="block text-sm font-medium text-gray-600">Новый статус</label>
                                <select name="status" id="project_status" class="mt-1 block w-full border-gray-300 rounded-md" required>
                                    <option value="new" {{ $project->status === 'new' ? 'selected' : '' }}>Новый</option>
                                    <option value="active" {{ $project->status === 'active' ? 'selected' : '' }}>В работе</option>
                                    <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Завершён</option>
                                    <option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>Отменён</option>
                                </select>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" onclick="closeModal('changeStatusModal')" class="mr-2 bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Отмена</button>
                                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Сохранить</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    function openModal(id) {
                        document.getElementById(id)?.classList.remove('hidden');
                    }
                    function closeModal(id) {
                        document.getElementById(id)?.classList.add('hidden');
                    }
                </script>
            @endcan
        </div>
    </div>


        {{-- Вкладки --}}
        @php
            $tab = request('tab', 'estimate');
        @endphp

        <div class="flex space-x-4 border-b border-gray-200 mb-4">
            <a href="{{ route('projects.show', $project->id) }}?tab=estimate" class="px-4 py-2 {{ $tab === 'estimate' ? 'border-b-2 border-blue-600 font-semibold text-blue-700' : 'text-gray-600' }}">
                Смета
            </a>
            <a href="{{ route('projects.show', $project->id) }}?tab=staff" class="px-4 py-2 {{ $tab === 'staff' ? 'border-b-2 border-blue-600 font-semibold text-blue-700' : 'text-gray-600' }}">
                Персонал
            </a>
            <a href="{{ route('projects.show', $project->id) }}?tab=equipment" class="px-4 py-2 {{ $tab === 'equipment' ? 'border-b-2 border-blue-600 font-semibold text-blue-700' : 'text-gray-600' }}">
                Оборудование
            </a>
        </div>

        {{-- Контент вкладки --}}
        @if ($tab === 'estimate')
            {{-- Смета --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Смета оборудования</h2>

                @php
                    $total = $project->equipment->sum('price');
                @endphp

                @if ($project->equipment->isEmpty())
                    <p class="text-gray-600">Нет прикреплённого оборудования</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 mb-4">
                        <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Название</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Цена</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Статус</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach ($project->equipment as $item)
                            <tr>
                                <td class="px-6 py-4">{{ $item->name }}</td>
                                <td class="px-6 py-4">{{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-4">{{ $item->pivot->status === 'assigned' ? 'прикреплён' : $item->pivot->status }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="text-right font-semibold text-gray-800">
                        Всего: {{ number_format($total, 2) }} ₽
                    </div>
                @endif
            </div>
        @elseif ($tab === 'staff')
            {{-- Заглушка --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Сотрудники</h2>
                <p class="text-gray-600">Пока не реализовано</p>
            </div>
        @elseif ($tab === 'equipment')
            {{-- Оборудование --}}
            <div class="flex flex-row gap-8">
                <div class="w-1/3 bg-white rounded-lg shadow-lg p-6 h-fit">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Категории</h2>
                    <div id="categoryTree" class="space-y-3">
                        @foreach (\App\Models\Category::whereNull('parent_id')->with('children', 'user')->get() as $category)
                            @include('equipment.category-item', ['category' => $category, 'depth' => 0])
                        @endforeach
                    </div>
                </div>

                <div class="w-2/3 bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 id="categoryTitle" class="text-xl font-semibold text-gray-700"></h2>
                    </div>
                    <div class="mb-4">
                        <input type="text" id="filterEquipment" placeholder="Фильтр по названию..." class="w-full p-2 border rounded-md">
                    </div>
                    <div id="equipmentList">
                        {{-- Таблица будет загружена через loadEquipment(categoryId) --}}
                    </div>
                </div>
            </div>
        @endif
    </div>
    <script>
        let sortDirection = 1;

        const categoriesBase = "{{ url('/categories') }}"; // базовый URL
        const projectEquipmentListBase = "{{ route('projects.equipmentList', $project) }}"; // новый эндпоинт (ниже добавим)

        async function loadEquipment(categoryId) {
            window.currentCategoryId = categoryId;

            // Заголовок категории
            try {
                const r = await fetch(`${categoriesBase}/${categoryId}`, { headers: { 'Accept': 'application/json' } });
                if (r.ok) {
                    const data = await r.json();
                    const h = document.getElementById('categoryTitle');
                    if (h) h.textContent = data.name || '';
                }
            } catch (e) { /* no-op */ }

            // Таблица оборудования (HTML-фрагмент)
            const resp = await fetch(`${projectEquipmentListBase}?category_id=${categoryId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await resp.text();
            document.getElementById('equipmentList').innerHTML = html;

            attachFilterHandler();
        }
        async function attachToProject(equipmentId) {
            try {
                const attachBase = `{{ route('projects.equipment.attach', ['project' => $project, 'equipment' => 0]) }}`;
                const url = attachBase.replace('/0', `/${equipmentId}`);
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await r.json();
                if (!r.ok) throw new Error(data.error || 'Ошибка прикрепления');
                // Перезагрузим таблицу текущей категории
                if (window.currentCategoryId) await loadEquipment(window.currentCategoryId);
            } catch (e) {
                alert(e.message);
            }
        }
        async function detachFromProject(equipmentId) {
            try {
                const detachBase = `{{ route('projects.equipment.detach', ['project' => $project, 'equipment' => 0]) }}`;
                const url = detachBase.replace('/0', `/${equipmentId}`);
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-HTTP-Method-Override': 'DELETE',
                    },
                });
                const data = await r.json();
                if (!r.ok) throw new Error(data.error || 'Ошибка удаления');
                if (window.currentCategoryId) await loadEquipment(window.currentCategoryId);
            } catch (e) {
                alert(e.message);
            }
        }


        function attachFilterHandler() {
            const input = document.getElementById('filterEquipment');
            if (!input) return;
            input.oninput = function () {
                const q = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#equipmentTableBody tr');
                rows.forEach(tr => {
                    const name = tr.cells[0]?.textContent?.toLowerCase() || '';
                    tr.style.display = name.includes(q) ? '' : 'none';
                });
            };
        }
        attachFilterHandler();

        function sortTable(column) {
            const tbody = document.getElementById('equipmentTableBody');
            const rows = Array.from(tbody?.getElementsByTagName('tr') || []);
            const isPrice = column === 2;

            rows.sort((a, b) => {
                let aValue = a.cells[column]?.textContent?.trim() || '';
                let bValue = b.cells[column]?.textContent?.trim() || '';
                if (isPrice) {
                    aValue = parseFloat(aValue.replace(',', '.') || 0);
                    bValue = parseFloat(bValue.replace(',', '.') || 0);
                }
                return aValue.toString().localeCompare(bValue.toString(), undefined, { numeric: true }) * sortDirection;
            });

            sortDirection *= -1;
            rows.forEach(row => tbody.appendChild(row));
        }

        // Раскрывающиеся ветки дерева
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.expand-toggle');
            if (!btn) return;
            const wrapper = btn.closest('.collapsible');
            if (!wrapper) return;
            const content = wrapper.querySelector('.collapsible-content');
            if (!content) return;
            const open = content.style.display !== 'none';
            content.style.display = open ? 'none' : '';
            btn.textContent = open ? '►' : '▼';
        });

        // Удаление категории
        async function deleteCategory(categoryId) {
            if (!confirm('Удалить категорию и всё содержимое?')) return;
            try {
                const resp = await fetch(`{{ route('categories.destroy', ['category' => 'ID']) }}`.replace('ID', categoryId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-HTTP-Method-Override': 'DELETE',
                    },
                });
                const data = await resp.json();
                if (resp.ok) {
                    // Удалим узел дерева из DOM
                    const node = document.querySelector(`.collapsible[data-category-id="${categoryId}"]`);
                    if (node) node.remove();
                    // Очистим правую часть если смотрели эту категорию
                    if (window.currentCategoryId === categoryId) {
                        document.getElementById('categoryTitle').textContent = '';
                        document.getElementById('equipmentList').innerHTML = '';
                    }
                    alert('Категория удалена');
                } else {
                    alert(data.error || 'Ошибка при удалении категории');
                }
            } catch (e) {
                alert('Ошибка сети при удалении категории');
            }
        }

        // Редактирование оборудования
        async function editEquipment(id) {
            try {
                const r = await fetch(`{{ url('/equipment') }}/${id}/edit`);
                const data = await r.json();
                openEquipmentModal(data.category_id);

                // Заполняем форму
                document.getElementById('equipment_id').value = data.id;
                document.getElementById('name').value = data.name || '';
                document.getElementById('description').value = data.description || '';
                document.getElementById('price').value = data.price || '';
                document.getElementById('category_id').value = data.category_id || '';

                // specs — JSON
                if (data.specifications) {
                    document.getElementById('specifications').value = JSON.stringify(data.specifications, null, 2);
                } else if (document.getElementById('specifications')) {
                    document.getElementById('specifications').value = '';
                }

                document.getElementById('modalTitle').textContent = 'Редактировать оборудование';
                document.getElementById('submitEquipment').textContent = 'Сохранить';
                const form = document.getElementById('createEquipmentForm');
                form.action = `{{ url('/equipment') }}/${id}`;
                ensurePutMethod(form);
            } catch (e) {
                alert('Не удалось загрузить данные оборудования');
            }
        }

        function ensurePutMethod(form) {
            let method = form.querySelector('input[name="_method"]');
            if (!method) {
                method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                form.appendChild(method);
            }
            method.value = 'PUT';
        }

        // Удаление оборудования
        async function deleteEquipment(id) {
            if (!confirm('Удалить это оборудование?')) return;
            try {
                const resp = await fetch(`{{ url('/equipment') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-HTTP-Method-Override': 'DELETE',
                    },
                });
                const data = await resp.json();
                if (resp.ok) {
                    // Удалим строку
                    const tr = document.querySelector(`tr[data-id="${id}"]`);
                    if (tr) tr.remove();
                } else {
                    alert(data.error || 'Ошибка при удалении');
                }
            } catch (e) {
                alert('Ошибка сети при удалении');
            }
        }
    </script>

