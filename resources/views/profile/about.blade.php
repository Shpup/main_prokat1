@extends('layouts.app')

@section('content')
<div class="max-w-[1800px] mx-auto px-8 py-6">
  @include('profile.partials._tabs')

  <!-- Блок 1 — Профиль пользователя -->
  <section class="bg-white rounded-xl shadow p-6 mb-6">
    <header class="flex items-center gap-2 mb-4 text-lg font-semibold text-gray-900">
      <span>👤</span>
      <span>Профиль пользователя</span>
    </header>
    <form action="{{ route('profile.about.updateInfo') }}" method="post">@csrf @method('PUT')
      <div class="flex items-start gap-6">
        <div class="flex flex-col items-center gap-2">
          <div class="w-24 h-24 rounded-full bg-gray-100 grid place-items-center text-3xl text-gray-400">👤</div>
          <button type="button" class="px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700">Загрузить фото</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 flex-1">
          <div>
            <label class="block text-sm text-gray-700 mb-1">Фамилия</label>
            <input type="text" name="last_name" value="{{ old('last_name',$u->last_name ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2">
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Имя</label>
            <input type="text" name="first_name" value="{{ old('first_name',$u->first_name ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2">
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Отчество</label>
            <input type="text" name="middle_name" value="{{ old('middle_name',$u->middle_name ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2">
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Дата рождения</label>
            <input type="date" name="birth_date" value="{{ old('birth_date',$u->birth_date ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2" disabled title="Скоро">
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Город</label>
            <input type="text" name="city" value="{{ old('city',$u->city ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2" disabled title="Скоро">
          </div>
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <button class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Сохранить изменения</button>
      </div>
    </form>
  </section>

  <!-- Блок 2 — Контакты -->
  <section class="bg-white rounded-xl shadow p-6 mb-6">
    <header class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2 text-lg font-semibold text-gray-900">
        <span>📞</span>
        <span>Контакты</span>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" id="addPhoneBtn" class="px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700">+ Телефон</button>
        <button type="button" id="addEmailBtn" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">+ E‑mail</button>
      </div>
    </header>

    @if(($u->phones->count() + $u->emails->count()) === 0)
      <div class="text-center text-gray-600 py-10">
        <div class="text-5xl mb-3">📭</div>
        <div>Пока ничего нет. Добавьте данные.</div>
      </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      @foreach($u->phones as $p)
        <div class="border border-gray-100 rounded-lg p-3 hover:shadow-sm transition">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="font-semibold">📱 {{ $p->value }}</div>
              @if($p->comment)
                <div class="text-sm text-gray-500">{{ $p->comment }}</div>
              @endif
            </div>
            <form id="del-phone-{{ $p->id }}" action="{{ route('profile.phones.destroy',$p) }}" method="post">@csrf @method('DELETE')
              <button class="text-red-600 hover:text-red-700" title="Удалить">🗑</button>
            </form>
          </div>
          <form action="{{ route('profile.phones.update',$p) }}" method="post" class="mt-2">@csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <input name="value" value="{{ $p->value }}" class="border border-gray-300 rounded-md px-3 py-2">
              <input name="comment" value="{{ $p->comment }}" class="border border-gray-300 rounded-md px-3 py-2" placeholder="Комментарий">
            </div>
            <div class="mt-2 text-right">
              <button class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">✏️ Сохранить</button>
            </div>
          </form>
        </div>
      @endforeach

      @foreach($u->emails as $e)
        <div class="border border-gray-100 rounded-lg p-3 hover:shadow-sm transition">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="font-semibold">✉️ {{ $e->value }}</div>
              <div class="text-sm text-gray-500">{{ $e->comment }}</div>
            </div>
            <form id="del-email-{{ $e->id }}" action="{{ route('profile.emails.destroy',$e) }}" method="post">@csrf @method('DELETE')
              <button class="text-red-600 hover:text-red-700" title="Удалить">🗑</button>
            </form>
          </div>
          <form action="{{ route('profile.emails.update',$e) }}" method="post" class="mt-2">@csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
              <input name="value" type="email" value="{{ $e->value }}" class="border border-gray-300 rounded-md px-3 py-2">
              <input name="comment" value="{{ $e->comment }}" class="border border-gray-300 rounded-md px-3 py-2" placeholder="Комментарий">
              <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_primary" value="1" {{ $e->is_primary?'checked':'' }}> Для уведомлений
              </label>
            </div>
            <div class="mt-2 text-right">
              <button class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">✏️ Сохранить</button>
            </div>
          </form>
        </div>
      @endforeach
    </div>
  </section>

  <!-- Блок 3 — Учётная запись -->
  <section class="bg-white rounded-xl shadow p-6 mb-6">
    <header class="flex items-center gap-2 mb-4 text-lg font-semibold text-gray-900">
      <span>🔐</span>
      <span>Учётная запись</span>
    </header>
    <div class="mb-3">
      <label class="block text-sm text-gray-700 mb-1">Логин</label>
      <input type="text" value="{{ $u->login ?? $u->email }}" readonly class="w-full bg-gray-50 border border-gray-200 rounded-md px-3 py-2 text-gray-700">
    </div>
    <form action="{{ route('profile.about.updatePassword') }}" method="post">@csrf @method('PUT')
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
          <label class="block text-sm text-gray-700 mb-1">Текущий пароль</label>
          <input type="password" name="current_password" class="w-full border border-gray-300 rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm text-gray-700 mb-1">Новый пароль</label>
          <input type="password" name="password" class="w-full border border-gray-300 rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm text-gray-700 mb-1">Подтверждение</label>
          <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-md px-3 py-2">
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <button class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Обновить пароль</button>
      </div>
    </form>
  </section>

  <!-- Блок 4 — Документы -->
  <section class="bg-white rounded-xl shadow p-6 mb-6">
    <header class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2 text-lg font-semibold text-gray-900">
        <span>📄</span>
        <span>Документы</span>
      </div>
      <button type="button" data-open-doc="select" class="px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700">+ Добавить документ</button>
    </header>

    @if($u->documents->count() === 0)
      <div class="text-center text-gray-600 py-10">
        <div class="text-5xl mb-3">🗂️</div>
        <div>Пока ничего нет. Добавьте данные.</div>
      </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
      @foreach($u->documents as $d)
        <div class="border border-gray-100 rounded-lg p-4 hover:shadow-sm transition">
          <div class="flex items-center justify-between mb-2">
            <div class="font-semibold">{{ __('types.'.$d->type) }}</div>
            <div class="text-green-600">✔</div>
          </div>
          <div class="text-sm text-gray-700">Серия: {{ $d->series ?? '—' }}</div>
          <div class="text-sm text-gray-700">Номер: {{ $d->number ?? '—' }}</div>
          <div class="text-sm text-gray-700">Выдан: {{ optional($d->issued_at)->format('d.m.Y') ?? '—' }} • {{ $d->issued_by ?? '—' }}</div>
          <div class="text-sm text-gray-700">Действует до: {{ optional($d->expires_at)->format('d.m.Y') ?? '—' }}</div>
          <div class="mt-3 flex items-center gap-2">
            <button class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50" data-edit-doc="{{ $d->id }}">Изменить</button>
            <form action="{{ route('profile.documents.destroy',$d) }}" method="post">@csrf @method('DELETE')
              <button class="px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700">Удалить</button>
            </form>
          </div>
          <template class="payload">
            {!! json_encode([
              'id'=>$d->id,'type'=>$d->type,'series'=>$d->series,'number'=>$d->number,
              'issued_at'=>optional($d->issued_at)->format('Y-m-d'),
              'issued_by'=>$d->issued_by,'expires_at'=>optional($d->expires_at)->format('Y-m-d'),
              'comment'=>$d->comment,
            ]) !!}
          </template>
        </div>
      @endforeach
    </div>
  </section>

  <!-- Блок 5 — Дополнительно (визуальный каркас) -->
  <section class="bg-white rounded-xl shadow p-6">
    <header class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2 text-lg font-semibold text-gray-900">
        <span>➕</span>
        <span>Дополнительно</span>
      </div>
      <button type="button" id="openExtra" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">Редактировать</button>
    </header>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      <div class="text-sm text-gray-700"><span class="text-gray-500">Должность:</span> —</div>
      <div class="text-sm text-gray-700"><span class="text-gray-500">Отдел:</span> —</div>
      <div class="text-sm text-gray-700"><span class="text-gray-500">Дата начала работы:</span> —</div>
      <div class="text-sm text-gray-700"><span class="text-gray-500">Навыки:</span>
        <div class="mt-1 flex flex-wrap gap-2">
          <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs">—</span>
        </div>
      </div>
      <div class="md:col-span-2 text-sm text-gray-700"><span class="text-gray-500">Комментарий:</span> —</div>
    </div>
  </section>

  <!-- Modals: добавить телефон / email / документ / доп.инфо (каркас) -->
  <div id="modalAddPhone" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <div class="relative z-10 max-w-md mx-auto mt-24 bg-white rounded-xl shadow-lg p-6">
      <div class="flex items-start justify-between mb-4"><div class="text-lg font-semibold">Добавить телефон</div><button class="text-gray-500" data-close>✕</button></div>
      <form method="post" action="{{ route('profile.phones.store') }}">@csrf
        <div class="grid grid-cols-1 gap-2">
          <input name="value" placeholder="+7..." class="border border-gray-300 rounded-md px-3 py-2" required>
          <input name="comment" placeholder="Комментарий" class="border border-gray-300 rounded-md px-3 py-2">
        </div>
        <div class="mt-3 flex justify-end gap-2">
          <button type="button" class="px-3 py-1.5 rounded-md border border-gray-300" data-close>Отмена</button>
          <button class="px-3 py-1.5 rounded-md bg-blue-600 text-white">Добавить</button>
        </div>
      </form>
    </div>
  </div>

  <div id="modalAddEmail" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <div class="relative z-10 max-w-md mx-auto mt-24 bg-white rounded-xl shadow-lg p-6">
      <div class="flex items-start justify-between mb-4"><div class="text-lg font-semibold">Добавить E‑mail</div><button class="text-gray-500" data-close>✕</button></div>
      <form method="post" action="{{ route('profile.emails.store') }}">@csrf
        <div class="grid grid-cols-1 gap-2">
          <input name="value" type="email" placeholder="you@example.com" class="border border-gray-300 rounded-md px-3 py-2" required>
          <input name="comment" placeholder="Комментарий" class="border border-gray-300 rounded-md px-3 py-2">
          <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="is_primary" value="1"> Для уведомлений</label>
        </div>
        <div class="mt-3 flex justify-end gap-2">
          <button type="button" class="px-3 py-1.5 rounded-md border border-gray-300" data-close>Отмена</button>
          <button class="px-3 py-1.5 rounded-md bg-blue-600 text-white">Добавить</button>
        </div>
      </form>
    </div>
  </div>

  <div id="docModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <div class="relative z-10 max-w-md mx-auto mt-24 bg-white rounded-xl shadow-lg p-6">
      <div class="flex items-start justify-between mb-4"><div class="text-lg font-semibold"><span id="docTitle">Документ</span></div><button class="text-gray-500" data-close>✕</button></div>
      <form id="docForm" method="post"
            action="{{ route('profile.documents.store') }}"
            data-store="{{ route('profile.documents.store') }}"
            data-base-update="{{ route('profile.documents.update','__ID__') }}">@csrf
        <input type="hidden" name="_method" value="POST">
        <input type="hidden" name="type" id="docType">
        <div class="grid grid-cols-1 gap-2">
          <input name="series" id="docSeries" placeholder="Серия" class="border border-gray-300 rounded-md px-3 py-2">
          <input name="number" id="docNumber" placeholder="Номер" class="border border-gray-300 rounded-md px-3 py-2">
          <input type="date" name="issued_at" id="docIssuedAt" class="border border-gray-300 rounded-md px-3 py-2">
          <input name="issued_by" id="docIssuedBy" placeholder="Кем выдан" class="border border-gray-300 rounded-md px-3 py-2">
          <input type="date" name="expires_at" id="docExpiresAt" class="border border-gray-300 rounded-md px-3 py-2">
          <input name="comment" id="docComment" placeholder="Комментарий" class="border border-gray-300 rounded-md px-3 py-2">
        </div>
        <div class="mt-3 flex justify-end gap-2">
          <button type="button" class="px-3 py-1.5 rounded-md border border-gray-300" data-close>Отмена</button>
          <button class="px-3 py-1.5 rounded-md bg-blue-600 text-white">Сохранить</button>
        </div>
      </form>
    </div>
  </div>

  <div id="extraModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <div class="relative z-10 max-w-lg mx-auto mt-24 bg-white rounded-xl shadow-lg p-6">
      <div class="flex items-start justify-between mb-4"><div class="text-lg font-semibold">Дополнительно</div><button class="text-gray-500" data-close>✕</button></div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <input placeholder="Должность" class="border border-gray-300 rounded-md px-3 py-2">
        <input placeholder="Отдел" class="border border-gray-300 rounded-md px-3 py-2">
        <input type="date" placeholder="Дата начала" class="border border-gray-300 rounded-md px-3 py-2">
        <input placeholder="Навыки через запятую" class="border border-gray-300 rounded-md px-3 py-2">
        <textarea placeholder="Комментарий" class="md:col-span-2 border border-gray-300 rounded-md px-3 py-2"></textarea>
      </div>
      <div class="mt-3 flex justify-end gap-2">
        <button type="button" class="px-3 py-1.5 rounded-md border border-gray-300" data-close>Закрыть</button>
        <button type="button" class="px-3 py-1.5 rounded-md bg-blue-600 text-white" data-close>Сохранить</button>
      </div>
    </div>
  </div>

  <!-- mini-scripts: модалки и doc-редактор -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const bindModal = (openBtn, modalId) => {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        if (openBtn) openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
        modal.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', () => modal.classList.add('hidden')));
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.add('hidden'); });
      };
      bindModal(document.getElementById('addPhoneBtn'), 'modalAddPhone');
      bindModal(document.getElementById('addEmailBtn'), 'modalAddEmail');
      bindModal(document.getElementById('openExtra'), 'extraModal');

      // Документы: открытие добавления по типу
      document.querySelectorAll('[data-open-doc]').forEach(btn => {
        btn.addEventListener('click', () => {
          const type = btn.getAttribute('data-open-doc');
          const modal = document.getElementById('docModal');
          if (!modal) return;
          modal.classList.remove('hidden');
          const t = document.getElementById('docType');
          if (type && type !== 'select') {
            t.value = type;
            document.getElementById('docTitle').textContent = 'Документ: ' + type;
          }
        });
      });

      // Документы: редактирование
      document.querySelectorAll('[data-edit-doc]').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-edit-doc');
          const host = btn.closest('.border');
          const payload = host && host.querySelector('template.payload');
          if (!payload) return;
          const data = JSON.parse(payload.innerHTML.trim());
          const modal = document.getElementById('docModal');
          modal.classList.remove('hidden');
          document.querySelector('#docForm input[name=_method]').value = 'PUT';
          document.getElementById('docForm').setAttribute('action', document.getElementById('docForm').dataset.baseUpdate.replace('__ID__', id));
          document.getElementById('docType').value = data.type;
          document.getElementById('docSeries').value = data.series ?? '';
          document.getElementById('docNumber').value = data.number ?? '';
          document.getElementById('docIssuedAt').value = data.issued_at ?? '';
          document.getElementById('docIssuedBy').value = data.issued_by ?? '';
          document.getElementById('docExpiresAt').value = data.expires_at ?? '';
          document.getElementById('docComment').value = data.comment ?? '';
          document.getElementById('docTitle').textContent = 'Редактировать документ';
        });
      });
    });
  </script>
</div>
@endsection


