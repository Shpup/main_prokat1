<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .collapsible { cursor: pointer; }
        .collapsible-content { display: none; }
        .category-tree .collapsible {
            padding-left: 1rem;
            transition: padding-left 0.2s ease;
        }
        .noselect {
            -webkit-touch-callout: none; /* iOS Safari */
            -webkit-user-select: none; /* Safari */
            -khtml-user-select: none; /* Konqueror HTML */
            -moz-user-select: none; /* Old versions of Firefox */
            -ms-user-select: none; /* Internet Explorer/Edge */
            user-select: none; /* Non-prefixed version, currently
                                  supported by Chrome, Edge, Opera and Firefox */
            color:white;
        }
        .category-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            background-color: #f9fafb;
            margin-bottom: 0.25rem;
        }
        .category-item:hover {
            background-color: #f3f4f6;
        }
        .category-name {
            display: flex;
            align-items: center;
        }
        .category-name a {
            color: #3b82f6;
            text-decoration: none;
            margin-left: 0.5rem;
        }
        .category-name a:hover {
            text-decoration: underline;
        }
        .expand-toggle {
            font-size: 0.875rem;
            color: #6b7280;
            transition: transform 0.2s ease;
            cursor: pointer;
        }
        .add-button {
            color: #10b981;
            font-size: 0.875rem;
            margin-left: 0.5rem;
        }
        .add-button:hover {
            color: #059669;
        }
        .delete-button {
            font-size: 0.875rem;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body class="antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')
    <div class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <button onclick="openCreate()" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 margin:100" >
                Добавить площадку
            </button>
            <div class="noselect"> MM</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Наименование</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Адрес</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Телефон</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Менеджер</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Режим доступа</th>
                        <th class="px-6 py-3 text-center text-sm font-medium text-gray-600">Сотрудников</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-600">Действия</th>
                    </tr>
                    </thead>
                    <tbody id="sitesTableBody" class="divide-y divide-gray-200">
                    @forelse ($sites as $site)
                        <tr class="hover:bg-gray-50" data-id="{{ $site->id }}">
                            <td class="px-6 py-4">{{ $site->name ?? '—' }}</td>
                            <td class="px-6 py-4">{{ $site->address ?? '—' }}</td>
                            <td class="px-6 py-4">{{ $site->phone ?? '—' }}</td>
                            <td class="px-6 py-4">{{ $site->manager ?? '—' }}</td>
                            <td class="px-6 py-4">
                                @if ($site->access_mode === 'documents')
                                    по документам
                                @elseif ($site->access_mode === 'passes')
                                    по пропускам
                                @elseif ($site->access_mode === 'none')
                                    без контроля
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">{{ $site->staff->count() }}</td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="openEdit({{ $site->id }})" class="text-blue-600 hover:underline mr-4">Ред.</button>
                                <button onclick="destroy({{ $site->id }})" class="text-red-600 hover:underline">Удалить</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-600">Нет площадок</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $sites->links() }}
            </div>
        </div>

        <!-- Модальное окно для создания/редактирования площадки -->
        <div id="createSiteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg">
                <h2 id="modalTitle" class="text-xl font-semibold text-gray-800 mb-4">Добавить площадку</h2>
                <form id="createSiteForm" action="{{ route('sites.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="site_id">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-600">Наименование</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-600">Адрес</label>
                            <input type="text" name="address" id="address" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-600">Телефон</label>
                            <input type="text" name="phone" id="phone" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="manager" class="block text-sm font-medium text-gray-600">Менеджер</label>
                            <input type="text" name="manager" id="manager" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="access_mode" class="block text-sm font-medium text-gray-600">Режим доступа</label>
                            <select name="access_mode" id="access_mode" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="">—</option>
                                <option value="documents">по документам</option>
                                <option value="passes">по пропускам</option>
                                <option value="none">без контроля</option>
                            </select>
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="comment" class="block text-sm font-medium text-gray-600">Комментарий</label>
                            <textarea name="comment" id="comment" class="mt-1 block w-full border-gray-300 rounded-md" rows="3"></textarea>
                        </div>
                    </div>
                    <h3 class="text-lg font-medium mt-6">Сотрудники площадки</h3>
                    <button type="button" onclick="openStaffModal()" class="mt-2 mb-4 px-4 py-2 bg-green-500 text-white rounded">Добавить сотрудника</button>
                    <div id="staffList" class="overflow-x-auto mb-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Сотрудник</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Телефон</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Комментарий</th>
                                <th class="px-6 py-3 text-right text-sm font-medium text-gray-600">Действия</th>
                            </tr>
                            </thead>
                            <tbody id="staffTableBody">
                            <!-- Динамически заполняется JS -->
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" onclick="closeModal('createSiteModal')" class="mr-2 bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Отмена</button>
                        <button type="submit" id="submitSite" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Добавить</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Модальное окно для создания/редактирования сотрудника -->
        <div id="createStaffModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 id="staffModalTitle" class="text-lg font-semibold mb-4">Добавить сотрудника</h3>
                <div class="space-y-4">
                    <input type="hidden" id="staff_index">
                    <div>
                        <label for="staff_name" class="block text-sm font-medium text-gray-600">Сотрудник</label>
                        <input type="text" id="staff_name" class="mt-1 block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="staff_phone" class="block text-sm font-medium text-gray-600">Телефон</label>
                        <input type="text" id="staff_phone" class="mt-1 block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="staff_comment" class="block text-sm font-medium text-gray-600">Комментарий</label>
                        <textarea id="staff_comment" class="mt-1 block w-full border-gray-300 rounded-md" rows="2"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-2">
                    <button type="button" onclick="closeModal('createStaffModal')" class="px-4 py-2 bg-gray-200">Отмена</button>
                    <button type="button" onclick="saveStaff()" class="px-4 py-2 bg-blue-600 text-white">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
</div>
    <script>
        let sites = @json($sites->items());
        const createUrl = '{{ route('sites.store') }}';
        const updateUrlTemplate = '{{ route('sites.update', ['site' => 'SITE_ID']) }}';
        const destroyUrlTemplate = '{{ route('sites.destroy', ['site' => 'SITE_ID']) }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function openCreate() {
            document.getElementById('createSiteForm').reset();
            document.getElementById('createSiteModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Добавить площадку';
            document.getElementById('site_id').value = '';
            document.getElementById('submitSite').textContent = 'Добавить';
            document.getElementById('createSiteForm').action = createUrl;
            updateStaffTable([]);
        }

        function openEdit(siteId) {
            const site = sites.find(s => s.id === siteId);
            if (!site) return alert('Площадка не найдена');
            document.getElementById('createSiteModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Редактировать площадку';
            document.getElementById('site_id').value = site.id;
            document.getElementById('name').value = site.name || '';
            document.getElementById('address').value = site.address || '';
            document.getElementById('phone').value = site.phone || '';
            document.getElementById('manager').value = site.manager || '';
            document.getElementById('access_mode').value = site.access_mode || '';
            document.getElementById('comment').value = site.comment || '';
            document.getElementById('submitSite').textContent = 'Сохранить';
            document.getElementById('createSiteForm').action = updateUrlTemplate.replace('SITE_ID', site.id);
            updateStaffTable(site.staff);
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function openStaffModal(index = null) {
            document.getElementById('createStaffModal').classList.remove('hidden');
            document.getElementById('staff_index').value = index !== null ? index : '';
            document.getElementById('staffModalTitle').textContent = index !== null ? 'Редактировать сотрудника' : 'Добавить сотрудника';
            if (index !== null) {
                const siteId = document.getElementById('site_id').value;
                const site = sites.find(s => s.id === parseInt(siteId));
                if (site && site.staff[index]) {
                    document.getElementById('staff_name').value = site.staff[index].name || '';
                    document.getElementById('staff_phone').value = site.staff[index].phone || '';
                    document.getElementById('staff_comment').value = site.staff[index].comment || '';
                }
            } else {
                document.getElementById('createStaffModal').reset();
            }
        }

        function saveStaff() {
            const index = document.getElementById('staff_index').value;
            const staff = {
                name: document.getElementById('staff_name').value,
                phone: document.getElementById('staff_phone').value,
                comment: document.getElementById('staff_comment').value
            };
            const siteId = document.getElementById('site_id').value;
            const site = sites.find(s => s.id === parseInt(siteId));
            if (site) {
                if (index !== '') {
                    site.staff[index] = staff;
                } else {
                    site.staff.push(staff);
                }
                updateStaffTable(site.staff);
            }
            closeModal('createStaffModal');
        }

        function removeStaff(index) {
            const siteId = document.getElementById('site_id').value;
            const site = sites.find(s => s.id === parseInt(siteId));
            if (site) {
                site.staff.splice(index, 1);
                updateStaffTable(site.staff);
            }
        }

        function updateStaffTable(staffData = []) {
            const staffTableBody = document.getElementById('staffTableBody');
            if (staffData.length > 0) {
                staffTableBody.innerHTML = staffData.map((staff, idx) => `
                <tr data-index="${idx}">
                    <td class="px-6 py-4">${staff.name || '—'}</td>
                    <td class="px-6 py-4">${staff.phone || '—'}</td>
                    <td class="px-6 py-4">${staff.comment || '—'}</td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="openStaffModal(${idx})" class="text-blue-600 hover:underline mr-4">Ред.</button>
                        <button onclick="removeStaff(${idx})" class="text-red-600 hover:underline">Удалить</button>
                    </td>
                </tr>
            `).join('');
            } else {
                staffTableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-600">Нет сотрудников</td></tr>';
            }
        }

        document.getElementById('createSiteForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('_token', csrfToken);
            if (document.getElementById('site_id').value) {
                formData.append('_method', 'PUT');
            }

            // Добавляем сотрудников в FormData
            const siteId = document.getElementById('site_id').value;
            const site = sites.find(s => s.id === parseInt(siteId)) || { staff: [] };
            site.staff.forEach((staff, index) => {
                formData.append(`staff[${index}][name]`, staff.name || '');
                formData.append(`staff[${index}][phone]`, staff.phone || '');
                formData.append(`staff[${index}][comment]`, staff.comment || '');
            });

            const url = this.action;
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                if (data.id) {
                    let siteIndex = sites.findIndex(s => s.id === data.id);
                    if (siteIndex !== -1) {
                        sites[siteIndex] = data;
                    } else {
                        sites.unshift(data);
                    }
                    updateTable();
                    closeModal('createSiteModal');
                }
            } else {
                alert('Ошибка сохранения');
            }
        });

        function destroy(siteId) {
            if (!confirm('Уверены?')) return;
            const url = destroyUrlTemplate.replace('SITE_ID', siteId);
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).then(response => {
                if (response.ok) {
                    sites = sites.filter(s => s.id !== siteId);
                    updateTable();
                } else {
                    alert('Ошибка удаления');
                }
            }).catch(() => alert('Ошибка сети'));
        }

        function updateTable() {
            const tbody = document.getElementById('sitesTableBody');
            tbody.innerHTML = sites.map(site => `
            <tr class="hover:bg-gray-50" data-id="${site.id}">
                <td class="px-6 py-4">${site.name || '—'}</td>
                <td class="px-6 py-4">${site.address || '—'}</td>
                <td class="px-6 py-4">${site.phone || '—'}</td>
                <td class="px-6 py-4">${site.manager || '—'}</td>
                <td class="px-6 py-4">${site.access_mode === 'documents' ? 'по документам' : site.access_mode === 'passes' ? 'по пропускам' : site.access_mode === 'none' ? 'без контроля' : '—'}</td>
                <td class="px-6 py-4 text-center">${site.staff.length}</td>
                <td class="px-6 py-4 text-right">
                    <button onclick="openEdit(${site.id})" class="text-blue-600 hover:underline mr-4">Ред.</button>
                    <button onclick="destroy(${site.id})" class="text-red-600 hover:underline">Удалить</button>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-600">Нет площадок</td></tr>';
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateTable();
        });
    </script>
</body>
</html>
