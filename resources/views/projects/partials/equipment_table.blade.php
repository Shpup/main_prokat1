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
    @forelse ($equipment as $item)
        @php
            $attachedToThis = $item->projects->contains($project->id);
        @endphp
        <tr class="hover:bg-gray-50" data-id="{{ $item->id }}">
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
                @if ($attachedToThis)
                    прикреплён
                @elseif ($item->projects->isNotEmpty())
                    {{-- Показать, что занято другим проектом (можно перечислить) --}}
                    Занято
                @else
                    На складе
                @endif
            </td>
            @can('edit projects')
                <td class="px-6 py-4">
                    @if (!$attachedToThis)
                        <button onclick="attachToProject({{ $item->id }})" class="text-blue-600 hover:underline">Добавить</button>
                    @else
                        <button onclick="detachFromProject({{ $item->id }})" class="text-red-600 hover:underline">Убрать</button>
                    @endif
                </td>
            @endcan
        </tr>
    @empty
        <tr>
            <td colspan="{{ Auth::user()->hasPermissionTo('view prices') ? 5 : 4 }}" class="px-6 py-4 text-center text-gray-600">
                Нет оборудования в этой категории
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
