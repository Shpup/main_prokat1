<div class="collapsible" data-category-id="{{ $category->id }}">
    <div class="category-item">
        <div class="category-name">
            <a href="#" onclick="loadEquipment({{ $category->id }})" class="text-blue-600 hover:underline" style="margin-left: {{ $depth * 1 }}rem;">{{ $category->name }}</a>
        </div>
        @can('create projects')
            <button onclick="openCategoryModal({{ $category->id }})" class="add-button">+</button>
        @endcan
        @if ($category->children->isNotEmpty())
            <button class="expand-toggle">▼</button>
        @endif
        @can('delete projects')
            @if (auth()->id() === $category->user_id || auth()->user()->hasRole('admin'))
                <button onclick="deleteCategory({{ $category->id }})" class="text-red-600 hover:underline ml-2 delete-button">Удалить</button>
            @endif
        @endcan
    </div>
    <div class="collapsible-content">
        @foreach ($category->children as $child)
            @include('equipment.category-item', ['category' => $child, 'depth' => $depth + 1])
        @endforeach
    </div>
</div>
