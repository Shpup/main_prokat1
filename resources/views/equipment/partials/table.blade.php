@if ($equipment->isEmpty())
    <tr>
        <td colspan="{{ Auth::user()->hasPermissionTo('view prices') ? 6 : 5 }}" class="px-6 py-4 text-center text-gray-600">Нет оборудования в этой категории</td>
    </tr>
@else
    @foreach ($equipment as $item)
        <tr class="border-t">
            <td class="px-6 py-4">{{ $item->name }}</td>
            <td class="px-6 py-4">
                @if ($item->qrcode)
                    <img src="{{ Storage::url($item->qrcode) }}" alt="QR-код" class="h-16 w-16">
                @else
                    Нет QR-кода
                @endif
            </td>
            @can('view prices')
                <td class="px-6 py-4">{{ $item->price ? number_format($item->price, 2) : 'Не указана' }}</td>
            @endcan
            <td class="px-6 py-4">
                @if (!empty($item->formatted_specifications))
                    <div class="text-sm text-gray-600">
                        @foreach ($item->formatted_specifications as $spec)
                            <div>{{ $spec['label'] }}: {{ $spec['value'] }} {{ $spec['unit'] }}</div>
                        @endforeach
                    </div>
                @else
                    <span class="text-sm text-gray-400">Не указаны</span>
                @endif
            </td>
            <td class="px-6 py-4">
                @if ($item->status === 'on_warehouse')
                    <span class="text-sm text-gray-600">На складе</span>
                @elseif ($item->status === 'sent_to_project')
                    <span class="text-sm text-gray-600">Едет на проект</span>
                @elseif ($item->status === 'on_project')
                    <span class="text-sm text-gray-600">На проекте</span>
                @elseif ($item->status === 'sent_to_warehouse')
                    <span class="text-sm text-gray-600">Едет на склад</span>
                @else
                    <span class="text-sm text-gray-600">{{ $item->status ?? 'На складе' }}</span>
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
