<nav class="flex items-center gap-6 mb-4">
  <a href="{{ route('profile.index', request()->only('range')) }}"
     class="border-b-2 pb-1 {{ request()->routeIs('profile.index') && !request('view') ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-700' }}">
     Ближайшие проекты и задачи
  </a>
  <a href="{{ route('profile.index', ['view'=>'projects'] + request()->except('range')) }}"
     class="border-b-2 pb-1 {{ request('view')==='projects' ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-700' }}">
     Проекты
  </a>
  <a href="{{ route('profile.index', ['view'=>'tasks'] + request()->except('range')) }}"
     class="border-b-2 pb-1 {{ request('view')==='tasks' ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-700' }}">
     Задачи
  </a>
  <a href="{{ route('profile.about.edit') }}"
     class="border-b-2 pb-1 {{ request()->routeIs('profile.about.*') ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-700' }}">
     О себе
  </a>
</nav>


