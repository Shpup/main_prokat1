@include('layouts.navigation')
@vite(['resources/css/app.css', 'resources/css/lk-about.css', 'resources/js/app.js', 'resources/js/lk-about.js'])

  <div class="max-w-[1800px] mx-auto px-8 py-6">
    @include('profile.partials._tabs')

    @if(request('view')==='projects')
      <div class="flex items-start justify-between gap-4 mb-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Проекты</h1>
        <div></div>
      </div>

      <div x-data="projectsFilter()" class="bg-white rounded-lg shadow p-4 mb-5">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
          <div class="lg:col-span-2 relative">
            <input type="text" x-model="filters.search" placeholder="Поиск по названию или месту..."
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                   x-on:input="searchSuggestions()"
                   x-on:keydown="handleKeydown($event)">
            <div x-show="showSuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto">
              <!-- Заглушка "Загружаются проекты" -->
              <div x-show="isLoading" class="px-3 py-4 text-center text-gray-500">
                <div class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Загружаются проекты...
                </div>
              </div>
              
              <!-- Сообщение "Нет подходящих проектов" -->
              <div x-show="!isLoading && suggestions.length === 0 && filters.search.length > 0" class="px-4 py-6 text-center">
                <div class="mb-3">
                  <svg class="mx-auto h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <div class="text-base font-medium text-gray-900 mb-1">Проекты не найдены</div>
                <div class="text-sm text-gray-500">Попробуйте изменить поисковый запрос или проверьте правильность написания</div>
              </div>
              
              <!-- Список проектов -->
              <template x-for="(suggestion, index) in suggestions" :key="index">
                <div class="px-3 py-2 cursor-pointer hover:bg-gray-100 border-b border-gray-100 last:border-b-0"
                     x-on:click="selectSuggestion(suggestion)"
                     x-bind:class="{ 'bg-blue-50': selectedIndex === index }">
                  <div class="font-medium" x-text="suggestion.title"></div>
                  <div class="text-sm text-gray-500" x-text="suggestion.location"></div>
                  <div class="text-xs text-gray-400" x-text="suggestion.date"></div>
                </div>
              </template>
            </div>
          </div>
          <div>
            <select x-model="filters.status" class="w-full border border-gray-300 rounded-md px-3 py-2">
              <option value="">Статус</option>
              <option value="new">Новый</option>
              <option value="active">Активный</option>
              <option value="completed">Завершён</option>
            </select>
          </div>
        </div>
        
        <div class="flex flex-col md:flex-row md:justify-start gap-3 mt-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Дата начала</label>
            <input type="date" x-model="filters.startDate" 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Дата окончания</label>
            <input type="date" x-model="filters.endDate" 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
          </div>
        </div>
        
        <div class="mt-3 flex items-center gap-2">
          <button @click="applyFilters()" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Применить</button>
          <button @click="resetFilters()" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Сбросить</button>
        </div>
      </div>

      <div x-data="projectsList()" x-init="init()">
        <div x-show="loading" class="text-center py-8">
          <div class="text-gray-600">Загрузка проектов...</div>
        </div>
        
        <div x-show="!loading && projects.length === 0" class="bg-white rounded-xl border border-gray-100 p-10 text-center">
          <div class="text-lg font-semibold mb-1">Проектов не найдено.</div>
          <p class="text-gray-600">Измени фильтры или сбрось поиск.</p>
          <div class="mt-3">
            <button @click="resetFilters()" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Сбросить</button>
          </div>
        </div>
        
        <div x-show="!loading && projects.length > 0" class="space-y-6">
          <template x-for="(group, date) in groupedProjects" :key="date">
            <div>
              <h3 x-show="date !== 'no_date'" class="text-sm font-semibold text-gray-700" x-text="date"></h3>
              <div class="space-y-3">
                <template x-for="project in group" :key="project.id">
                  <a :href="project.url" class="block bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-lg transition transform hover:-translate-y-0.5">
                    <div class="flex items-start justify-between gap-3">
                      <div class="font-semibold text-gray-900 truncate" :title="project.title" x-text="project.title"></div>
                      <div class="text-sm text-gray-500 truncate" :title="project.location" x-text="project.location"></div>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-sm text-gray-700">
                      <div>
                        Время: <span x-text="project.time_range || 'Время не назначено'"></span>
                        <span class="ml-3">Оплата: <span x-text="project.payment || 'Не указана'"></span></span>
                      </div>
                      <span class="text-xs px-2 py-1 rounded" :class="getStatusClass(project.status)" x-text="getStatusText(project.status)"></span>
                    </div>
                  </a>
                </template>
              </div>
            </div>
          </template>
        </div>
        
        <div x-show="!loading && totalPages > 1" class="mt-6 flex items-center justify-center gap-2">
          <button x-show="currentPage > 1" @click="changePage(currentPage - 1)" class="px-3 py-2 rounded border border-gray-300 hover:bg-gray-50">Предыдущая</button>
          <span class="px-3 py-2">Стр. <span x-text="currentPage"></span> из <span x-text="totalPages"></span></span>
          <button x-show="currentPage < totalPages" @click="changePage(currentPage + 1)" class="px-3 py-2 rounded border border-gray-300 hover:bg-gray-50">Следующая</button>
        </div>
      </div>

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

    <div x-data="upcomingFilter()" class="bg-white rounded-lg shadow p-4 mb-5">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
        <div class="lg:col-span-2 relative">
          <input type="text" x-model="filters.search" placeholder="Поиск по названию..."
                 class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                 x-on:input="searchSuggestions()"
                 x-on:keydown="handleKeydown($event)"
                 x-on:keydown.enter.prevent="applyFilters()">
          <div x-show="showSuggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto">
            <!-- Заглушка "Загружаются проекты" -->
            <div x-show="isLoading" class="px-3 py-4 text-center text-gray-500">
              <div class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Загружаются проекты...
              </div>
            </div>
            
            <!-- Сообщение "Нет подходящих проектов" -->
            <div x-show="!isLoading && suggestions.length === 0 && filters.search.length > 0" class="px-4 py-6 text-center">
              <div class="mb-3">
                <svg class="mx-auto h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <div class="text-base font-medium text-gray-900 mb-1">Проекты не найдены</div>
              <div class="text-sm text-gray-500">Попробуйте изменить поисковый запрос или проверьте правильность написания</div>
            </div>
            
            <!-- Список проектов -->
            <template x-for="(suggestion, index) in suggestions" :key="index">
              <div class="px-3 py-2 cursor-pointer hover:bg-gray-100 border-b border-gray-100 last:border-b-0"
                   x-on:click="selectSuggestion(suggestion)"
                   x-bind:class="{ 'bg-blue-50': selectedIndex === index }">
                <div class="font-medium" x-text="suggestion.title"></div>
                <div class="text-sm text-gray-500" x-text="suggestion.location"></div>
                <div class="text-xs text-gray-400" x-text="suggestion.date"></div>
              </div>
            </template>
          </div>
        </div>
        <div>
          <select x-model="filters.type" class="w-full border border-gray-300 rounded-md px-3 py-2">
            <option value="all">Все</option>
            <option value="project">Проекты</option>
            <option value="task">Задачи</option>
          </select>
        </div>
        <div>
          <select x-model="filters.status" class="w-full border border-gray-300 rounded-md px-3 py-2">
            <option value="">Любой статус</option>
            <option value="new">Новый</option>
            <option value="active">Активный</option>
          </select>
        </div>
        <div>
          <select x-model="filters.range" class="w-full border border-gray-300 rounded-md px-3 py-2">
            <option value="7">+7 дней</option>
            <option value="14">+14 дней</option>
            <option value="30">+30 дней</option>
          </select>
        </div>
      </div>
      <div class="mt-3 flex items-center gap-2">
        <button type="button" @click="applyFilters($event)" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Применить</button>
        <button type="button" @click="resetFilters($event)" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Сбросить фильтры</button>
      </div>
    </div>

    <div x-data="upcomingList()" x-init="init()">
      <div x-show="loading" class="text-center py-8">
        <div class="text-gray-600">Загрузка проектов и задач...</div>
      </div>
      
      <div x-show="!loading && items.length === 0" class="bg-white rounded-xl border border-gray-100 p-10 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="mx-auto mb-4 w-16 h-16 text-gray-300">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h8M8 11h8m-8 4h6M5 6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H5z"/>
        </svg>
        <div class="text-lg font-semibold mb-1">У вас нет проектов или задач с ближайшими сроками.</div>
      </div>
      
      <div x-show="!loading && items.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <template x-for="item in items" :key="item.id">
          <a :href="item.url" class="block bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-lg transition transform hover:-translate-y-0.5">
            <div class="flex items-start justify-between gap-3 mb-1">
              <div class="font-semibold text-gray-900" x-text="item.title"></div>
              <span class="text-xs px-2 py-1 rounded" :class="getStatusClass(item.status)" x-text="getStatusText(item.status)"></span>
            </div>
            <div class="text-sm font-semibold text-gray-900" x-text="'Начало проекта: ' + (item.deadline || 'Без срока')"></div>
            <div x-show="item.description" class="mt-2 text-sm text-gray-700 line-clamp-2" x-text="item.description"></div>
            <div class="mt-3 flex items-center justify-end">
              <span class="text-blue-700 font-semibold">Перейти →</span>
            </div>
          </a>
        </template>
      </div>
    </div>

    @endif

    
  </div>

<script>
const formatDateLocal = (date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}
function projectsFilter() {
    return {
        filters: {
            search: '',
            status: '',
            startDate: formatDateLocal(new Date()),
            endDate: formatDateLocal(new Date(Date.now() + 7 * 24 * 60 * 60 * 1000))
        },
        suggestions: [],
        showSuggestions: false,
        selectedIndex: -1,
        isLoading: false,
        searchTimeout: null,
        
        async searchSuggestions() {
            // Очищаем предыдущий таймаут
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
            
            // Если поиск пустой, сразу очищаем
            if (this.filters.search.length < 1) {
                this.suggestions = [];
                this.showSuggestions = false;
                this.isLoading = false;
                return;
            }
            
            // Показываем заглушку "Загружаются проекты"
            this.isLoading = true;
            this.showSuggestions = true;
            this.suggestions = [];
            
            // Добавляем обработчик клика вне области поиска
            this.setupClickOutsideHandler();
            
            // Устанавливаем новый таймаут (300мс задержка)
            this.searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`{{ route('profile.autocomplete') }}?q=${encodeURIComponent(this.filters.search)}&section=projects`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        this.suggestions = data.suggestions || [];
                        this.showSuggestions = true; // Всегда показываем блок, даже если нет результатов
                        this.selectedIndex = -1;
                    }
                } catch (error) {
                    console.error('Ошибка автодополнения:', error);
                    this.suggestions = [];
                    this.showSuggestions = false;
                } finally {
                    this.isLoading = false;
                }
            }, 300); // 300мс задержка
        },
        
        selectSuggestion(suggestion) {
            this.filters.search = suggestion.title;
            
            // Автоматически устанавливаем даты проекта, если они есть
            if (suggestion.date) {
                // Парсим дату из формата "dd.mm.yyyy"
                const dateParts = suggestion.date.split('.');
                if (dateParts.length === 3) {
                    // Правильный порядок: год, месяц (0-based), день
                    const day = parseInt(dateParts[0]);
                    const month = parseInt(dateParts[1]) - 1; // Месяц начинается с 0
                    const year = parseInt(dateParts[2]);
                    
                    const projectDate = new Date(year, month, day);
                    
                    // Устанавливаем дату начала как дату проекта
                    this.filters.startDate = formatDateLocal(projectDate);
                    
                    // Устанавливаем дату окончания как дату проекта + 7 дней
                    const endDate = new Date(projectDate);
                    endDate.setDate(endDate.getDate() + 7);
                    this.filters.endDate = formatDateLocal(endDate);
                }
            }
            
            this.showSuggestions = false;
            this.suggestions = [];
            this.selectedIndex = -1;
        },
        
        handleKeydown(event) {
            if (!this.showSuggestions) return;
            
            switch(event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                    break;
                case 'Enter':
                    event.preventDefault();
                    if (this.selectedIndex >= 0 && this.suggestions[this.selectedIndex]) {
                        this.selectSuggestion(this.suggestions[this.selectedIndex]);
                    }
                    break;
                case 'Escape':
                    this.showSuggestions = false;
                    this.selectedIndex = -1;
                    break;
            }
        },
        
        applyFilters() {
            // Обновляем глобальные фильтры для Alpine.js
            window.projectsFilters = this.filters;
            // Перезагружаем список проектов
            if (window.projectsListComponent) {
                window.projectsListComponent.loadProjects();
            }
            this.showSuggestions = false;
        },
        
        resetFilters() {
            this.filters = {
                search: '',
                status: '',
                startDate: formatDateLocal(new Date()),
                endDate: formatDateLocal(new Date(Date.now() + 7 * 24 * 60 * 60 * 1000))
            };
            this.suggestions = [];
            this.showSuggestions = false;
            this.selectedIndex = -1;
            this.applyFilters();
        },
        
        setupClickOutsideHandler() {
            // Удаляем предыдущий обработчик, если он есть
            if (this.clickOutsideHandler) {
                document.removeEventListener('click', this.clickOutsideHandler);
            }
            
            // Создаем новый обработчик
            this.clickOutsideHandler = (event) => {
                const searchContainer = event.target.closest('[x-data*="projectsFilter"]');
                if (!searchContainer) {
                    this.showSuggestions = false;
                    this.selectedIndex = -1;
                    document.removeEventListener('click', this.clickOutsideHandler);
                }
            };
            
            // Добавляем обработчик с небольшой задержкой, чтобы не сработал сразу
            setTimeout(() => {
                document.addEventListener('click', this.clickOutsideHandler);
            }, 100);
        }
    }
}

function projectsList() {
    return {
        loading: true,
        projects: [],
        groupedProjects: {},
        currentPage: 1,
        totalPages: 1,
        
        init() {
            this.loadProjects();
            // Сохраняем ссылку на компонент для обновления из фильтров
            window.projectsListComponent = this;
        },
        
        async loadProjects() {
            this.loading = true;
            
            try {
                const filters = window.projectsFilters || {
                    search: '',
                    status: '',
                    startDate: formatDateLocal(new Date()),
                    endDate: formatDateLocal(new Date(Date.now() + 7 * 24 * 60 * 60 * 1000))
                };
                
                const params = new URLSearchParams({
                    view: 'projects',
                    p_q: filters.search,
                    p_status: filters.status,
                    p_start_date: filters.startDate,
                    p_end_date: filters.endDate,
                    p_page: this.currentPage
                });
                
                const response = await fetch(`{{ route('profile.index') }}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.projects = data.projects || [];
                    this.groupedProjects = data.groupedProjects || {};
                    this.currentPage = data.currentPage || 1;
                    this.totalPages = data.totalPages || 1;
                } else {
                    console.error('Ошибка загрузки проектов');
                }
            } catch (error) {
                console.error('Ошибка загрузки проектов:', error);
            } finally {
                this.loading = false;
            }
        },
        
        changePage(page) {
            this.currentPage = page;
            this.loadProjects();
        },
        
        getStatusClass(status) {
            const statusMap = {
                'new': 'bg-yellow-100 text-yellow-700',
                'active': 'bg-green-100 text-green-700',
                'completed': 'bg-blue-100 text-blue-700'
            };
            return statusMap[status] || 'bg-gray-100 text-gray-700';
        },
        
        getStatusText(status) {
            const statusMap = {
                'new': 'Новый',
                'active': 'Активный',
                'completed': 'Завершён'
            };
            return statusMap[status] || status;
        }
    }
}

function upcomingFilter() {
    return {
        filters: {
            search: '',
            type: 'all',
            status: '',
            range: '7'
        },
        suggestions: [],
        showSuggestions: false,
        selectedIndex: -1,
        isLoading: false,
        searchTimeout: null,

        async searchSuggestions() {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            if (this.filters.search.length < 1) {
                this.suggestions = [];
                this.showSuggestions = false;
                this.isLoading = false;
                return;
            }

            this.isLoading = true;
            this.showSuggestions = true;
            this.suggestions = [];
            
            // Добавляем обработчик клика вне области поиска
            this.setupClickOutsideHandler();

            this.searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`{{ route('profile.autocomplete') }}?q=${encodeURIComponent(this.filters.search)}&section=upcoming`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        this.suggestions = data.suggestions || [];
                        this.showSuggestions = true; // Всегда показываем блок, даже если нет результатов
                        this.selectedIndex = -1;
                    }
                } catch (error) {
                    console.error('Ошибка автодополнения:', error);
                    this.suggestions = [];
                    this.showSuggestions = false;
                } finally {
                    this.isLoading = false;
                }
            }, 300);
        },

        selectSuggestion(suggestion) {
            this.filters.search = suggestion.title;
            this.showSuggestions = false;
            this.suggestions = [];
            this.selectedIndex = -1;
        },

        handleKeydown(event) {
            if (!this.showSuggestions) return;
            
            switch(event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                    break;
                case 'Enter':
                    event.preventDefault();
                    if (this.selectedIndex >= 0 && this.suggestions[this.selectedIndex]) {
                        this.selectSuggestion(this.suggestions[this.selectedIndex]);
                    }
                    break;
                case 'Escape':
                    this.showSuggestions = false;
                    this.selectedIndex = -1;
                    break;
            }
        },

        applyFilters(event) {
            if (event && event.preventDefault) event.preventDefault();
            // Обновляем глобальные фильтры для Alpine.js
            window.upcomingFilters = this.filters;
            // Перезагружаем список проектов
            if (window.upcomingListComponent) {
                window.upcomingListComponent.loadItems();
            }
            this.showSuggestions = false;
        },

        resetFilters(event) {
            if (event && event.preventDefault) event.preventDefault();
            this.filters = {
                search: '',
                type: 'all',
                status: '',
                range: '7'
            };
            this.suggestions = [];
            this.showSuggestions = false;
            this.selectedIndex = -1;
            this.applyFilters();
        },
        
        setupClickOutsideHandler() {
            // Удаляем предыдущий обработчик, если он есть
            if (this.clickOutsideHandler) {
                document.removeEventListener('click', this.clickOutsideHandler);
            }
            
            // Создаем новый обработчик
            this.clickOutsideHandler = (event) => {
                const searchContainer = event.target.closest('[x-data*="upcomingFilter"]');
                if (!searchContainer) {
                    this.showSuggestions = false;
                    this.selectedIndex = -1;
                    document.removeEventListener('click', this.clickOutsideHandler);
                }
            };
            
            // Добавляем обработчик с небольшой задержкой, чтобы не сработал сразу
            setTimeout(() => {
                document.addEventListener('click', this.clickOutsideHandler);
            }, 100);
        }
    }
}

function upcomingList() {
    return {
        loading: true,
        items: [],
        currentPage: 1,
        totalPages: 1,
        
        init() {
            // Загружаем по умолчанию проекты с +7 дней
            this.loadItemsWithDefaults();
            // Сохраняем ссылку на компонент для обновления из фильтров
            window.upcomingListComponent = this;
        },
        
        async loadItemsWithDefaults() {
            this.loading = true;
            
            try {
                const params = new URLSearchParams({
                    view: 'upcoming',
                    u_q: '',
                    u_type: 'all',
                    u_status: '',
                    u_range: '7',
                    u_page: this.currentPage
                });
                
                const response = await fetch(`{{ route('profile.index') }}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.items = data.items || [];
                    this.currentPage = data.currentPage || 1;
                    this.totalPages = data.totalPages || 1;
                } else {
                    console.error('Ошибка загрузки проектов и задач');
                }
            } catch (error) {
                console.error('Ошибка загрузки проектов и задач:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async loadItems() {
            this.loading = true;
            
            try {
                const filters = window.upcomingFilters || {
                    search: '',
                    type: 'all',
                    status: '',
                    range: '7'
                };
                
                const params = new URLSearchParams({
                    view: 'upcoming',
                    u_q: filters.search,
                    u_type: filters.type,
                    u_status: filters.status,
                    u_range: filters.range,
                    u_page: this.currentPage
                });
                
                const response = await fetch(`{{ route('profile.index') }}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.items = data.items || [];
                    this.currentPage = data.currentPage || 1;
                    this.totalPages = data.totalPages || 1;
                } else {
                    console.error('Ошибка загрузки проектов и задач');
                }
            } catch (error) {
                console.error('Ошибка загрузки проектов и задач:', error);
            } finally {
                this.loading = false;
            }
        },
        
        changePage(page) {
            this.currentPage = page;
            this.loadItems();
        },
        
        getStatusClass(status) {
            const statusMap = {
                'new': 'bg-yellow-100 text-yellow-700',
                'active': 'bg-green-100 text-green-700',
                'completed': 'bg-blue-100 text-blue-700',
                'in_progress': 'bg-yellow-100 text-yellow-800',
                'done': 'bg-green-100 text-green-700',
                'overdue': 'bg-red-100 text-red-700'
            };
            return statusMap[status] || 'bg-gray-100 text-gray-700';
        },
        
        getStatusText(status) {
            const statusMap = {
                'new': 'Новый',
                'active': 'Активный',
                'completed': 'Завершён',
                'in_progress': 'В работе',
                'done': 'Выполнена',
                'overdue': 'Просрочена'
            };
            return statusMap[status] || status;
        }
    }
}
</script>



