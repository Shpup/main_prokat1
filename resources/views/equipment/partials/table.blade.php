@if ($equipment->isEmpty())
    <tr>
        <td colspan="{{ Auth::user()->hasPermissionTo('view prices') ? 5 : 4 }}" class="px-6 py-4 text-center text-gray-600">Нет оборудования в этой категории</td>
    </tr>
@else
    @foreach ($equipment as $item)
        <tr class="border-t">
            <td class="px-6 py-4">{{ $item->name }}</td>
            <td class="px-6 py-4">{{ $item->barcode }}</td>
            @can('view prices')
                <td class="px-6 py-4">{{ $item->price ? number_format($item->price, 2) : 'Не указана' }}</td>
            @endcan
            <td class="px-6 py-4">
                @foreach ($item->projects as $project)
                    <span class="text-sm text-gray-600">{{ $project->pivot->status }} ({{ $project->name }})</span><br>
                @endforeach
                @if ($item->projects->isEmpty())
                    На складе
                @endif
            </td>
            @can('edit projects')
                <td class="px-6 py-4">
                    <a href="{{ route('equipment.edit', $item) }}" class="text-blue-600 hover:underline">Редактировать</a>
                    <form action="{{ route('equipment.destroy', $item) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Вы уверены?')">Удалить</button>
                    </form>
                </td>
            @endcan
        </tr>
    @endforeach
@endif
