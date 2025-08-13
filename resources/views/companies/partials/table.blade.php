@forelse ($companies as $c)
    @php
        $companyData = [
            'id' => $c->id,
            'name' => $c->name,
            'type' => $c->type,
            'country' => $c->country,
            'tax_rate' => (float)$c->tax_rate,
            'accounting_method' => $c->accounting_method,
            'comment' => $c->comment,
            'is_default' => (bool)$c->is_default,
        ];
    @endphp
    <tr>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            <a href="{{ route('companies.show', $c->id) }}" class="text-blue-600 hover:underline">{{ $c->name ?? '—' }}</a>
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            @switch($c->type)
                @case('ip')
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">ИП</span>
                    @break
                @case('ur')
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Юр. лицо</span>
                    @break
                @case('fl')
                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Физ. лицо</span>
                    @break
                @default
                    —
            @endswitch
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $c->country ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $c->tax_rate ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            @switch($c->accounting_method)
                @case('osn_inclusive')
                    <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">ОСН, налог в стоимости</span>
                    @break
                @case('osn_exclusive')
                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">ОСН, налог сверху</span>
                    @break
                @case('usn_inclusive')
                    <span class="inline-flex items-center rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-medium text-teal-800">УСН, налог в стоимости</span>
                    @break
                @case('usn_exclusive')
                    <span class="inline-flex items-center rounded-full bg-cyan-100 px-2.5 py-0.5 text-xs font-medium text-cyan-800">УСН, налог сверху</span>
                    @break
                @default
                    —
            @endswitch
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            {{ $c->comment ?? '—' }}
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            @if($c->is_default) <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">✔</span> @else — @endif
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            <div class="flex flex-col items-center space-y-1">
                <button
                    type="button"
                    class="px-2.5 py-1 text-xs font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 w-24"
                    @click='openCompanyModal(@json($companyData))'
                >
                    Редактировать
                </button>
                <button
                    type="button"
                    class="px-2.5 py-1 text-xs font-medium text-white bg-red-500 rounded-md hover:bg-red-600 w-24"
                    @click="deleteCompany({{ $c->id }})"
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
