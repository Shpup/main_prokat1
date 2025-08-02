<div class="ml-4 flex items-center">
    <a href="#" onclick="loadEquipment({{ $category->id }})" class="text-blue-600 hover:underline flex-1">{{ $prefix }}{{ $category->name }}</a>
    @can('create projects')
        <button onclick="openCategoryModal({{ $category->id }})" class="text-green-600 hover:text-green-800 ml-2">+</button>
    @endcan
</div>
@if ($category->children->isNotEmpty())
    @foreach ($category->children as $child)
        @include('categories.partials.tree', ['category' => $child, 'prefix' => $prefix . '—'])
    @endforeach
@endif
