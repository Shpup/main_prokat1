@forelse ($projects as $project)
    <tr>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ $project->name ?? '—' }}</td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ Str::limit($project->description, 50, '...') ?? '—' }}</td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ $project->manager ? $project->manager->name : '—' }}</td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ \Carbon\Carbon::parse($project->start_date)->format('d.m.Y') ?? '—' }}</td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d.m.Y') : '—' }}</td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            <select
                class="status-select"
                x-data="{ status: '{{ $project->status }}' }"
                x-model="status"
                x-on:change="console.log('Status change triggered:', {{ $project->id }}, status); window.dispatchEvent(new CustomEvent('update-status', { detail: { projectId: {{ $project->id }}, status: status, element: $el } }))"
                x-init="$el.className = 'status-select ' + ({
                    'active': 'bg-green-100 text-green-800',
                    'new': 'bg-yellow-100 text-yellow-800',
                    'completed': 'bg-blue-100 text-blue-800',
                    'cancelled': 'bg-red-100 text-red-800'
                }[status] || 'bg-gray-100 text-gray-800')"
            >
                <option value="new" {{ $project->status === 'new' ? 'selected' : '' }}>Новый</option>
                <option value="active" {{ $project->status === 'active' ? 'selected' : '' }}>Активен</option>
                <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Завершён</option>
                <option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>Отменён</option>
            </select>
        </td>
        <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
            <div class="flex flex-col items-center space-y-1">
                <a
                    href="{{ route('projects.show', $project->id) }}"
                    class="px-2.5 py-1 text-xs font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 w-24 text-center"
                >
                    Перейти в проект
                </a>
                <form method="POST" action="{{ route('projects.destroy', $project->id) }}" onsubmit="return confirm('Удалить проект?')">
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
        <td colspan="7" class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
            Нет данных
        </td>
    </tr>
@endforelse
