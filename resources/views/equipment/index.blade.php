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
        <div class="flex flex-row gap-8">
            <!-- Левая колонка: Дерево категорий -->
            <div class="w-1/3 bg-white rounded-lg shadow-lg p-6 h-fit">
                <h2 class="text-xl font-semibold text-gray-700 mb-6">Категории
                    @can('create projects')
                        <button onclick="openCategoryModal(null)" class="add-button">+</button>
                    @endcan
                </h2>
                <div id="categoryTree" class="category-tree space-y-3">
                    @php
                        $user = auth()->user();
                        $adminId = $user->hasRole('admin') ? $user->id : $user->admin_id;
                        $categories = \App\Models\Category::whereNull('parent_id')->where('admin_id', $adminId)->with('children', 'admin')->get();
                    @endphp
                    @if ($categories->isEmpty())
                        <p class="text-gray-600">Нет категорий для отображения</p>
                    @else
                        @foreach ($categories as $category)
                            @include('equipment.category-item', ['category' => $category, 'depth' => 0])
                        @endforeach
                    @endif
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
                    @include('equipment._table', ['equipment' => $equipment])
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.currentCategoryId = '';

    function loadEquipment(categoryId) {
        window.currentCategoryId = categoryId;
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const url = categoryId ? `/equipment?category_id=${categoryId}` : '/equipment';

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка: ' + response.statusText);
                }
                return response.text();
            })
            .then(html => {
                document.getElementById('equipmentList').innerHTML = html;
                document.getElementById('categoryTitle').textContent = categoryId ? `Категория: ${document.querySelector(`.collapsible[data-category-id="${categoryId}"] a`).textContent}` : 'Все оборудование';
            })
            .catch(error => {
                console.error('Ошибка загрузки оборудования:', error);
                alert('Ошибка: ' + error.message);
            });
    }

    function openCategoryModal(parentId) {
        const modal = document.createElement('div');
        modal.innerHTML = `
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
                <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                    <h2 class="text-lg font-semibold mb-4">Добавить категорию</h2>
                    <form id="categoryForm" method="POST">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                        <input type="hidden" name="parent_id" value="${parentId || ''}">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-600">Название</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md" required>
                        </div>
                        <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Создать</button>
                        <button type="button" onclick="this.closest('.fixed').remove()" class="ml-2 text-gray-600 hover:underline">Отмена</button>
                    </form>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('/categories', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ошибка: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        addCategoryToTree(data.category, parentId);
                        if (parentId) {
                            updateParentCategory(parentId);
                        }
                        modal.remove();
                        alert(data.success);
                    } else {
                        alert('Ошибка: ' + (data.error || 'Не удалось создать категорию'));
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Ошибка: ' + error.message);
                });
        });
    }

    function openEquipmentModal(categoryId) {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        fetch('/equipment/create' + (categoryId ? `?category_id=${categoryId}` : ''), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                const modal = document.createElement('div');
                modal.innerHTML = `
                <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
                    <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg w-full">
                        <h2 class="text-lg font-semibold mb-4">Добавить оборудование</h2>
                        <form id="equipmentForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="${token}">
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-600">Название</label>
                                <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            </div>
                            <div class="mb-4">
                                <label for="category_id" class="block text-sm font-medium text-gray-600">Категория</label>
                                <select name="category_id" id="category_id" class="mt-1 block w-full border-gray-300 rounded-md">
                                    <option value="">Нет</option>
                                    ${data.categories.map(category => `
                                        <option value="${category.id}" ${categoryId == category.id ? 'selected' : ''}>${category.name}</option>
                                    `).join('')}
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-600">Описание</label>
                                <textarea name="description" id="description" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                            </div>
                            ${data.canViewPrices ? `
                                <div class="mb-4">
                                    <label for="price" class="block text-sm font-medium text-gray-600">Цена</label>
                                    <input type="number" step="0.01" name="price" id="price" class="mt-1 block w-full border-gray-300 rounded-md">
                                </div>
                            ` : ''}
                            <div class="mb-4">
                                <label for="specifications" class="block text-sm font-medium text-gray-600">Характеристики (JSON)</label>
                                <textarea name="specifications" id="specifications" class="mt-1 block w-full border-gray-300 rounded-md" placeholder='{"weight": "10kg", "size": "100x50cm"}'></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="image" class="block text-sm font-medium text-gray-600">Изображение</label>
                                <input type="file" name="image" id="image" class="mt-1 block w-full border-gray-300 rounded-md">
                            </div>
                            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Добавить</button>
                            <button type="button" onclick="this.closest('.fixed').remove()" class="ml-2 text-gray-600 hover:underline">Отмена</button>
                        </form>
                    </div>
                </div>
            `;
                document.body.appendChild(modal);
                document.getElementById('equipmentForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    fetch('/equipment', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Ошибка: ' + response.statusText);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                modal.remove();
                                alert(data.success);
                                loadEquipment(window.currentCategoryId);
                            } else {
                                alert('Ошибка: ' + (data.error || 'Не удалось добавить оборудование'));
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка:', error);
                            alert('Ошибка: ' + error.message);
                        });
                });
            })
            .catch(error => {
                console.error('Ошибка загрузки данных формы:', error);
                alert('Ошибка: ' + error.message);
            });
    }

    function addCategoryToTree(category, parentId = null) {
        const categoryTree = document.getElementById('categoryTree');
        const parentElement = parentId
            ? categoryTree.querySelector(`.collapsible[data-category-id="${parentId}"] .collapsible-content`)
            : categoryTree;

        if (!parentElement) {
            console.warn('Родительская категория не найдена, добавляем в корень');
            parentElement = categoryTree;
        }

        const newCategory = document.createElement('div');
        newCategory.className = 'collapsible';
        newCategory.setAttribute('data-category-id', category.id);
        newCategory.setAttribute('data-expanded', 'false');
        const hasChildren = category.children && category.children.length > 0;
        newCategory.innerHTML = `
            <div class="category-item">
                <div class="category-name">
                    <a href="#" onclick="loadEquipment(${category.id})" class="text-blue-600 hover:underline ml-4">${category.name}</a>
                    <span class="text-sm text-gray-500 ml-2">(Владелец: ${category.admin?.name ?? 'Неизвестно'})</span>
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
        initializeToggleButtons(newCategory);
    }

    function updateParentCategory(parentId) {
        const parentElement = document.querySelector(`.collapsible[data-category-id="${parentId}"]`);
        if (parentElement) {
            const content = parentElement.querySelector('.collapsible-content');
            let toggle = parentElement.querySelector('.expand-toggle');
            if (!toggle) {
                toggle = document.createElement('button');
                toggle.className = 'expand-toggle';
                toggle.textContent = '▼';
                const categoryItem = parentElement.querySelector('.category-item');
                const deleteButton = categoryItem.querySelector('.delete-button');
                if (deleteButton) {
                    categoryItem.insertBefore(toggle, deleteButton);
                } else {
                    categoryItem.appendChild(toggle);
                }
                initializeToggleButtons(parentElement);
            }
            content.style.display = parentElement.getAttribute('data-expanded') === 'true' ? 'block' : 'none';
        }
    }

    function initializeToggleButtons(container = document.getElementById('categoryTree')) {
        const toggleButtons = container.querySelectorAll('.expand-toggle');
        toggleButtons.forEach(button => {
            if (!button.dataset.eventAttached) {
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
                    }
                });
            }
        });

        const nestedCollapsibles = container.querySelectorAll('.collapsible-content');
        nestedCollapsibles.forEach(nested => initializeToggleButtons(nested));
    }

    function deleteCategory(categoryId) {
        if (!confirm('Вы уверены, что хотите удалить эту категорию? Все вложенные категории и оборудование также будут удалены!')) {
            return;
        }
        const token = document.querySelector('meta[name="csrf-token"]').content;
        fetch(`/categories/${categoryId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.success);
                    const categoryElement = document.querySelector(`.collapsible[data-category-id="${categoryId}"]`);
                    if (categoryElement) {
                        const parentCategory = categoryElement.closest('.collapsible-content')?.closest('.collapsible');
                        categoryElement.remove();
                        if (parentCategory) {
                            const remainingChildren = parentCategory.querySelectorAll('.collapsible-content .collapsible').length;
                            if (remainingChildren === 0) {
                                const toggle = parentCategory.querySelector('.expand-toggle');
                                if (toggle) toggle.remove();
                            }
                        }
                        if (window.currentCategoryId == categoryId) {
                            loadEquipment('');
                        }
                    }
                } else {
                    alert('Ошибка: ' + (data.error || 'Не удалось удалить категорию'));
                }
            })
            .catch(error => {
                console.error('Ошибка при удалении категории:', error);
                alert('Ошибка: ' + error.message);
            });
    }

    function deleteEquipment(id) {
        if (!confirm('Вы уверены, что хотите удалить это оборудование?')) {
            return;
        }
        const token = document.querySelector('meta[name="csrf-token"]').content;
        fetch(`/equipment/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.success);
                    document.querySelector(`tr[data-id="${id}"]`).remove();
                } else {
                    alert('Ошибка: ' + (data.error || 'Не удалось удалить оборудование'));
                }
            })
            .catch(error => {
                console.error('Ошибка при удалении оборудования:', error);
                alert('Ошибка: ' + error.message);
            });
    }

    function editEquipment(id) {
        window.location.href = `/equipment/${id}/edit`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        initializeToggleButtons();
        loadEquipment('');
    });
</script>
</body>
</html>
