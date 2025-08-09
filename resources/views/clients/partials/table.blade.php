@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\App\Models\Client[] $clients */
    $fmt = static function ($value) {
        if ($value === null) return '—';
        $v = (float) $value;
        $str = rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');
        return $str . '%';
    };
@endphp

@forelse ($clients as $client)
    @php
        $clientData = [
            'id' => $client->id,
            'name' => $client->name,
            'description' => $client->description,
            'phone' => $client->phone,
            'email' => $client->email,
            'discount_equipment' => (float)$client->discount_equipment,
            'discount_services' => (float)$client->discount_services,
            'discount_materials' => (float)$client->discount_materials,
            'blacklisted' => (bool)$client->blacklisted,
        ];
    @endphp

    <tr>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $client->name ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $client->phone ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $client->email ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $fmt($client->discount_equipment) }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $fmt($client->discount_services) }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $fmt($client->discount_materials) }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            @if ($client->blacklisted)
                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Да</span>
            @else
                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Нет</span>
            @endif
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            <div class="flex flex-col items-center space-y-1">
                <button
                    type="button"
                    class="px-2.5 py-1 text-xs font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 w-24"
                    @click='openModal(@json($clientData))'
                >
                    Редактировать
                </button>

                <form method="POST" action="{{ route('clients.destroy', $client->id) }}" onsubmit="return confirm('Удалить клиента?')">
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
