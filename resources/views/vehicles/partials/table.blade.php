@forelse ($vehicles as $vehicle)
    @php
        $vehicleData = [
            'id' => $vehicle->id,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'year' => (int)$vehicle->year,
            'license_plate' => $vehicle->license_plate,
            'status' => $vehicle->status,
            'mileage' => (int)$vehicle->mileage,
            'fuel_type' => $vehicle->fuel_type,
            'fuel_grade' => $vehicle->fuel_grade,
            'fuel_consumption' => (float)$vehicle->fuel_consumption,
            'diesel_consumption' => (float)$vehicle->diesel_consumption,
            'battery_capacity' => (float)$vehicle->battery_capacity,
            'range' => (int)$vehicle->range,
            'hybrid_consumption' => (float)$vehicle->hybrid_consumption,
            'hybrid_range' => (int)$vehicle->hybrid_range,
            'comment' => $vehicle->comment,
        ];
    @endphp

    <tr>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $vehicle->brand ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $vehicle->model ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $vehicle->year ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $vehicle->license_plate ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            @switch($vehicle->status)
                @case('available')
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Доступен</span>
                    @break
                @case('in_use')
                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">В использовании</span>
                    @break
                @case('maintenance')
                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">На обслуживании</span>
                    @break
                @default
                    —
            @endswitch
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $vehicle->mileage ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $vehicle->fuel_type ? ucfirst($vehicle->fuel_type) : '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            <div class="flex flex-col items-center space-y-1">
                <button
                    type="button"
                    class="px-2.5 py-1 text-xs font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 w-24"
                    @click='openVehicleModal(@json($vehicleData))'
                >
                    Редактировать
                </button>
                <form method="POST" action="{{ route('vehicles.destroy', $vehicle->id) }}" onsubmit="return confirm('Удалить транспорт?')">
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
