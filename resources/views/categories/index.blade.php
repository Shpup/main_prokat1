<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Склад оборудования</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .collapsible { cursor: pointer; }
        .collapsible-content { display: none; }
        .category-tree .collapsible {
            padding-left: 1rem;
            transition: padding-left 0.2s ease;
        }
        .category-tree .collapsible .collapsible-content .collapsible {
            padding-left: 2rem;
        }
        .category-tree .collapsible .collapsible-content .collapsible .collapsible-content .collapsible {
            padding-left: 3rem;
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
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Склад оборудования</h1>

        <div class="flex flex-row gap-8">
            <!-- Левая колонка: Дерево категорий -->
            <div class="w-1/3 bg-white rounded-lg shadow-lg p-6 h-fit">
                <h2 class="text-xl font-semibold text-gray-700 mb-6">Категории</h2>
                <div id="categoryTree" class="category-tree space-y-3">
                    @foreach (\App\Models\Category::whereNull('parent_id')->with('children', 'user')->get() as $category)
                        @include('equipment.category-item', ['category' => $category, 'depth' => 0])
                    @endforeach
                </div>
            </div>

            <!-- Правая колонка: Содержимое категории -->
            <div class="w-2/3 bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="categoryTitle" class="text-xl font-semibold text-gray-700"></h2>
                    @can('create projects')
                        <button onclick="openEquipmentModal(window.currentCategoryId)" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                            Добавить оборудование
                        </button>
                    @endcan
                </div>
                <div class="mb-4">
                    <input type="text" id="filterEquipment" placeholder="Фильтр по названию..." class="w-full p-2 border rounded-md">
                </div>
                <div id="equipmentList">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 cursor-pointer" onclick="sortTable(0)">Название</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Штрихкод</th>
                            @can('view prices')
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 cursor-pointer" onclick="sortTable(2)">Цена</th>
                            @endcan
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Статус</th>
                            @can('edit projects')
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Действия</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody id="equipmentTableBody" class="divide-y divide-gray-200">
                        @if ($equipment->isEmpty())
                            <tr>
                                <td colspan="{{ Auth::user()->hasPermissionTo('view prices') ? 5 : 4 }}" class="px-6 py-4 text-center text-gray-600">Нет оборудования в этой категории</td>
                            </tr>
                        @else
                            @foreach ($equipment as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ $item->name }}</td>
                                    <td class="px-6 py-4">
                                        @if ($item->barcode)
                                            <img src="{{ Storage::url($item->barcode) }}" alt="Штрихкод" class="h-16 w-16">
                                        @else
                                            Нет штрихкода
                                        @endif
                                    </td>
                                    @can('view prices')
                                        <td class="px-6 py-4">{{ $item->price ? number_format($item->price, 2) : 'Не указана' }}</td>
                                    @endcan
                                    <td class="px-6 py-4">
                                        @if ($item->projects->isNotEmpty())
                                            @foreach ($item->projects as $project)
                                                <span class="text-sm text-gray-600">{{ $project->pivot->status }} ({{ $project->name }})</span><br>
                                            @endforeach
                                        @else
                                            На складе
                                        @endif
                                    </td>
                                    @can('edit projects')
                                        <td class="px-6 py-4">
                                            <a href="{{ route('equipment.edit', $item) }}" class="text-blue-600 hover:underline">Редактировать</a>
                                            <form action="{{ route('equipment.destroy', $item) }}" method="POST" class="inline-block ml-4">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Вы уверены?')">Удалить</button>
                                            </form>
                                        </td>
                                    @endcan
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Модальное окно для создания категории -->
        <div id="createCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Добавить категорию</h2>
                <form id="createCategoryForm" action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-600">Название</label>
                        <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="mb-4">
                        <label for="parent_id" class="block text-sm font-medium text-gray-600">Родительская категория</label>
                        <select name="parent_id" id="parent_id" class="mt-1 block w-full border-gray-300 rounded-md">
                            <option value="">Нет</option>
                            @foreach (\App\Models\Category::where('user_id', auth()->id())->orWhere('user_id', null)->get() as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" onclick="closeModal('createCategoryModal')" class="mr-2 bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Отмена</button>
                        <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">Создать</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Модальное окно для создания оборудования -->
        <div id="createEquipmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Добавить оборудование</h2>
                <form id="createEquipmentForm" action="{{ route('equipment.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-600">Название</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-600">Категория</label>
                            <select name="category_id" id="category_id" class="mt-1 block w-full border-gray-300 rounded-md" onchange="updateCategoryDisplay()">
                                <option value="">Нет</option>
                                @foreach (\App\Models\Category::where('user_id', auth()->id())->orWhere('user_id', null)->get() as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <span id="categoryDisplay" class="text-sm text-gray-500 mt-1 block"></span>
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-600">Описание</label>
                            <textarea name="description" id="description" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                        </div>
                        @can('view prices')
                            <div class="mb-4">
                                <label for="price" class="block text-sm font-medium text-gray-600">Цена</label>
                                <input type="number" step="0.01" name="price" id="price" class="mt-1 block w-full border-gray-300 rounded-md">
                            </div>
                        @endcan
                        <div class="mb-4 col-span-2">
                            <label for="specifications" class="block text-sm font-medium text-gray-600">Характеристики (JSON)</label>
                            <textarea name="specifications" id="specifications" class="mt-1 block w-full border-gray-300 rounded-md" placeholder='{"weight": "10kg", "size": "100x50cm"}'></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="image" class="block text-sm font-medium text-gray-600">Изображение</label>
                            <input type="file" name="image" id="image" class="mt-1 block w-full border-gray-300 rounded-md" onchange="previewImage(event)">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-600">Предпросмотр</label>
                            <img id="imagePreview" class="mt-1 h-32 w-auto border rounded-md" src="#" alt="Предпросмотр изображения" style="display: none;">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" onclick="closeModal('createEquipmentModal')" class="mr-2 bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Отмена</button>
                        <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Добавить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.loadEquipment = function(categoryId) {
        console.log('Загрузка оборудования для категории:', categoryId);
        if (!categoryId && categoryId !== 0) {
            console.warn('categoryId не определён, загрузка всех категорий');
            categoryId = ''; // Пустой category_id для загрузки всех записей
            document.getElementById('categoryTitle').textContent = 'Все категории';
        } else {
            fetch(`/api/categories/${categoryId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('categoryTitle').textContent = data.name || 'Неизвестная категория';
                })
                .catch(error => {
                    console.error('Ошибка загрузки названия категории:', error);
                    document.getElementById('categoryTitle').textContent = 'Ошибка загрузки';
                });
        }
        fetch(`/equipment?category_id=${categoryId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
            .then(response => {
                console.log('Ответ сервера на загрузку оборудования:', response);
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                    });
                }
                return response.text();
            })
            .then(html => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const newTableBody = tempDiv.querySelector('#equipmentTableBody');
                if (newTableBody) {
                    document.getElementById('equipmentTableBody').innerHTML = newTableBody.innerHTML;
                    window.currentCategoryId = categoryId;
                    filterEquipment();
                } else {
                    document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-600">Нет оборудования в этой категории</td></tr>';
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки оборудования:', error);
                document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-600">Ошибка загрузки оборудования: ' + error.message + '</td></tr>';
            });
    };

    window.openCategoryModal = function(parentId) {
        console.log('Открытие модального окна для категории, parentId:', parentId);
        document.getElementById('createCategoryForm').reset();
        document.getElementById('createCategoryModal').classList.remove('hidden');
        document.getElementById('parent_id').value = parentId || '';
    };

    window.openEquipmentModal = function(categoryId) {
        console.log('Открытие модального окна для оборудования, categoryId:', categoryId);
        document.getElementById('createEquipmentForm').reset();
        document.getElementById('createEquipmentModal').classList.remove('hidden');
        const categorySelect = document.getElementById('category_id');
        if (categoryId) {
            categorySelect.value = categoryId;
        }
        updateCategoryDisplay();
        document.getElementById('imagePreview').style.display = 'none';
        window.currentCategoryId = categoryId || null;
    };

    window.closeModal = function(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        window.currentCategoryId = null;
        document.getElementById('createCategoryForm')?.reset();
        document.getElementById('createEquipmentForm')?.reset();
        document.getElementById('imagePreview').style.display = 'none';
    };

    let sortDirection = 1;

    function sortTable(column) {
        const tbody = document.getElementById('equipmentTableBody');
        const rows = Array.from(tbody.getElementsByTagName('tr'));
        const isPrice = column === 2;

        rows.sort((a, b) => {
            let aValue = a.cells[column]?.textContent?.trim() || '';
            let bValue = b.cells[column]?.textContent?.trim() || '';
            if (isPrice) {
                aValue = parseFloat(aValue.replace(',', '.') || 0);
                bValue = parseFloat(bValue.replace(',', '.') || 0);
            }
            return aValue.localeCompare(bValue, undefined, { numeric: true }) * sortDirection;
        });

        sortDirection *= -1;
        rows.forEach(row => tbody.appendChild(row));
    }

    function filterEquipment() {
        const input = document.getElementById('filterEquipment').value.toLowerCase();
        const rows = document.getElementById('equipmentTableBody').getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            const nameCell = rows[i].getElementsByTagName('td')[0];
            if (nameCell) {
                const name = nameCell.textContent.toLowerCase();
                rows[i].style.display = name.includes(input) ? '' : 'none';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }

    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('imagePreview');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    document.getElementById('filterEquipment').addEventListener('input', filterEquipment);

    // Обработка формы создания категории
    document.getElementById('createCategoryForm').addEventListener('submit', function (e) {
        e.preventDefault();
        console.log('Отправка формы создания категории');
        const formData = new FormData(this);
        console.log('Данные формы:', Object.fromEntries(formData));
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!token) {
            console.error('CSRF-токен не найден');
            alert('Ошибка: CSRF-токен не найден');
            return;
        }
        if (!formData.get('name').trim()) {
            console.error('Поле name пустое');
            alert('Ошибка: Поле "Название" обязательно для заполнения');
            return;
        }
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
            .then(response => {
                console.log('Ответ сервера на создание категории:', response);
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.category) {
                    alert(data.success);
                    closeModal('createCategoryModal');
                    addCategoryToTree(data.category);
                    const parentId = formData.get('parent_id') || null;
                    const parentElement = parentId ? document.querySelector(`.collapsible[data-category-id="${parentId}"]`) : document.getElementById('categoryTree');
                    if (parentElement) {
                        initializeToggleButtons(parentElement);
                        if (parentId) {
                            fetch(`/api/categories/${parentId}/children`, {
                                headers: {
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json'
                                }
                            })
                                .then(response => response.json())
                                .then(children => {
                                    if (children.length > 0) {
                                        const parentToggle = parentElement.querySelector('.expand-toggle');
                                        if (parentToggle) {
                                            parentToggle.style.display = 'inline-block';
                                        } else {
                                            const categoryItem = parentElement.querySelector('.category-item');
                                            if (categoryItem) {
                                                categoryItem.innerHTML += '<button class="expand-toggle">▼</button>';
                                                initializeToggleButtons(parentElement);
                                            }
                                        }
                                    }
                                });
                        }
                    }
                } else {
                    console.error('Неожиданный ответ:', data);
                    alert('Ошибка: ' + (data.error || 'Не удалось создать категорию'));
                }
            })
            .catch(error => {
                console.error('Ошибка при создании категории:', error);
                try {
                    const errorData = JSON.parse(error.message.match(/Response: (.*)/)?.[1] || '{}');
                    if (errorData.errors) {
                        alert('Ошибка валидации: ' + Object.values(errorData.errors).flat().join(', '));
                    } else {
                        alert('Ошибка: ' + error.message);
                    }
                } catch (e) {
                    alert('Ошибка: ' + error.message);
                }
            });
    });

    // Обработка формы создания оборудования
    document.getElementById('createEquipmentForm').addEventListener('submit', function (e) {
        e.preventDefault();
        console.log('Отправка формы создания оборудования');
        const formData = new FormData(this);
        console.log('Данные формы:', Object.fromEntries(formData));
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!token) {
            console.error('CSRF-токен не найден');
            alert('Ошибка: CSRF-токен не найден');
            return;
        }
        if (!formData.get('name').trim()) {
            console.error('Поле name пустое');
            alert('Ошибка: Поле "Название" обязательно для заполнения');
            return;
        }
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
            .then(response => {
                console.log('Ответ сервера на создание оборудования:', response);
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.success);
                    closeModal('createEquipmentModal');
                    if (window.currentCategoryId) {
                        addEquipmentToTable(data.equipment);
                    }
                } else {
                    console.error('Неожиданный ответ:', data);
                    alert('Ошибка: Неожиданный ответ от сервера.');
                }
            })
            .catch(error => {
                console.error('Ошибка при создании оборудования:', error);
                try {
                    const errorData = JSON.parse(error.message.match(/Response: (.*)/)?.[1] || '{}');
                    if (errorData.errors) {
                        alert('Ошибка валидации: ' + Object.values(errorData.errors).flat().join(', '));
                    } else {
                        alert('Ошибка: ' + error.message);
                    }
                } catch (e) {
                    alert('Ошибка: ' + error.message);
                }
            });
    });

    // Функция для добавления новой категории в дерево
    function addCategoryToTree(category) {
        if (!category || !category.id) {
            console.error('Некорректные данные категории:', category);
            alert('Ошибка: Некорректные данные категории');
            return;
        }
        const parentId = category.parent_id || null;
        const categoryTree = document.getElementById('categoryTree');
        let parentElement = parentId ? categoryTree.querySelector(`.collapsible[data-category-id="${parentId}"] .collapsible-content`) : categoryTree;

        if (!parentElement) {
            console.warn('Родительская категория не найдена, добавляем в корень');
            parentElement = categoryTree;
        }

        const newCategory = document.createElement('div');
        newCategory.className = 'collapsible';
        newCategory.setAttribute('data-category-id', category.id);
        newCategory.setAttribute('data-expanded', 'false'); // Изначально свернуто
        // Проверяем наличие детей на основе данных из API
        const hasChildren = category.children && category.children.length > 0;
        newCategory.innerHTML = `
                <div class="category-item">
                    <div class="category-name">
                        <a href="#" onclick="loadEquipment(${category.id})" class="text-blue-600 hover:underline ml-4">— ${category.name}</a>
                        <span class="text-sm text-gray-500 ml-2">(Владелец: ${category.user?.name ?? 'Неизвестно'})</span>
                    </div>
                    @can('create projects')
        <button onclick="openCategoryModal(${category.id})" class="add-button">+</button>
                    @endcan
        ${hasChildren ? '<button class="expand-toggle">▼</button>' : ''}
                    @can('delete projects')
        <button onclick="deleteCategory(${category.id})" class="text-red-600 hover:underline ml-2 delete-button">Удалить</button>
                    @endcan
        </div>
        <div class="collapsible-content" style="display: none;"></div>
`;
        parentElement.appendChild(newCategory);
        initializeToggleButtons(newCategory); // Инициализация кнопок для новой категории
        console.log('Added category:', newCategory, 'Expanded:', newCategory.getAttribute('data-expanded'));
    }

    // Функция для добавления нового оборудования в таблицу
    function addEquipmentToTable(equipment) {
        const tbody = document.getElementById('equipmentTableBody');
        const existingRows = tbody.getElementsByTagName('tr');
        let exists = false;

        // Проверяем, есть ли уже такое оборудование
        for (let row of existingRows) {
            if (row.cells[0].textContent === equipment.name) {
                exists = true;
                break;
            }
        }

        if (!exists) {
            const newRow = document.createElement('tr');
            newRow.className = 'hover:bg-gray-50';
            const canViewPrices = @json(Auth::user()->hasPermissionTo('view prices'));
            const canEditProjects = @json(Auth::user()->hasPermissionTo('edit projects'));
            const colSpan = canViewPrices ? 5 : 4;

            newRow.innerHTML = `
                    <td class="px-6 py-4">${equipment.name}</td>
                    <td class="px-6 py-4">
                        ${equipment.barcode ? `<img src="${equipment.barcode.startsWith('/storage') ? equipment.barcode : '/storage/' + equipment.barcode}" alt="Штрихкод" class="h-16 w-16">` : 'Нет штрихкода'}
                    </td>
                    ${canViewPrices ? `<td class="px-6 py-4">${equipment.price ? number_format(equipment.price, 2) : 'Не указана'}</td>` : ''}
                    <td class="px-6 py-4">На складе</td>
                    ${canEditProjects ? `
                        <td class="px-6 py-4">
                            <a href="/equipment/${equipment.id}/edit" class="text-blue-600 hover:underline">Редактировать</a>
                            <form action="/equipment/${equipment.id}" method="POST" class="inline-block ml-4">
                                @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Вы уверены?')">Удалить</button>
        </form>
    </td>` : ''}
                `.replace(/>\s+</g, '><'); // Убираем лишние пробелы

            // Устанавливаем colSpan для случая, если таблица пуста
            if (tbody.children.length === 1 && tbody.children[0].cells[0].colSpan === colSpan) {
                tbody.innerHTML = '';
            }
            tbody.appendChild(newRow);
            filterEquipment(); // Перефильтровываем таблицу
        } else {
            console.warn('Оборудование уже существует, обновление не требуется:', equipment.name);
        }
    }

    // Функция для обновления отображения выбранной категории
    function updateCategoryDisplay() {
        const categorySelect = document.getElementById('category_id');
        const categoryDisplay = document.getElementById('categoryDisplay');
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        categoryDisplay.textContent = selectedOption ? `Выбрана: ${selectedOption.text}` : '';
    }

    // Функция для инициализации всех кнопок разворота (рекурсивно)
    function initializeToggleButtons(container = document.getElementById('categoryTree')) {
        const toggleButtons = container.querySelectorAll('.expand-toggle');
        toggleButtons.forEach(button => {
            if (!button.dataset.eventAttached) { // Предотвращаем повторную привязку
                button.dataset.eventAttached = 'true';
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const collapsible = button.closest('.collapsible');
                    if (collapsible) {
                        const isExpanded = collapsible.getAttribute('data-expanded') === 'true';
                        const content = collapsible.querySelector('.collapsible-content');
                        const toggle = collapsible.querySelector('.expand-toggle');

                        if (!isExpanded) {
                            content.style.display = 'block';
                            toggle.style.transform = 'rotate(180deg)';
                            collapsible.setAttribute('data-expanded', 'true');
                        } else {
                            content.style.display = 'none';
                            toggle.style.transform = 'rotate(0deg)';
                            collapsible.setAttribute('data-expanded', 'false');
                        }
                        console.log('Toggled:', collapsible, 'Expanded:', collapsible.getAttribute('data-expanded'));
                    }
                });
            }
        });

        // Рекурсивно инициализируем вложенные контейнеры
        const nestedCollapsibles = container.querySelectorAll('.collapsible-content');
        nestedCollapsibles.forEach(nested => {
            initializeToggleButtons(nested);
        });
    }

    // Функция для удаления категории
    function deleteCategory(categoryId) {
        if (!confirm('Вы уверены, что хотите удалить эту категорию? Все вложенные категории и оборудование также будут удалены!')) {
            return;
        }
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!token) {
            alert('Ошибка: CSRF-токен не найден');
            return;
        }
        fetch(`/categories/${categoryId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
            .then(response => {
                console.log('Ответ сервера на удаление категории:', response);
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.success);
                    const categoryElement = document.querySelector(`.collapsible[data-category-id="${categoryId}"]`);
                    if (categoryElement) {
                        categoryElement.remove();
                        // Обновляем таблицу оборудования, если текущая категория была активной
                        if (window.currentCategoryId == categoryId) {
                            loadEquipment('');
                        }
                    }
                } else {
                    console.error('Неожиданный ответ:', data);
                    alert('Ошибка: ' + (data.error || 'Не удалось удалить категорию'));
                }
            })
            .catch(error => {
                console.error('Ошибка при удалении категории:', error);
                alert('Ошибка: ' + error.message);
            });
    }

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        initializeToggleButtons();
        updateCategoryDisplay();
        // Устанавливаем начальное состояние для всех существующих
        document.querySelectorAll('.collapsible').forEach(el => {
            el.setAttribute('data-expanded', 'false');
            const content = el.querySelector('.collapsible-content');
            const toggle = el.querySelector('.expand-toggle');
            if (content) content.style.display = 'none';
            if (toggle) toggle.style.transform = 'rotate(0deg)';
        });
        // Загружаем оборудование по умолчанию
        loadEquipment('');
    });
</script>
</body>
</html>
