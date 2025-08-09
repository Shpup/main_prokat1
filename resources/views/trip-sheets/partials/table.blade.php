@forelse ($tripSheets as $tripSheet)
    @php
        $tripSheetData = [
            'id' => $tripSheet->id,
            'date_time' => $tripSheet->date_time,
            'location_id' => $tripSheet->location_id,
            'address' => $tripSheet->address,
            'vehicle_id' => $tripSheet->vehicle_id,
            'driver_id' => $tripSheet->driver_id,
            'distance' => (float)$tripSheet->distance,
            'cost' => (float)$tripSheet->cost,
            'status' => $tripSheet->status,
        ];
    @endphp

    <tr>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $tripSheet->date_time ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $tripSheet->location ? $tripSheet->location->name : ($tripSheet->address ?? '—') }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $tripSheet->vehicle ? $tripSheet->vehicle->brand . ' ' . $tripSheet->vehicle->model : '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $tripSheet->driver ? $tripSheet->driver->name : '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $tripSheet->distance ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $tripSheet->cost ? number_format($tripSheet->cost, 2, '.', '') : '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            @switch($tripSheet->status)
                @case('in_progress')
                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">В процессе</span>
                    @break
                @case('completed')
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Завершено</span>
                    @break
                @case('cancelled')
                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Отменено</span>
                    @break
                @default
                    —
            @endswitch
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            <div class="flex flex-col items-center space-y-1">
                <button
                    type="button"
                    class="px-2.5 py-1 text-xs font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 w-24"
                    @click='openTripSheetModal(@json($tripSheetData))'
                >
                    Редактировать
                </button>
                <form method="POST" action="{{ route('trip-sheets.destroy', $tripSheet->id) }}" onsubmit="return confirm('Удалить путеводный лист?')">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="px-2.5 py-1 text-xs font-medium text-white bg-red-500 rounded-md hover:bg-red-600 w-24"
                    >
                        Удалить
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
            Нет данных
        </td>
    </tr>
@endforelse
