<div id="equipmentList">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
        <tr>
            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 cursor-pointer" onclick="sortTable(0)">Название</th>
            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Штрихкод</th>
            @can('view prices')
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 cursor-pointer" onclick="sortTable(2)">Цена</th>
            @endcan
            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 w-40">Характеристики</th>
            <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Статус</th>
            @can('edit projects')
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Действия</th>
            @endcan
        </tr>
        </thead>
        <tbody id="equipmentTableBody" class="divide-y divide-gray-200">
        @if ($equipment->isEmpty())
            <tr>
                <td colspan="{{ Auth::user()->hasPermissionTo('view prices') ? 6 : 5 }}" class="px-6 py-4 text-center text-gray-600">Нет оборудования в этой категории</td>
            </tr>
        @else
            @foreach ($equipment as $item)
                <tr class="hover:bg-gray-50" data-id="{{ $item->id }}">
                    <td class="px-6 py-4">{{ $item->name }}</td>
                    <td class="px-6 py-4">
                        @if ($item->qrcode)
                            <!-- Debug: {{ Storage::url($item->qrcode) }} -->
                            <img src="{{ Storage::url($item->qrcode) }}" alt="QR-код" class="h-16 w-16" onerror="console.log('Failed to load QR code: ' + this.src)">
                        @else
                            Нет QR-кода
                        @endif
                    </td>
                    @can('view prices')
                        <td class="px-6 py-4">{{ $item->price ? number_format($item->price, 2) : 'Не указана' }}</td>
                    @endcan
                    <td class="px-6 py-4 w-40">
                        @if (!empty($item->formatted_specifications))
                            <div class="text-sm text-gray-600">
                                @foreach ($item->formatted_specifications as $spec)
                                    <div class="whitespace-nowrap">{{ $spec['label'] }}: {{ $spec['value'] }} {{ $spec['unit'] }}</div>
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
                            <button onclick="editEquipment({{ $item->id }})" class="text-blue-600 hover:underline mr-4">Редактировать</button>
                            <button onclick="deleteEquipment({{ $item->id }})" class="text-red-600 hover:underline">Удалить</button>
                        </td>
                    @endcan
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
</div>
