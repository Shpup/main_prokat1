<table id="counterpartiesTable" class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
    <tr>
        <th class="cursor-pointer" @click="sort('name')">
            Наименование
            <span x-text="sortField === 'name' ? (sortDirection === 'asc' ? '↑' : '↓') : ''"></span>
        </th>
        <th>Менеджер</th>
        <th class="cursor-pointer" @click="sort('code')">
            Код
            <span x-text="sortField === 'code' ? (sortDirection === 'asc' ? '↑' : '↓') : ''"></span>
        </th>
        <th class="cursor-pointer" @click="sort('status')">
            Статус
            <span x-text="sortField === 'status' ? (sortDirection === 'asc' ? '↑' : '↓') : ''"></span>
        </th>
        <th>Фактический адрес</th>
        <th>Комментарий</th>
        <th class="cursor-pointer" @click="sort('is_available_for_sublease')">
            Субаренда
            <span x-text="sortField === 'is_available_for_sublease' ? (sortDirection === 'asc' ? '↑' : '↓') : ''"></span>
        </th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
    @forelse ($counterparties as $counterparty)
        <tr>
            <td>
                <a href="{{ route('counterparties.show', $counterparty) }}" class="text-blue-600 hover:underline">
                    {{ $counterparty->name ?? '—' }}
                </a>
            </td>
            <td>{{ $counterparty->manager ? $counterparty->manager->name : '—' }}</td>
            <td>{{ $counterparty->code ?? '—' }}</td>
            <td>
                @switch($counterparty->status)
                    @case('new')
                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">Новый</span>
                        @break
                    @case('verified')
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Проверенный</span>
                        @break
                    @case('dangerous')
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Опасный</span>
                        @break
                    @default
                        —
                @endswitch
            </td>
            <td>{{ $counterparty->actual_address ?? '—' }}</td>
            <td>{{ $counterparty->comment ?? '—' }}</td>
            <td>
                @if($counterparty->is_available_for_sublease)
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">✔</span>
                @else
                    —
                @endif
            </td>
            <td>
                <div class="flex flex-col items-center space-y-1">
                    <button
                        @click="deleteCounterparty({{ $counterparty->id }})"
                        class="px-2.5 py-1 text-xs font-medium text-white bg-red-500 rounded-md hover:bg-red-600 w-24"
                    >
                        Удалить
                    </button>
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
    </tbody>
</table>
