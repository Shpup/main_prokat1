@include('layouts.navigation')
@vite(['resources/css/app.css', 'resources/css/lk-about.css', 'resources/js/app.js', 'resources/js/lk-about.js'])

  <div class="max-w-[1800px] mx-auto px-8 py-6">
    @include('profile.partials._tabs')

    @if(request('view')==='projects')
      <div class="flex items-start justify-between gap-4 mb-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Проекты</h1>
        <div></div>
      </div>

      <form method="get" action="{{ route('profile.index') }}" class="bg-white rounded-lg shadow p-4 mb-5">
        <input type="hidden" name="view" value="projects">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
          <div class="lg:col-span-2">
            <input type="text" name="p_q" value="{{ $projectsFilters['pSearch'] ?? '' }}" placeholder="Поиск по названию или месту..."
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
          </div>
                     <div>
             <select name="p_status" class="w-full border border-gray-300 rounded-md px-3 py-2">
               @php $st = $projectsFilters['pStatus'] ?? 'new'; @endphp
               <option value="new" {{ $st==='new' ? 'selected' : '' }}>Новый</option>
                              <option value="active" {{ $st==='active' ? 'selected' : '' }}>Активный</option>
               <option value="completed" {{ $st==='completed' ? 'selected' : '' }}>Завершён</option>
               <option value="cancelled" {{ $st==='cancelled' ? 'selected' : '' }}>Отменён</option>
             </select>
           </div>
           <div>
             <select name="p_time" class="w-full border border-gray-300 rounded-md px-3 py-2">
               @php $time = $projectsFilters['pTime'] ?? ''; @endphp
               <option value="" {{ $time==='' ? 'selected' : '' }}>Время</option>
               <option value="with_time" {{ $time==='with_time' ? 'selected' : '' }}>С назначенным временем</option>
               <option value="without_time" {{ $time==='without_time' ? 'selected' : '' }}>Без назначенного времени</option>
             </select>
           </div>
           
         </div>
         
                   <div class="flex flex-col md:flex-row md:justify-start gap-3 mt-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Дата начала</label>
              <input type="date" name="p_start_date" value="{{ $projectsFilters['pStartDate'] ?? '' }}" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Дата окончания</label>
              <input type="date" name="p_end_date" value="{{ $projectsFilters['pEndDate'] ?? '' }}" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
          </div>
        <div class="mt-3 flex items-center gap-2">
          <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Применить</button>
          <a href="{{ route('profile.index', ['view'=>'projects']) }}" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Сбросить</a>
        </div>
      </form>

      @if(($projectsGrouped ?? collect())->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 p-10 text-center">
          <div class="text-lg font-semibold mb-1">Проектов не найдено.</div>
          <p class="text-gray-600">Измени фильтры или сбрось поиск.</p>
          <div class="mt-3">
            <a href="{{ route('profile.index', ['view'=>'projects']) }}" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Сбросить</a>
          </div>
        </div>
      @else
        <div class="space-y-6">
          @foreach($projectsGrouped as $date => $items)
            @if($date !== 'no_date')
              <h3 class="text-sm font-semibold text-gray-700">{{ $date }}</h3>
            @endif
            <div class="space-y-3">
              @foreach($items as $it)
                @php
                  $status = $it['status'] ?? 'new';
                  $statusMap = [
                    'new' => 'bg-yellow-100 text-yellow-700',
                    'active' => 'bg-green-100 text-green-700',
                    'completed' => 'bg-blue-100 text-blue-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                  ];
                @endphp
                <a href="{{ $it['url'] ?? '#' }}" class="block bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-lg transition transform hover:-translate-y-0.5">
                  <div class="flex items-start justify-between gap-3">
                    <div class="font-semibold text-gray-900 truncate" title="{{ $it['title'] }}">{{ $it['title'] }}</div>
                    <div class="text-sm text-gray-500 truncate" title="{{ $it['location'] }}">{{ $it['location'] }}</div>
                  </div>
                  <div class="mt-1 flex items-center justify-between text-sm text-gray-700">
                    <div>
                      Время: {{ $it['time_range'] ?? 'Время не назначено' }}
                      <span class="ml-3">Оплата: {{ $it['payment'] ?? 'Не указана' }}</span>
                    </div>
                                         <span class="text-xs px-2 py-1 rounded {{ $statusMap[$status] ?? 'bg-gray-100 text-gray-700' }}">
                       {{ $status==='new' ? 'Новый' : ($status==='active' ? 'Активный' : ($status==='completed' ? 'Завершён' : 'Отменён')) }}
                     </span>
                  </div>
                </a>
              @endforeach
            </div>
          @endforeach
        </div>

        @php
          $pPage = $projectsFilters['pPage'] ?? 1;
          $pTotalPages = $projectsFilters['pTotalPages'] ?? 1;
        @endphp
        @if($pTotalPages > 1)
          <div class="mt-6 flex items-center justify-center gap-2">
            @php $qs = request()->query(); $qs['view']='projects'; @endphp
            @if($pPage > 1)
              <a class="px-3 py-2 rounded border border-gray-300 hover:bg-gray-50" href="{{ route('profile.index', array_merge($qs, ['p_page'=>$pPage-1])) }}">Предыдущая</a>
            @endif
            <span class="px-3 py-2">Стр. {{ $pPage }} из {{ $pTotalPages }}</span>
            @if($pPage < $pTotalPages)
              <a class="px-3 py-2 rounded border border-gray-300 hover:bg-gray-50" href="{{ route('profile.index', array_merge($qs, ['p_page'=>$pPage+1])) }}">Следующая</a>
            @endif
          </div>
        @endif
      @endif

    @elseif(request('view')==='tasks')
      <div class="flex items-start justify-between gap-4 mb-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Задачи</h1>
        <div></div>
      </div>

                           <form method="get" action="{{ route('profile.index') }}" class="bg-white rounded-lg shadow p-4 mb-5">
          <input type="hidden" name="view" value="tasks">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
              <input type="text" name="t_q" value="{{ $tasksFilters['tSearch'] ?? '' }}" placeholder="Поиск: задача, проект, исполнитель"
                     class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
              @php $tp = $tasksFilters['tPriority'] ?? ''; @endphp
              <select name="t_priority" class="w-full border border-gray-300 rounded-md px-3 py-2">
                <option value="" {{ $tp==='' ? 'selected' : '' }}>Любой приоритет</option>
                <option value="high" {{ $tp==='high' ? 'selected' : '' }}>Высокий</option>
                <option value="medium" {{ $tp==='medium' ? 'selected' : '' }}>Средний</option>
                <option value="low" {{ $tp==='low' ? 'selected' : '' }}>Низкий</option>
              </select>
            </div>
          </div>
          
          <div class="flex flex-col md:flex-row md:justify-start gap-3 mt-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Дата начала</label>
              <input type="date" name="t_start_date" value="{{ $tasksFilters['tStartDate'] ?? '' }}" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Дата окончания</label>
              <input type="date" name="t_end_date" value="{{ $tasksFilters['tEndDate'] ?? '' }}" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
          </div>
          <div class="mt-3 flex items-center gap-2">
            <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Применить</button>
            <a href="{{ route('profile.index', ['view'=>'tasks']) }}" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Сбросить</a>
          </div>
        </form>

      @php $list = $tasksPage ?? collect(); @endphp
      @if(($list instanceof \Illuminate\Support\Collection ? $list->count() : count($list)) === 0)
        <div class="bg-white rounded-xl border border-gray-100 p-10 text-center">
          <div class="text-lg font-semibold mb-1">Задач не найдено.</div>
          <p class="text-gray-600">Измени фильтры или сбрось поиск.</p>
          <div class="mt-3">
            <a href="{{ route('profile.index', ['view'=>'tasks']) }}" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Сбросить</a>
          </div>
        </div>
      @else
        <div class="space-y-3">
          @foreach($list as $it)
            @php
              $deadline = $it['deadline'];
              $isOverdue = $deadline instanceof \Carbon\Carbon ? $deadline->isPast() : false;
              $deadlineText = $deadline ? $deadline->format('d.m.Y') : 'Без срока';
              $priority = $it['priority'] ?? 'medium';
              $status = $it['status'] ?? 'new';
              $priorityMap = [ 'low' => 'bg-green-100 text-green-700', 'medium' => 'bg-yellow-100 text-yellow-800', 'high' => 'bg-red-100 text-red-700'];
              $statusMap = ['new'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-yellow-100 text-yellow-800','done'=>'bg-gray-100 text-gray-700','overdue'=>'bg-red-100 text-red-700'];
              $assignee = trim(($it['assignee_name'] ?? ''));
              $initials = $assignee !== '' ? collect(explode(' ', $assignee))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('') : '';
            @endphp
            <a href="{{ $it['url'] ?? '#' }}" class="block bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-lg transition transform hover:-translate-y-0.5">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="font-semibold text-gray-900 truncate" title="{{ $it['title'] }}">{{ $it['title'] }}</div>
                  <div class="text-sm text-gray-500 truncate" title="{{ $it['project_title'] }}">{{ $it['project_title'] }}</div>
                </div>
                <div class="flex items-center gap-2">
                  @if(!empty($initials))
                    <div class="w-7 h-7 rounded-full bg-gray-100 grid place-items-center text-xs text-gray-700" title="{{ $assignee }}">{{ $initials }}</div>
                  @endif
                </div>
              </div>
              <div class="mt-2 flex items-center justify-between text-sm">
                <div class="{{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-600' }}">Дедлайн: {{ $deadlineText }}</div>
                <div class="flex items-center gap-2">
                  <span class="text-xs px-2 py-1 rounded {{ $priorityMap[$priority] }}">{{ $priority==='high' ? 'Высокий' : ($priority==='medium' ? 'Средний' : 'Низкий') }}</span>
                  <span class="text-xs px-2 py-1 rounded {{ $statusMap[$status] }}">{{ $status==='new' ? 'Новая' : ($status==='in_progress' ? 'В работе' : ($status==='done' ? 'Выполнена' : 'Просрочена')) }}</span>
                </div>
              </div>
            </a>
          @endforeach
        </div>

        @php
          $tPage = $tasksFilters['tPage'] ?? 1;
          $tTotalPages = $tasksFilters['tTotalPages'] ?? 1;
        @endphp
        @if($tTotalPages > 1)
          <div class="mt-6 flex items-center justify-center gap-2">
            @php $qs = request()->query(); $qs['view']='tasks'; @endphp
            @if($tPage > 1)
              <a class="px-3 py-2 rounded border border-gray-300 hover:bg-gray-50" href="{{ route('profile.index', array_merge($qs, ['t_page'=>$tPage-1])) }}">Предыдущая</a>
            @endif
            <span class="px-3 py-2">Стр. {{ $tPage }} из {{ $tTotalPages }}</span>
            @if($tPage < $tTotalPages)
              <a class="px-3 py-2 rounded border border-gray-300 hover:bg-gray-50" href="{{ route('profile.index', array_merge($qs, ['t_page'=>$tPage+1])) }}">Следующая</a>
            @endif
          </div>
        @endif
      @endif

    @else
    <div class="flex items-start justify-between gap-4 mb-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Ближайшие проекты и задачи</h1>
        <p class="text-sm text-gray-600 mt-1">Ваши активные проекты и задачи с ближайшими сроками выполнения.</p>
      </div>
    </div>

    <form method="get" action="{{ route('profile.index') }}" class="bg-white rounded-lg shadow p-4 mb-5">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
        <div class="lg:col-span-2">
          <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Поиск по названию..."
                 class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
          <select name="type" class="w-full border border-gray-300 rounded-md px-3 py-2">
            <option value="all" {{ ($filters['type'] ?? 'all')==='all' ? 'selected' : '' }}>Все</option>
            <option value="project" {{ ($filters['type'] ?? '')==='project' ? 'selected' : '' }}>Проекты</option>
            <option value="task" {{ ($filters['type'] ?? '')==='task' ? 'selected' : '' }}>Задачи</option>
          </select>
        </div>
        <div>
                     <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2">
             <option value="" {{ ($filters['status'] ?? '')==='' ? 'selected' : '' }}>Любой статус</option>
             <option value="new" {{ ($filters['status'] ?? '')==='new' ? 'selected' : '' }}>Новый</option>
             <option value="active" {{ ($filters['status'] ?? '')==='active' ? 'selected' : '' }}>Активный</option>
             <option value="cancelled" {{ ($filters['status'] ?? '')==='cancelled' ? 'selected' : '' }}>Отменён</option>
           </select>
        </div>
        
        
        <div>
          <select name="range" class="w-full border border-gray-300 rounded-md px-3 py-2">
            <option value="7" {{ ($filters['range'] ?? 7)==7 ? 'selected' : '' }}>+7 дней</option>
            <option value="14" {{ ($filters['range'] ?? 7)==14 ? 'selected' : '' }}>+14 дней</option>
            <option value="30" {{ ($filters['range'] ?? 7)==30 ? 'selected' : '' }}>+30 дней</option>
          </select>
        </div>
      </div>
      <div class="mt-3 flex items-center gap-2">
        <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Применить</button>
        <a href="{{ route('profile.index') }}" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Сбросить фильтры</a>
      </div>
    </form>

    @php
      $items = $sorted ?? collect();
    @endphp

    @if(($items instanceof \Illuminate\Support\Collection ? $items->count() : count($items)) === 0)
      <div class="bg-white rounded-xl border border-gray-100 p-10 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="mx-auto mb-4 w-16 h-16 text-gray-300">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h8M8 11h8m-8 4h6M5 6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H5z"/>
        </svg>
        <div class="text-lg font-semibold mb-1">У вас нет проектов или задач с ближайшими сроками.</div>
        
        
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($items as $it)
          @php
            $deadline = $it['deadline'] ?? null;
            $isOverdue = $deadline instanceof \Carbon\Carbon ? $deadline->isPast() : false;
                         $deadlineText = $deadline ? $deadline->format('d.m.Y') : 'Без срока';
            $status = $it['status'] ?? 'in_progress';
            $priority = $it['priority'] ?? 'medium';
                         $statusMap = [
               'new' => 'bg-yellow-100 text-yellow-700',
               'active' => 'bg-green-100 text-green-700',
               'completed' => 'bg-blue-100 text-blue-700',
               'cancelled' => 'bg-red-100 text-red-700',
               'in_progress' => 'bg-yellow-100 text-yellow-800',
               'done' => 'bg-green-100 text-green-700',
               'overdue' => 'bg-red-100 text-red-700',
             ];
            $priorityMap = [
              'high' => 'text-red-600',
              'medium' => 'text-gray-700',
              'low' => 'text-gray-500',
            ];
          @endphp
          <a href="{{ $it['url'] ?? '#' }}" class="block bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-lg transition transform hover:-translate-y-0.5">
            <div class="flex items-start justify-between gap-3 mb-1">
              <div class="font-semibold text-gray-900">{{ $it['title'] }}</div>
                             <span class="text-xs px-2 py-1 rounded {{ $statusMap[$status] ?? 'bg-gray-100 text-gray-700' }}">{{ $status === 'new' ? 'Новый' : ($status === 'active' ? 'Активный' : ($status === 'completed' ? 'Завершён' : ($status === 'cancelled' ? 'Отменён' : ($status === 'done' ? 'Завершено' : ($status === 'in_progress' ? 'Активный' : ($status === 'overdue' ? 'Просрочено' : $status)))))) }}</span>
            </div>
                         <div class="text-sm font-semibold text-gray-900">Начало проекта: {{ $deadlineText }}</div>
            @if(!empty($it['description']))
              <div class="mt-2 text-sm text-gray-700 line-clamp-2">{{ $it['description'] }}</div>
            @endif
            <div class="mt-3 flex items-center justify-end">
              <span class="text-blue-700 font-semibold">Перейти →</span>
            </div>
          </a>
        @endforeach
      </div>
    @endif

    @endif

    
  </div>



