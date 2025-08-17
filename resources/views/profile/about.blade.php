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
         <form id="profileForm" action="{{ route('profile.about.updateInfo') }}" method="post">@csrf @method('PUT')
                           <div class="flex items-start gap-6">
          <div class="flex flex-col items-center gap-2">
            <div class="w-24 h-24 rounded-full bg-gray-100 grid place-items-center text-3xl text-gray-400">👤</div>
            <button type="button" class="px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700">Загрузить фото</button>
          </div>
          <div class="flex-1">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
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
                 <input type="date" name="birth_date" value="{{ old('birth_date',$u->profile->birth_date ? $u->profile->birth_date->format('Y-m-d') : '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2">
               </div>
               <div>
                 <label class="block text-sm text-gray-700 mb-1">Город</label>
                 <input type="text" name="city" value="{{ old('city',$u->profile->city ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2">
               </div>
            </div>
            <div class="mt-4 flex justify-end">
              <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Сохранить изменения</button>
            </div>
          </div>
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

     <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
       <!-- Основные контакты -->
       <div>
         <h3 class="text-md font-semibold text-gray-800 mb-3">Основные контакты</h3>
         
         @if(!$u->email && !$u->phone)
           <div class="text-center text-gray-600 py-8">
             <div class="text-4xl mb-2">📭</div>
             <div class="text-sm">Пока ничего нет. Добавьте данные.</div>
           </div>
         @endif

         <div class="space-y-3">
           <!-- Основной телефон -->
                       <div class="contact-item border border-gray-200 rounded-lg p-4 bg-gray-50 min-h-[80px]" data-type="primary" data-contact-type="phone">
             <div class="contact-view flex items-start justify-between gap-3">
               <div class="flex items-center gap-3">
                 <div class="text-xl">📱</div>
                 <div>
                   <div class="font-semibold text-gray-900">{{ $u->phone ?: 'Не указан' }}</div>
                   @if($u->phone)
                     <div class="text-sm text-gray-500">Основной телефон</div>
                   @endif
                 </div>
               </div>
               <div class="flex items-center gap-2">
                 <button type="button" class="contact-edit-btn text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
               </div>
             </div>
             
             <div class="contact-edit hidden mt-3">
               <form class="contact-form" action="{{ route('profile.primary.updatePhone') }}" method="post" data-contact-type="phone">@csrf @method('PUT')
                 <div class="space-y-3">
                   <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                     <input type="tel" name="phone" value="{{ $u->phone }}" class="w-full border border-gray-300 rounded-md px-3 py-2 phone-mask" placeholder="+7 (999) 123-45-67">
                   </div>
                 </div>
                 <div class="mt-3 flex justify-end gap-2">
                   <button type="button" class="contact-cancel-btn px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm">Отмена</button>
                   <button type="submit" class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 text-sm">Сохранить</button>
                 </div>
               </form>
             </div>
           </div>
           
           <!-- Основной email -->
                       <div class="contact-item border border-gray-200 rounded-lg p-4 bg-gray-50 min-h-[80px]" data-type="primary" data-contact-type="email">
             <div class="contact-view flex items-start justify-between gap-3">
               <div class="flex items-center gap-3">
                 <div class="text-xl">✉️</div>
                 <div>
                   <div class="font-semibold text-gray-900">{{ $u->email ?: 'Не указан' }}</div>
                   @if($u->email)
                     <div class="text-sm text-gray-500">Основной email</div>
                   @endif
                 </div>
               </div>
               <div class="flex items-center gap-2">
                 <button type="button" class="contact-edit-btn text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
               </div>
             </div>
             
             <div class="contact-edit hidden mt-3">
               <form class="contact-form" action="{{ route('profile.primary.updateEmail') }}" method="post" data-contact-type="email">@csrf @method('PUT')
                 <div class="space-y-3">
                   <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                     <input type="email" name="email" value="{{ $u->email }}" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                   </div>
                 </div>
                 <div class="mt-3 flex justify-end gap-2">
                   <button type="button" class="contact-cancel-btn px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm">Отмена</button>
                   <button type="submit" class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 text-sm">Сохранить</button>
                 </div>
               </form>
             </div>
           </div>
         </div>
       </div>

       <!-- Дополнительные контакты -->
       <div>
         <h3 class="text-md font-semibold text-gray-800 mb-3">Дополнительные контакты</h3>
         
         @if(($u->phones->count() + $u->emails->count()) === 0)
           <div class="text-center text-gray-600 py-8">
             <div class="text-4xl mb-2">📭</div>
             <div class="text-sm">Пока ничего нет. Добавьте данные.</div>
           </div>
         @endif

                   <div class="space-y-3" id="additionalContacts">
           @foreach($u->phones as $p)
                           <div class="contact-item border border-gray-200 rounded-lg p-4 bg-gray-50 hover:shadow-sm transition min-h-[80px]" data-type="additional" data-contact-type="phone" data-id="{{ $p->id }}">
               <div class="contact-view flex items-start justify-between gap-3">
                 <div class="flex items-center gap-3">
                   <div class="text-xl">📱</div>
                   <div>
                     <div class="font-semibold text-gray-900">{{ $p->value }}</div>
                     @if($p->comment)
                       <div class="text-sm text-gray-500">{{ $p->comment }}</div>
                     @endif
                   </div>
                 </div>
                 <div class="flex items-center gap-2">
                   <button type="button" class="contact-edit-btn text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
                   <button type="button" class="contact-delete-btn text-red-600 hover:text-red-700" title="Удалить">🗑</button>
                 </div>
               </div>
               
               <div class="contact-edit hidden mt-3">
                 <form class="contact-form" action="{{ route('profile.phones.update', $p) }}" method="post" data-contact-type="phone">@csrf @method('PUT')
                   <div class="space-y-3">
                     <div>
                       <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                       <input type="tel" name="value" value="{{ $p->value }}" class="w-full border border-gray-300 rounded-md px-3 py-2 phone-mask" required>
                     </div>
                     <div>
                       <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
                       <input type="text" name="comment" value="{{ $p->comment }}" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Комментарий">
                     </div>
                   </div>
                   <div class="mt-3 flex justify-end gap-2">
                     <button type="button" class="contact-cancel-btn px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm">Отмена</button>
                     <button type="submit" class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 text-sm">Сохранить</button>
                   </div>
                 </form>
               </div>
             </div>
           @endforeach

           @foreach($u->emails as $e)
                           <div class="contact-item border border-gray-200 rounded-lg p-4 bg-gray-50 hover:shadow-sm transition min-h-[80px]" data-type="additional" data-contact-type="email" data-id="{{ $e->id }}">
               <div class="contact-view flex items-start justify-between gap-3">
                 <div class="flex items-center gap-3">
                   <div class="text-xl">✉️</div>
                   <div>
                     <div class="font-semibold text-gray-900">{{ $e->value }}</div>
                     @if($e->comment)
                       <div class="text-sm text-gray-500">{{ $e->comment }}</div>
                     @endif
                   </div>
                 </div>
                 <div class="flex items-center gap-2">
                   <button type="button" class="contact-edit-btn text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
                   <button type="button" class="contact-delete-btn text-red-600 hover:text-red-700" title="Удалить">🗑</button>
                 </div>
               </div>
               
               <div class="contact-edit hidden mt-3">
                 <form class="contact-form" action="{{ route('profile.emails.update', $e) }}" method="post" data-contact-type="email">@csrf @method('PUT')
                   <div class="space-y-3">
                     <div>
                       <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                       <input type="email" name="value" value="{{ $e->value }}" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                     </div>
                     <div>
                       <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
                       <input type="text" name="comment" value="{{ $e->comment }}" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Комментарий">
                     </div>
                     <div>
                       <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                         <input type="checkbox" name="is_primary" value="1" {{ $e->is_primary ? 'checked' : '' }}> Для уведомлений
                       </label>
                     </div>
                   </div>
                   <div class="mt-3 flex justify-end gap-2">
                     <button type="button" class="contact-cancel-btn px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm">Отмена</button>
                     <button type="submit" class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 text-sm">Сохранить</button>
                   </div>
                 </form>
               </div>
             </div>
           @endforeach
         </div>
       </div>
     </div>
   </section>

     <!-- Блок 3 — Учётная запись -->
   <section class="bg-white rounded-xl shadow p-6 mb-6">
     <header class="flex items-center gap-2 mb-4 text-lg font-semibold text-gray-900">
       <span>🔐</span>
       <span>Учётная запись</span>
     </header>
     
     <!-- Логин -->
     <div class="mb-6">
       <label class="block text-sm text-gray-700 mb-2">Логин (email):</label>
       <div id="loginDisplay" class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-md">
         <span class="text-gray-700">{{ $u->login ?? $u->email }}</span>
         <button type="button" id="editLoginBtn" class="text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
       </div>
       
       <div id="loginEdit" class="hidden">
         <form id="loginForm" action="{{ route('profile.about.updateLogin') }}" method="post">@csrf @method('PUT')
           <div class="flex items-center gap-3">
             <input type="email" name="email" value="{{ $u->email }}" class="flex-1 border border-gray-300 rounded-md px-3 py-2" required>
             <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Сохранить</button>
             <button type="button" id="cancelLoginBtn" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Отменить</button>
           </div>
         </form>
       </div>
     </div>
     
     <!-- Пароль -->
     <div>
       <label class="block text-sm text-gray-700 mb-2">Пароль:</label>
       <div id="passwordDisplay" class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-md">
         <span class="text-gray-700" id="passwordText">••••••••</span>
         <div class="flex items-center gap-2">
           <button type="button" id="togglePasswordBtn" class="text-gray-600 hover:text-gray-700" title="Показать/скрыть">👁️</button>
           <button type="button" id="editPasswordBtn" class="text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
         </div>
       </div>
       
       <div id="passwordEdit" class="hidden">
         <form id="passwordForm" action="{{ route('profile.about.updatePassword') }}" method="post">@csrf @method('PUT')
           <div class="space-y-3">
             <div>
               <label class="block text-sm text-gray-700 mb-1">Текущий пароль</label>
               <input type="password" name="current_password" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
             </div>
             <div>
               <label class="block text-sm text-gray-700 mb-1">Новый пароль</label>
               <input type="password" name="password" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
             </div>
             <div>
               <label class="block text-sm text-gray-700 mb-1">Подтверждение</label>
               <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
             </div>
             <div class="flex justify-end gap-2">
               <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Сохранить</button>
               <button type="button" id="cancelPasswordBtn" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Отменить</button>
             </div>
           </div>
         </form>
       </div>
     </div>
   </section>

     <!-- Блок 4 — Документы -->
   <section class="bg-white rounded-xl shadow p-6 mb-6">
     <header class="flex items-center justify-between mb-4">
       <div class="flex items-center gap-2 text-lg font-semibold text-gray-900">
         <span>📄</span>
         <span>Документы</span>
       </div>
       <button type="button" id="addDocumentBtn" class="px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700">+ Добавить документ</button>
     </header>

     @if($u->documents->count() === 0)
       <div class="text-center text-gray-600 py-10">
         <div class="text-5xl mb-3">🗂️</div>
         <div>Пока ничего нет. Добавьте данные.</div>
       </div>
     @endif

     <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
       @foreach($u->documents as $d)
         <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
           <div class="flex items-start justify-between mb-3">
             <div class="flex items-center gap-2">
               <div class="text-2xl">
                 @if($d->type === 'passport') 📄
                 @elseif($d->type === 'foreign_passport') 🛂
                 @elseif($d->type === 'driver_license') 🚗
                 @else 📋
                 @endif
               </div>
               <div>
                 <div class="font-semibold text-gray-900">{{ __('types.'.$d->type) }}</div>
                 <div class="text-sm text-gray-600">
                   @if($d->series && $d->number)
                     Серия {{ $d->series }} №{{ $d->number }}
                   @elseif($d->number)
                     №{{ $d->number }}
                   @else
                     —
                   @endif
                 </div>
               </div>
             </div>
             <div class="text-green-600 text-lg">✔</div>
           </div>
           
           @if($d->issued_at)
             <div class="text-sm text-gray-700 mb-1">
               <span class="font-medium">Выдан:</span> {{ $d->issued_at->format('d.m.Y') }}
               @if($d->issued_by) • {{ $d->issued_by }}@endif
             </div>
           @endif
           
                       @if($d->expires_at)
              <div class="text-sm text-gray-700 mb-1">
                <span class="font-medium">Действителен до:</span> {{ $d->expires_at->format('d.m.Y') }}
              </div>
            @endif
            
            @if($d->files && count($d->files) > 0)
              <div class="text-sm text-gray-600 mb-3">
                📎 {{ count($d->files) }} файл(ов)
              </div>
            @endif
           
           <div class="flex items-center gap-2">
             <button class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50 text-sm" data-view-doc="{{ $d->id }}" title="Просмотр">
               👁️ Просмотр
             </button>
             <button class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50 text-sm" data-edit-doc="{{ $d->id }}" title="Редактировать">
               ✏️ Изменить
             </button>
             <button class="px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700 text-sm" data-delete-doc="{{ $d->id }}" title="Удалить">
               🗑️ Удалить
             </button>
           </div>
           
           <template class="payload">
             {!! json_encode([
               'id'=>$d->id,'type'=>$d->type,'series'=>$d->series,'number'=>$d->number,
               'issued_at'=>optional($d->issued_at)->format('Y-m-d'),
               'issued_by'=>$d->issued_by,'expires_at'=>optional($d->expires_at)->format('Y-m-d'),
               'comment'=>$d->comment,'categories'=>$d->categories,'files'=>$d->files,
             ]) !!}
           </template>
         </div>
       @endforeach
     </div>
   </section>



     <!-- Модальное окно подтверждения удаления контакта -->
   <div id="contactDeleteModal" class="fixed inset-0 z-50 hidden">
     <div class="absolute inset-0 bg-black/40" data-close></div>
     <div class="relative z-10 max-w-md mx-auto mt-24 bg-white rounded-xl shadow-lg p-6">
       <div class="text-center">
         <div class="text-4xl mb-4">⚠️</div>
         <h3 class="text-lg font-semibold mb-2">Удалить контакт?</h3>
         <p class="text-gray-600 mb-6">Действие необратимо</p>
         <div class="flex justify-center gap-3">
           <button type="button" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50" data-close>Отмена</button>
           <button type="button" id="confirmContactDeleteBtn" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Удалить</button>
         </div>
       </div>
     </div>
   </div>

     <!-- Модальное окно выбора типа документа -->
   <div id="docTypeModal" class="fixed inset-0 z-50 hidden">
     <div class="absolute inset-0 bg-black/40" data-close></div>
     <div class="relative z-10 max-w-md mx-auto mt-24 bg-white rounded-xl shadow-lg p-6">
       <div class="flex items-start justify-between mb-4">
         <div class="text-lg font-semibold">Выберите тип документа</div>
         <button class="text-gray-500" data-close>✕</button>
       </div>
       <div class="space-y-3">
         <button type="button" class="w-full p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-left" data-doc-type="passport">
           <div class="flex items-center gap-3">
             <div class="text-2xl">📄</div>
             <div>
               <div class="font-semibold">Паспорт РФ</div>
               <div class="text-sm text-gray-600">Внутренний паспорт гражданина РФ</div>
             </div>
           </div>
         </button>
         <button type="button" class="w-full p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-left" data-doc-type="foreign_passport">
           <div class="flex items-center gap-3">
             <div class="text-2xl">🛂</div>
             <div>
               <div class="font-semibold">Загранпаспорт</div>
               <div class="text-sm text-gray-600">Заграничный паспорт</div>
             </div>
           </div>
         </button>
         <button type="button" class="w-full p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-left" data-doc-type="driver_license">
           <div class="flex items-center gap-3">
             <div class="text-2xl">🚗</div>
             <div>
               <div class="font-semibold">Водительские права</div>
               <div class="text-sm text-gray-600">Водительское удостоверение</div>
             </div>
           </div>
         </button>
       </div>
       <div class="mt-6 flex justify-end">
         <button type="button" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50" data-close>Отмена</button>
       </div>
     </div>
   </div>

   <!-- Модальное окно добавления/редактирования документа -->
   <div id="docFormModal" class="fixed inset-0 z-50 hidden">
     <div class="absolute inset-0 bg-black/40" data-close></div>
     <div class="relative z-10 max-w-2xl mx-auto mt-12 bg-white rounded-xl shadow-lg p-6 max-h-[90vh] overflow-y-auto">
       <div class="flex items-start justify-between mb-4">
         <div class="text-lg font-semibold">
           <span id="docFormTitle">Добавить документ</span>
           <div class="text-sm font-normal text-gray-600" id="docFormSubtitle"></div>
         </div>
         <button class="text-gray-500" data-close>✕</button>
       </div>
       
       <form id="docForm" method="post" enctype="multipart/form-data" action="{{ route('profile.documents.store') }}">
         @csrf
         <input type="hidden" name="_method" value="POST">
         <input type="hidden" name="type" id="docType">
         
         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
           <!-- Серия -->
           <div id="seriesField" class="hidden">
             <label class="block text-sm font-medium text-gray-700 mb-1">Серия</label>
             <input type="text" name="series" id="docSeries" class="w-full border border-gray-300 rounded-md px-3 py-2" maxlength="4" placeholder="0000">
             <div class="text-xs text-gray-500 mt-1">4 цифры</div>
           </div>
           
           <!-- Номер -->
           <div>
             <label class="block text-sm font-medium text-gray-700 mb-1">Номер <span class="text-red-500">*</span></label>
             <input type="text" name="number" id="docNumber" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
             <div class="text-xs text-gray-500 mt-1" id="numberHint"></div>
           </div>
           
           <!-- Дата выдачи -->
           <div>
             <label class="block text-sm font-medium text-gray-700 mb-1">Дата выдачи <span class="text-red-500">*</span></label>
             <input type="date" name="issued_at" id="docIssuedAt" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
           </div>
           
           <!-- Кем выдан -->
           <div id="issuedByField" class="hidden">
             <label class="block text-sm font-medium text-gray-700 mb-1">Кем выдан</label>
             <input type="text" name="issued_by" id="docIssuedBy" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="УФМС России">
           </div>
           
           <!-- Дата окончания -->
           <div id="expiresField" class="hidden">
             <label class="block text-sm font-medium text-gray-700 mb-1">Дата окончания <span class="text-red-500">*</span></label>
             <input type="date" name="expires_at" id="docExpiresAt" class="w-full border border-gray-300 rounded-md px-3 py-2">
           </div>
           
           <!-- Категории (для водительских прав) -->
           <div id="categoriesField" class="hidden md:col-span-2">
             <label class="block text-sm font-medium text-gray-700 mb-1">Категории <span class="text-red-500">*</span></label>
             <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
               @foreach(['A', 'B', 'C', 'D', 'E', 'M'] as $category)
                 <label class="flex items-center gap-2 p-2 border border-gray-200 rounded hover:bg-gray-50">
                   <input type="checkbox" name="categories[]" value="{{ $category }}" class="rounded">
                   <span class="text-sm">{{ $category }}</span>
                 </label>
               @endforeach
             </div>
           </div>
         </div>
         
         <!-- Комментарий -->
         <div class="mt-4">
           <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
           <textarea name="comment" id="docComment" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Дополнительная информация"></textarea>
         </div>
         
         <!-- Загрузка файлов -->
         <div class="mt-4">
           <label class="block text-sm font-medium text-gray-700 mb-1">Приложить фото</label>
           <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
             <input type="file" name="files[]" id="docFiles" multiple accept=".jpg,.jpeg,.png,.pdf" class="hidden">
             <label for="docFiles" class="cursor-pointer">
               <div class="text-4xl mb-2">📎</div>
               <div class="text-sm text-gray-600">Нажмите для выбора файлов</div>
               <div class="text-xs text-gray-500 mt-1">JPG, PNG, PDF до 10 МБ каждый</div>
             </label>
           </div>
           <div id="filePreview" class="mt-3 space-y-2"></div>
         </div>
         
         <div class="mt-6 flex justify-end gap-2">
           <button type="button" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50" data-close>Отмена</button>
           <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Сохранить</button>
         </div>
       </form>
     </div>
   </div>

   <!-- Модальное окно просмотра документа -->
   <div id="docViewModal" class="fixed inset-0 z-50 hidden">
     <div class="absolute inset-0 bg-black/40" data-close></div>
     <div class="relative z-10 max-w-4xl mx-auto mt-12 bg-white rounded-xl shadow-lg p-6 max-h-[90vh] overflow-y-auto">
       <div class="flex items-start justify-between mb-4">
         <div class="text-lg font-semibold">
           <span id="docViewTitle">Просмотр документа</span>
         </div>
         <button class="text-gray-500" data-close>✕</button>
       </div>
       
       <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
         <!-- Информация о документе -->
         <div>
           <div id="docViewInfo" class="space-y-3">
             <!-- Заполняется JavaScript -->
           </div>
         </div>
         
         <!-- Галерея файлов -->
         <div>
           <h3 class="font-medium text-gray-900 mb-3">Прикрепленные файлы</h3>
           <div id="docViewGallery" class="space-y-2">
             <!-- Заполняется JavaScript -->
           </div>
         </div>
       </div>
       
       <div class="mt-6 flex justify-end">
         <button type="button" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50" data-close>Закрыть</button>
       </div>
     </div>
   </div>

   <!-- Модальное окно подтверждения удаления -->
   <div id="docDeleteModal" class="fixed inset-0 z-50 hidden">
     <div class="absolute inset-0 bg-black/40" data-close></div>
     <div class="relative z-10 max-w-md mx-auto mt-24 bg-white rounded-xl shadow-lg p-6">
       <div class="text-center">
         <div class="text-4xl mb-4">⚠️</div>
         <h3 class="text-lg font-semibold mb-2">Удалить документ?</h3>
         <p class="text-gray-600 mb-6">Действие необратимо</p>
         <div class="flex justify-center gap-3">
           <button type="button" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50" data-close>Отмена</button>
           <button type="button" id="confirmDeleteBtn" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Удалить</button>
         </div>
       </div>
     </div>
   </div>



  <!-- mini-scripts: модалки и doc-редактор -->
     <script>
     document.addEventListener('DOMContentLoaded', () => {
       // Функция для показа уведомлений
       function showNotification(message, type = 'info') {
         // Создаем элемент уведомления
         const notification = document.createElement('div');
         notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;
         
         // Настройка стилей в зависимости от типа
         if (type === 'info') {
           notification.className += ' bg-blue-500 text-white';
         } else if (type === 'success') {
           notification.className += ' bg-green-500 text-white';
         } else if (type === 'error') {
           notification.className += ' bg-red-500 text-white';
         }
         
         notification.innerHTML = `
           <div class="flex items-center justify-between">
             <div class="flex items-center">
               <span class="mr-2">${type === 'info' ? 'ℹ️' : type === 'success' ? '✅' : '❌'}</span>
               <span class="text-sm">${message}</span>
             </div>
             <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">✕</button>
           </div>
         `;
         
         // Добавляем в DOM
         document.body.appendChild(notification);
         
         // Показываем уведомление
         setTimeout(() => {
           notification.classList.remove('translate-x-full');
         }, 100);
         
         // Автоматически скрываем через 5 секунд
         setTimeout(() => {
           notification.classList.add('translate-x-full');
           setTimeout(() => {
             if (notification.parentElement) {
               notification.remove();
             }
           }, 300);
         }, 5000);
       }
      const bindModal = (openBtn, modalId) => {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        if (openBtn) openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
        modal.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', () => modal.classList.add('hidden')));
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.add('hidden'); });
      };
      bindModal(document.getElementById('addPhoneBtn'), 'modalAddPhone');
      bindModal(document.getElementById('addEmailBtn'), 'modalAddEmail');

             // Документы: двухэтапное добавление
       const addDocumentBtn = document.getElementById('addDocumentBtn');
       const docTypeModal = document.getElementById('docTypeModal');
       const docFormModal = document.getElementById('docFormModal');
       const docViewModal = document.getElementById('docViewModal');
       const docDeleteModal = document.getElementById('docDeleteModal');
       const docForm = document.getElementById('docForm');
       const docFiles = document.getElementById('docFiles');
       const filePreview = document.getElementById('filePreview');

       // Открытие модального окна выбора типа
       if (addDocumentBtn) {
         addDocumentBtn.addEventListener('click', () => {
           docTypeModal.classList.remove('hidden');
         });
       }

       // Выбор типа документа
       document.querySelectorAll('[data-doc-type]').forEach(btn => {
         btn.addEventListener('click', () => {
           const type = btn.getAttribute('data-doc-type');
           const typeNames = {
             'passport': 'Паспорт РФ',
             'foreign_passport': 'Загранпаспорт',
             'driver_license': 'Водительские права'
           };
           
           // Настройка полей в зависимости от типа
           setupDocumentFields(type);
           
           // Заполнение формы
           document.getElementById('docType').value = type;
           document.getElementById('docFormTitle').textContent = 'Добавить документ';
           document.getElementById('docFormSubtitle').textContent = typeNames[type];
           
           // Переключение модальных окон
           docTypeModal.classList.add('hidden');
           docFormModal.classList.remove('hidden');
         });
       });

       // Настройка полей в зависимости от типа документа
       function setupDocumentFields(type) {
         const seriesField = document.getElementById('seriesField');
         const issuedByField = document.getElementById('issuedByField');
         const expiresField = document.getElementById('expiresField');
         const categoriesField = document.getElementById('categoriesField');
         const numberHint = document.getElementById('numberHint');
         const docNumber = document.getElementById('docNumber');
         const docExpiresAt = document.getElementById('docExpiresAt');

         // Скрываем все поля
         seriesField.classList.add('hidden');
         issuedByField.classList.add('hidden');
         expiresField.classList.add('hidden');
         categoriesField.classList.add('hidden');

         // Настройка по типу
         switch(type) {
           case 'passport':
             seriesField.classList.remove('hidden');
             issuedByField.classList.remove('hidden');
             expiresField.classList.remove('hidden');
             numberHint.textContent = '6 цифр';
             docNumber.maxLength = 6;
             docNumber.placeholder = '000000';
             docExpiresAt.required = false;
             break;
           case 'foreign_passport':
             expiresField.classList.remove('hidden');
             numberHint.textContent = 'Любое количество символов';
             docNumber.maxLength = '';
             docNumber.placeholder = 'Номер загранпаспорта';
             docExpiresAt.required = true;
             break;
           case 'driver_license':
             expiresField.classList.remove('hidden');
             categoriesField.classList.remove('hidden');
             numberHint.textContent = '10 цифр';
             docNumber.maxLength = 10;
             docNumber.placeholder = '0000000000';
             docExpiresAt.required = true;
             break;
         }
       }

       // Маски для полей
       document.getElementById('docSeries').addEventListener('input', function(e) {
         this.value = this.value.replace(/\D/g, '').slice(0, 4);
       });

       document.getElementById('docNumber').addEventListener('input', function(e) {
         const type = document.getElementById('docType').value;
         if (type === 'passport') {
           this.value = this.value.replace(/\D/g, '').slice(0, 6);
         } else if (type === 'driver_license') {
           this.value = this.value.replace(/\D/g, '').slice(0, 10);
         }
       });

       // Обработка загрузки файлов
       if (docFiles) {
         docFiles.addEventListener('change', function(e) {
           filePreview.innerHTML = '';
           const files = Array.from(this.files);
           
           files.forEach((file, index) => {
             const fileDiv = document.createElement('div');
             fileDiv.className = 'flex items-center justify-between p-2 bg-gray-50 rounded border';
             
             const fileInfo = document.createElement('div');
             fileInfo.className = 'flex items-center gap-2';
             
             const icon = document.createElement('span');
             icon.textContent = file.type.startsWith('image/') ? '🖼️' : '📄';
             
             const name = document.createElement('span');
             name.className = 'text-sm';
             name.textContent = file.name;
             
             const size = document.createElement('span');
             size.className = 'text-xs text-gray-500';
             size.textContent = `(${(file.size / 1024 / 1024).toFixed(1)} МБ)`;
             
             fileInfo.appendChild(icon);
             fileInfo.appendChild(name);
             fileInfo.appendChild(size);
             
             const removeBtn = document.createElement('button');
             removeBtn.type = 'button';
             removeBtn.className = 'text-red-600 hover:text-red-700 text-sm';
             removeBtn.textContent = '🗑️';
             removeBtn.onclick = () => {
               fileDiv.remove();
               // Создаем новый FileList без удаленного файла
               const dt = new DataTransfer();
               Array.from(this.files).forEach((f, i) => {
                 if (i !== index) dt.items.add(f);
               });
               this.files = dt.files;
             };
             
             fileDiv.appendChild(fileInfo);
             fileDiv.appendChild(removeBtn);
             filePreview.appendChild(fileDiv);
           });
         });
       }

       // Обработка формы документа
       if (docForm) {
         docForm.addEventListener('submit', function(e) {
           e.preventDefault();
           
           const formData = new FormData(this);
           const submitBtn = this.querySelector('button[type="submit"]');
           const originalText = submitBtn.textContent;
           
           submitBtn.textContent = 'Сохранение...';
           submitBtn.disabled = true;
           
           fetch(this.action, {
             method: 'POST',
             body: formData
           })
           .then(response => response.json())
           .then(data => {
             if (data.success) {
               // Закрываем модальное окно
               docFormModal.classList.add('hidden');
               
               // Перезагружаем страницу для отображения нового документа
               location.reload();
             } else {
               throw new Error(data.message || 'Ошибка сохранения');
             }
           })
           .catch(error => {
             console.error('Ошибка:', error);
             submitBtn.textContent = 'Ошибка!';
             setTimeout(() => {
               submitBtn.textContent = originalText;
               submitBtn.disabled = false;
             }, 2000);
           });
         });
       }

       // Просмотр документа
       document.querySelectorAll('[data-view-doc]').forEach(btn => {
         btn.addEventListener('click', () => {
           const id = btn.getAttribute('data-view-doc');
           const host = btn.closest('.border');
           const payload = host && host.querySelector('template.payload');
           if (!payload) return;
           
           const data = JSON.parse(payload.innerHTML.trim());
           const typeNames = {
             'passport': 'Паспорт РФ',
             'foreign_passport': 'Загранпаспорт',
             'driver_license': 'Водительские права'
           };
           
           // Заполняем информацию о документе
           const docViewInfo = document.getElementById('docViewInfo');
           docViewInfo.innerHTML = `
             <div class="flex items-center gap-3 mb-4">
               <div class="text-3xl">
                 ${data.type === 'passport' ? '📄' : data.type === 'foreign_passport' ? '🛂' : '🚗'}
               </div>
               <div>
                 <h3 class="text-lg font-semibold">${typeNames[data.type]}</h3>
                 <p class="text-gray-600">
                   ${data.series && data.number ? `Серия ${data.series} №${data.number}` : 
                     data.number ? `№${data.number}` : 'Номер не указан'}
                 </p>
               </div>
             </div>
             ${data.issued_at ? `<div><strong>Дата выдачи:</strong> ${new Date(data.issued_at).toLocaleDateString('ru-RU')}</div>` : ''}
             ${data.issued_by ? `<div><strong>Кем выдан:</strong> ${data.issued_by}</div>` : ''}
             ${data.expires_at ? `<div><strong>Действителен до:</strong> ${new Date(data.expires_at).toLocaleDateString('ru-RU')}</div>` : ''}
             ${data.categories && data.categories.length > 0 ? `<div><strong>Категории:</strong> ${data.categories.join(', ')}</div>` : ''}
             ${data.comment ? `<div><strong>Комментарий:</strong> ${data.comment}</div>` : ''}
           `;
           
                       // Заполняем галерею файлов
            const docViewGallery = document.getElementById('docViewGallery');
            if (data.files && data.files.length > 0) {
              docViewGallery.innerHTML = data.files.map(file => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded border">
                  <div class="flex items-center gap-3">
                    <div class="text-2xl">${file.type.startsWith('image/') ? '🖼️' : '📄'}</div>
                    <div>
                      <div class="font-medium">${file.name}</div>
                      <div class="text-sm text-gray-500">${(file.size / 1024 / 1024).toFixed(1)} МБ</div>
                    </div>
                  </div>
                  <a href="/storage/${file.path}" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm">
                    👁️ Просмотр
                  </a>
                </div>
              `).join('');
            } else {
              docViewGallery.innerHTML = '<p class="text-gray-500">Файлы не загружены</p>';
            }
           
           docViewModal.classList.remove('hidden');
         });
       });

       // Редактирование документа
       document.querySelectorAll('[data-edit-doc]').forEach(btn => {
         btn.addEventListener('click', () => {
           const id = btn.getAttribute('data-edit-doc');
           const host = btn.closest('.border');
           const payload = host && host.querySelector('template.payload');
           if (!payload) return;
           
           const data = JSON.parse(payload.innerHTML.trim());
           const typeNames = {
             'passport': 'Паспорт РФ',
             'foreign_passport': 'Загранпаспорт',
             'driver_license': 'Водительские права'
           };
           
           // Настройка полей
           setupDocumentFields(data.type);
           
           // Заполнение формы
           document.getElementById('docType').value = data.type;
           document.getElementById('docFormTitle').textContent = 'Редактировать документ';
           document.getElementById('docFormSubtitle').textContent = typeNames[data.type];
           document.querySelector('#docForm input[name=_method]').value = 'PUT';
           document.getElementById('docForm').setAttribute('action', '{{ route("profile.documents.update", "__ID__") }}'.replace('__ID__', id));
           
                       document.getElementById('docSeries').value = data.series || '';
            document.getElementById('docNumber').value = data.number || '';
            document.getElementById('docIssuedAt').value = data.issued_at || '';
            document.getElementById('docIssuedBy').value = data.issued_by || '';
            document.getElementById('docExpiresAt').value = data.expires_at || '';
            document.getElementById('docComment').value = data.comment || '';
            
            // Обработка категорий
            if (data.categories && Array.isArray(data.categories)) {
              document.querySelectorAll('input[name="categories[]"]').forEach(checkbox => {
                checkbox.checked = data.categories.includes(checkbox.value);
              });
            }
           
           // Очищаем файлы
           docFiles.value = '';
           filePreview.innerHTML = '';
           
           docFormModal.classList.remove('hidden');
         });
       });

       // Удаление документа
       document.querySelectorAll('[data-delete-doc]').forEach(btn => {
         btn.addEventListener('click', () => {
           const id = btn.getAttribute('data-delete-doc');
           
           // Показываем модальное окно подтверждения
           docDeleteModal.classList.remove('hidden');
           
           // Обработка подтверждения удаления
           document.getElementById('confirmDeleteBtn').onclick = () => {
             fetch(`{{ route('profile.documents.destroy', '__ID__') }}`.replace('__ID__', id), {
               method: 'DELETE',
               headers: {
                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
               }
             })
             .then(response => response.json())
             .then(data => {
               if (data.success) {
                 // Закрываем модальное окно
                 docDeleteModal.classList.add('hidden');
                 // Перезагружаем страницу
                 location.reload();
               } else {
                 throw new Error(data.message || 'Ошибка удаления');
               }
             })
             .catch(error => {
               console.error('Ошибка:', error);
               alert('Ошибка при удалении документа');
             });
           };
         });
       });

                // Закрытие модальных окон
         [docTypeModal, docFormModal, docViewModal, docDeleteModal, contactDeleteModal].forEach(modal => {
           if (modal) {
             modal.querySelectorAll('[data-close]').forEach(el => {
               el.addEventListener('click', () => modal.classList.add('hidden'));
             });
             modal.addEventListener('click', (e) => {
               if (e.target === modal) modal.classList.add('hidden');
             });
           }
         });

         // ===== СИСТЕМА КОНТАКТОВ =====
         
         // Маска для телефонов
         function applyPhoneMask(input) {
           let value = input.value.replace(/\D/g, '');
           if (value.startsWith('8')) {
             value = '7' + value.substring(1);
           }
           if (value.startsWith('7')) {
             value = value.substring(1);
           }
           if (value.length > 0) {
             value = '+7 ' + value;
           }
           if (value.length > 4) {
             value = value.substring(0, 4) + '(' + value.substring(4);
           }
           if (value.length > 8) {
             value = value.substring(0, 8) + ') ' + value.substring(8);
           }
           if (value.length > 13) {
             value = value.substring(0, 13) + '-' + value.substring(13);
           }
           if (value.length > 16) {
             value = value.substring(0, 16) + '-' + value.substring(16);
           }
           input.value = value;
         }

         // Применяем маску ко всем полям телефонов
         document.querySelectorAll('.phone-mask').forEach(input => {
           input.addEventListener('input', () => applyPhoneMask(input));
           input.addEventListener('focus', () => applyPhoneMask(input));
         });

         // Функция форматирования телефона для отображения
         function formatPhoneForDisplay(phone) {
           if (!phone) return 'Не указан';
           let value = phone.replace(/\D/g, '');
           if (value.startsWith('8')) {
             value = '7' + value.substring(1);
           }
           if (value.startsWith('7')) {
             value = value.substring(1);
           }
           if (value.length >= 10) {
             return `+7 ${value.substring(0, 3)} ${value.substring(3, 6)}-${value.substring(6, 8)}-${value.substring(8, 10)}`;
           }
           return phone;
         }

         // Закрытие всех открытых редакторов контактов
         function closeAllContactEditors() {
           document.querySelectorAll('.contact-edit').forEach(editor => {
             editor.classList.add('hidden');
           });
         }

         // Обработка редактирования контактов
         document.querySelectorAll('.contact-edit-btn').forEach(btn => {
           btn.addEventListener('click', (e) => {
             e.preventDefault();
             const contactItem = btn.closest('.contact-item');
             const view = contactItem.querySelector('.contact-view');
             const edit = contactItem.querySelector('.contact-edit');
             
             // Закрываем все другие редакторы
             closeAllContactEditors();
             
             // Открываем текущий редактор
             edit.classList.remove('hidden');
             
             // Фокусируемся на первом поле ввода
             const firstInput = edit.querySelector('input');
             if (firstInput) {
               setTimeout(() => firstInput.focus(), 100);
             }
           });
         });

         // Обработка отмены редактирования
         document.querySelectorAll('.contact-cancel-btn').forEach(btn => {
           btn.addEventListener('click', (e) => {
             e.preventDefault();
             const contactItem = btn.closest('.contact-item');
             const edit = contactItem.querySelector('.contact-edit');
             edit.classList.add('hidden');
           });
         });

         // Обработка форм контактов
         document.querySelectorAll('.contact-form').forEach(form => {
           form.addEventListener('submit', function(e) {
             e.preventDefault();
             
             const formData = new FormData(this);
             const submitBtn = this.querySelector('button[type="submit"]');
             const originalText = submitBtn.textContent;
             const contactItem = this.closest('.contact-item');
             const view = contactItem.querySelector('.contact-view');
             const edit = contactItem.querySelector('.contact-edit');
             const contactType = this.getAttribute('data-contact-type');
             
             submitBtn.textContent = 'Сохранение...';
             submitBtn.disabled = true;
             
             fetch(this.action, {
               method: 'POST',
               body: formData,
               headers: {
                 'X-Requested-With': 'XMLHttpRequest'
               }
             })
             .then(response => response.json())
             .then(data => {
               if (data.success) {
                 // Обновляем отображаемые данные
                 const valueDiv = view.querySelector('.font-semibold');
                 const commentDiv = view.querySelector('.text-sm.text-gray-500');
                 
                 if (contactType === 'phone') {
                   valueDiv.textContent = formatPhoneForDisplay(data.value || data.phone);
                 } else {
                   valueDiv.textContent = data.value || data.email;
                 }
                 
                 if (data.comment) {
                   if (commentDiv) {
                     commentDiv.textContent = data.comment;
                   } else {
                     const newCommentDiv = document.createElement('div');
                     newCommentDiv.className = 'text-sm text-gray-500';
                     newCommentDiv.textContent = data.comment;
                     valueDiv.parentNode.appendChild(newCommentDiv);
                   }
                 } else if (commentDiv) {
                   commentDiv.remove();
                 }
                 
                                   // Скрываем редактор
                  edit.classList.add('hidden');
                  
                                     // Показываем уведомление об успешном сохранении
                   showNotification('Контакт успешно сохранен', 'success');
                   
                   submitBtn.textContent = 'Сохранено!';
                   setTimeout(() => {
                     submitBtn.textContent = originalText;
                     submitBtn.disabled = false;
                   }, 2000);
                 } else {
                   throw new Error(data.message || 'Ошибка сохранения');
                 }
               })
               .catch(error => {
                 console.error('Ошибка:', error);
                 showNotification('Ошибка при сохранении контакта', 'error');
                 submitBtn.textContent = 'Ошибка!';
                 setTimeout(() => {
                   submitBtn.textContent = originalText;
                   submitBtn.disabled = false;
                 }, 2000);
               });
           });
         });

         // Обработка удаления контактов
         document.querySelectorAll('.contact-delete-btn').forEach(btn => {
           btn.addEventListener('click', (e) => {
             e.preventDefault();
             const contactItem = btn.closest('.contact-item');
             const contactId = contactItem.getAttribute('data-id');
             const contactType = contactItem.getAttribute('data-contact-type');
             
             // Показываем модальное окно подтверждения
             contactDeleteModal.classList.remove('hidden');
             
             // Обработка подтверждения удаления
             document.getElementById('confirmContactDeleteBtn').onclick = () => {
               const deleteUrl = contactType === 'phone' 
                 ? `{{ route('profile.phones.destroy', '__ID__') }}`.replace('__ID__', contactId)
                 : `{{ route('profile.emails.destroy', '__ID__') }}`.replace('__ID__', contactId);
               
               fetch(deleteUrl, {
                 method: 'DELETE',
                 headers: {
                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                   'X-Requested-With': 'XMLHttpRequest'
                 }
               })
               .then(response => response.json())
               .then(data => {
                                     if (data.success) {
                      // Удаляем элемент из DOM
                      contactItem.remove();
                      
                      // Закрываем модальное окно
                      contactDeleteModal.classList.add('hidden');
                      
                      // Показываем уведомление об успешном удалении
                      showNotification('Контакт успешно удален', 'success');
                      
                                            // Проверяем, нужно ли показать пустое состояние
                      const additionalContacts = document.getElementById('additionalContacts');
                      if (additionalContacts && additionalContacts.children.length === 0) {
                        additionalContacts.innerHTML = `
                          <div class="text-center text-gray-600 py-8">
                            <div class="text-4xl mb-2">📭</div>
                            <div class="text-sm">Пока ничего нет. Добавьте данные.</div>
                          </div>
                        `;
                      }
                    } else {
                      throw new Error(data.message || 'Ошибка удаления');
                    }
                  })
                  .catch(error => {
                    console.error('Ошибка:', error);
                    showNotification('Ошибка при удалении контакта', 'error');
                    contactDeleteModal.classList.add('hidden');
                  });
             };
           });
         });

         // Добавление новых контактов
         const addPhoneBtn = document.getElementById('addPhoneBtn');
         const addEmailBtn = document.getElementById('addEmailBtn');
         const additionalContacts = document.getElementById('additionalContacts');

         function createNewContactItem(type) {
           const isPhone = type === 'phone';
           const icon = isPhone ? '📱' : '✉️';
           const placeholder = isPhone ? '+7 (999) 123-45-67' : 'you@example.com';
           const inputType = isPhone ? 'tel' : 'email';
           const inputClass = isPhone ? 'phone-mask' : '';
           const inputName = isPhone ? 'value' : 'value';
           
                       const newContactHtml = `
              <div class="contact-item border border-gray-200 rounded-lg p-4 bg-gray-50 hover:shadow-sm transition min-h-[80px]" data-type="additional" data-contact-type="${type}">
               <div class="contact-edit">
                 <form class="contact-form" action="{{ route('profile.phones.store') }}" method="post" data-contact-type="${type}">@csrf
                   <div class="space-y-3">
                     <div>
                       <label class="block text-sm font-medium text-gray-700 mb-1">${isPhone ? 'Телефон' : 'Email'}</label>
                       <input type="${inputType}" name="${inputName}" class="w-full border border-gray-300 rounded-md px-3 py-2 ${inputClass}" placeholder="${placeholder}" required>
                     </div>
                     <div>
                       <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
                       <input type="text" name="comment" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Комментарий">
                     </div>
                     ${!isPhone ? `
                     <div>
                       <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                         <input type="checkbox" name="is_primary" value="1"> Для уведомлений
                       </label>
                     </div>
                     ` : ''}
                   </div>
                   <div class="mt-3 flex justify-end gap-2">
                     <button type="button" class="contact-cancel-btn px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm">Отмена</button>
                     <button type="submit" class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 text-sm">Сохранить</button>
                   </div>
                 </form>
               </div>
             </div>
           `;
           
           return newContactHtml;
         }

                   if (addPhoneBtn) {
            addPhoneBtn.addEventListener('click', () => {
              // Проверяем, есть ли уже дополнительный телефон
              const existingPhone = additionalContacts.querySelector('[data-contact-type="phone"]');
              if (existingPhone) {
                // Показываем уведомление
                showNotification('У вас уже есть дополнительный телефон. Вы можете удалить или отредактировать его.', 'info');
                return;
              }
              
              // Удаляем пустое состояние если есть
              const emptyState = additionalContacts.querySelector('.text-center');
              if (emptyState) {
                emptyState.remove();
              }
              
              // Закрываем все открытые редакторы
              closeAllContactEditors();
              
              // Добавляем новый элемент
              const newContactHtml = createNewContactItem('phone');
              additionalContacts.insertAdjacentHTML('afterbegin', newContactHtml);
             
             // Настраиваем обработчики для нового элемента
             const newContact = additionalContacts.firstElementChild;
             
             // Маска для телефона
             const phoneInput = newContact.querySelector('.phone-mask');
             if (phoneInput) {
               phoneInput.addEventListener('input', () => applyPhoneMask(phoneInput));
               phoneInput.addEventListener('focus', () => applyPhoneMask(phoneInput));
               setTimeout(() => phoneInput.focus(), 100);
             }
             
             // Обработчик отмены
             const cancelBtn = newContact.querySelector('.contact-cancel-btn');
             cancelBtn.addEventListener('click', () => {
               newContact.remove();
               // Проверяем, нужно ли показать пустое состояние
               if (additionalContacts.children.length === 0) {
                 additionalContacts.innerHTML = `
                   <div class="text-center text-gray-600 py-8">
                     <div class="text-4xl mb-2">📭</div>
                     <div class="text-sm">Пока ничего нет. Добавьте данные.</div>
                   </div>
                 `;
               }
             });
             
             // Обработчик формы
             const form = newContact.querySelector('.contact-form');
             form.addEventListener('submit', function(e) {
               e.preventDefault();
               
               const formData = new FormData(this);
               const submitBtn = this.querySelector('button[type="submit"]');
               const originalText = submitBtn.textContent;
               
               submitBtn.textContent = 'Сохранение...';
               submitBtn.disabled = true;
               
               fetch(this.action, {
                 method: 'POST',
                 body: formData,
                 headers: {
                   'X-Requested-With': 'XMLHttpRequest'
                 }
               })
               .then(response => response.json())
               .then(data => {
                 if (data.success) {
                   // Заменяем форму на отображение
                   const contactHtml = `
                     <div class="contact-view flex items-start justify-between gap-3">
                       <div class="flex items-center gap-3">
                         <div class="text-xl">📱</div>
                         <div>
                           <div class="font-semibold text-gray-900">${formatPhoneForDisplay(data.value)}</div>
                           ${data.comment ? `<div class="text-sm text-gray-500">${data.comment}</div>` : ''}
                         </div>
                       </div>
                       <div class="flex items-center gap-2">
                         <button type="button" class="contact-edit-btn text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
                         <button type="button" class="contact-delete-btn text-red-600 hover:text-red-700" title="Удалить">🗑</button>
                       </div>
                     </div>
                     
                     <div class="contact-edit hidden mt-3">
                       <form class="contact-form" action="{{ route('profile.phones.update', '__ID__') }}" method="post" data-contact-type="phone">@csrf @method('PUT')
                         <div class="space-y-3">
                           <div>
                             <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                             <input type="tel" name="value" value="${data.value}" class="w-full border border-gray-300 rounded-md px-3 py-2 phone-mask" required>
                           </div>
                           <div>
                             <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
                             <input type="text" name="comment" value="${data.comment || ''}" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Комментарий">
                           </div>
                         </div>
                         <div class="mt-3 flex justify-end gap-2">
                           <button type="button" class="contact-cancel-btn px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm">Отмена</button>
                           <button type="submit" class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 text-sm">Сохранить</button>
                         </div>
                       </form>
                     </div>
                   `;
                   
                   newContact.innerHTML = contactHtml;
                   newContact.setAttribute('data-id', data.id);
                   
                   // Настраиваем обработчики для нового элемента
                   setupContactHandlers(newContact);
                   
                   // Показываем уведомление об успешном создании
                   showNotification('Телефон успешно добавлен', 'success');
                   
                   submitBtn.textContent = 'Сохранено!';
                   setTimeout(() => {
                     submitBtn.textContent = originalText;
                     submitBtn.disabled = false;
                   }, 2000);
                 } else {
                   throw new Error(data.message || 'Ошибка сохранения');
                 }
               })
               .catch(error => {
                 console.error('Ошибка:', error);
                 showNotification('Ошибка при сохранении телефона', 'error');
                 submitBtn.textContent = 'Ошибка!';
                 setTimeout(() => {
                   submitBtn.textContent = originalText;
                   submitBtn.disabled = false;
                 }, 2000);
               });
             });
           });
         }

                   if (addEmailBtn) {
            addEmailBtn.addEventListener('click', () => {
              // Проверяем, есть ли уже дополнительный email
              const existingEmail = additionalContacts.querySelector('[data-contact-type="email"]');
              if (existingEmail) {
                // Показываем уведомление
                showNotification('У вас уже есть дополнительный email. Вы можете удалить или отредактировать его.', 'info');
                return;
              }
              
              // Удаляем пустое состояние если есть
              const emptyState = additionalContacts.querySelector('.text-center');
              if (emptyState) {
                emptyState.remove();
              }
              
              // Закрываем все открытые редакторы
              closeAllContactEditors();
              
              // Добавляем новый элемент
              const newContactHtml = createNewContactItem('email');
              additionalContacts.insertAdjacentHTML('afterbegin', newContactHtml);
             
             // Настраиваем обработчики для нового элемента
             const newContact = additionalContacts.firstElementChild;
             
             // Фокус на поле ввода
             const emailInput = newContact.querySelector('input[type="email"]');
             if (emailInput) {
               setTimeout(() => emailInput.focus(), 100);
             }
             
             // Обработчик отмены
             const cancelBtn = newContact.querySelector('.contact-cancel-btn');
             cancelBtn.addEventListener('click', () => {
               newContact.remove();
               // Проверяем, нужно ли показать пустое состояние
               if (additionalContacts.children.length === 0) {
                 additionalContacts.innerHTML = `
                   <div class="text-center text-gray-600 py-8">
                     <div class="text-4xl mb-2">📭</div>
                     <div class="text-sm">Пока ничего нет. Добавьте данные.</div>
                   </div>
                 `;
               }
             });
             
             // Обработчик формы
             const form = newContact.querySelector('.contact-form');
             form.setAttribute('action', '{{ route("profile.emails.store") }}');
             form.addEventListener('submit', function(e) {
               e.preventDefault();
               
               const formData = new FormData(this);
               const submitBtn = this.querySelector('button[type="submit"]');
               const originalText = submitBtn.textContent;
               
               submitBtn.textContent = 'Сохранение...';
               submitBtn.disabled = true;
               
               fetch(this.action, {
                 method: 'POST',
                 body: formData,
                 headers: {
                   'X-Requested-With': 'XMLHttpRequest'
                 }
               })
               .then(response => response.json())
               .then(data => {
                 if (data.success) {
                   // Заменяем форму на отображение
                   const contactHtml = `
                     <div class="contact-view flex items-start justify-between gap-3">
                       <div class="flex items-center gap-3">
                         <div class="text-xl">✉️</div>
                         <div>
                           <div class="font-semibold text-gray-900">${data.value}</div>
                           ${data.comment ? `<div class="text-sm text-gray-500">${data.comment}</div>` : ''}
                         </div>
                       </div>
                       <div class="flex items-center gap-2">
                         <button type="button" class="contact-edit-btn text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
                         <button type="button" class="contact-delete-btn text-red-600 hover:text-red-700" title="Удалить">🗑</button>
                       </div>
                     </div>
                     
                     <div class="contact-edit hidden mt-3">
                       <form class="contact-form" action="{{ route('profile.emails.update', '__ID__') }}" method="post" data-contact-type="email">@csrf @method('PUT')
                         <div class="space-y-3">
                           <div>
                             <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                             <input type="email" name="value" value="${data.value}" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                           </div>
                           <div>
                             <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
                             <input type="text" name="comment" value="${data.comment || ''}" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Комментарий">
                           </div>
                           <div>
                             <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                               <input type="checkbox" name="is_primary" value="1" ${data.is_primary ? 'checked' : ''}> Для уведомлений
                             </label>
                           </div>
                         </div>
                         <div class="mt-3 flex justify-end gap-2">
                           <button type="button" class="contact-cancel-btn px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm">Отмена</button>
                           <button type="submit" class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 text-sm">Сохранить</button>
                         </div>
                       </form>
                     </div>
                   `;
                   
                   newContact.innerHTML = contactHtml;
                   newContact.setAttribute('data-id', data.id);
                   
                   // Настраиваем обработчики для нового элемента
                   setupContactHandlers(newContact);
                   
                   // Показываем уведомление об успешном создании
                   showNotification('Email успешно добавлен', 'success');
                   
                   submitBtn.textContent = 'Сохранено!';
                   setTimeout(() => {
                     submitBtn.textContent = originalText;
                     submitBtn.disabled = false;
                   }, 2000);
                 } else {
                   throw new Error(data.message || 'Ошибка сохранения');
                 }
               })
               .catch(error => {
                 console.error('Ошибка:', error);
                 showNotification('Ошибка при сохранении email', 'error');
                 submitBtn.textContent = 'Ошибка!';
                 setTimeout(() => {
                   submitBtn.textContent = originalText;
                   submitBtn.disabled = false;
                 }, 2000);
               });
             });
           });
         }

         // Функция настройки обработчиков для контакта
         function setupContactHandlers(contactItem) {
           // Редактирование
           const editBtn = contactItem.querySelector('.contact-edit-btn');
           if (editBtn) {
             editBtn.addEventListener('click', (e) => {
               e.preventDefault();
               const view = contactItem.querySelector('.contact-view');
               const edit = contactItem.querySelector('.contact-edit');
               
               closeAllContactEditors();
               edit.classList.remove('hidden');
               
               const firstInput = edit.querySelector('input');
               if (firstInput) {
                 setTimeout(() => firstInput.focus(), 100);
               }
             });
           }
           
           // Отмена
           const cancelBtn = contactItem.querySelector('.contact-cancel-btn');
           if (cancelBtn) {
             cancelBtn.addEventListener('click', (e) => {
               e.preventDefault();
               const edit = contactItem.querySelector('.contact-edit');
               edit.classList.add('hidden');
             });
           }
           
           // Удаление
           const deleteBtn = contactItem.querySelector('.contact-delete-btn');
           if (deleteBtn) {
             deleteBtn.addEventListener('click', (e) => {
               e.preventDefault();
               const contactId = contactItem.getAttribute('data-id');
               const contactType = contactItem.getAttribute('data-contact-type');
               
               contactDeleteModal.classList.remove('hidden');
               
               document.getElementById('confirmContactDeleteBtn').onclick = () => {
                 const deleteUrl = contactType === 'phone' 
                   ? `{{ route('profile.phones.destroy', '__ID__') }}`.replace('__ID__', contactId)
                   : `{{ route('profile.emails.destroy', '__ID__') }}`.replace('__ID__', contactId);
                 
                 fetch(deleteUrl, {
                   method: 'DELETE',
                   headers: {
                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                     'X-Requested-With': 'XMLHttpRequest'
                   }
                 })
                 .then(response => response.json())
                 .then(data => {
                                       if (data.success) {
                      contactItem.remove();
                      contactDeleteModal.classList.add('hidden');
                      
                      // Показываем уведомление об успешном удалении
                      showNotification('Контакт успешно удален', 'success');
                      
                                            if (additionalContacts && additionalContacts.children.length === 0) {
                        additionalContacts.innerHTML = `
                          <div class="text-center text-gray-600 py-8">
                            <div class="text-4xl mb-2">📭</div>
                            <div class="text-sm">Пока ничего нет. Добавьте данные.</div>
                          </div>
                        `;
                      }
                    } else {
                      throw new Error(data.message || 'Ошибка удаления');
                    }
                  })
                  .catch(error => {
                    console.error('Ошибка:', error);
                    showNotification('Ошибка при удалении контакта', 'error');
                    contactDeleteModal.classList.add('hidden');
                  });
               };
             });
           }
           
           // Форма
           const form = contactItem.querySelector('.contact-form');
           if (form) {
             form.addEventListener('submit', function(e) {
               e.preventDefault();
               
               const formData = new FormData(this);
               const submitBtn = this.querySelector('button[type="submit"]');
               const originalText = submitBtn.textContent;
               const view = contactItem.querySelector('.contact-view');
               const edit = contactItem.querySelector('.contact-edit');
               const contactType = this.getAttribute('data-contact-type');
               
               submitBtn.textContent = 'Сохранение...';
               submitBtn.disabled = true;
               
               fetch(this.action, {
                 method: 'POST',
                 body: formData,
                 headers: {
                   'X-Requested-With': 'XMLHttpRequest'
                 }
               })
               .then(response => response.json())
               .then(data => {
                 if (data.success) {
                   const valueDiv = view.querySelector('.font-semibold');
                   const commentDiv = view.querySelector('.text-sm.text-gray-500');
                   
                   if (contactType === 'phone') {
                     valueDiv.textContent = formatPhoneForDisplay(data.value);
                   } else {
                     valueDiv.textContent = data.value;
                   }
                   
                   if (data.comment) {
                     if (commentDiv) {
                       commentDiv.textContent = data.comment;
                     } else {
                       const newCommentDiv = document.createElement('div');
                       newCommentDiv.className = 'text-sm text-gray-500';
                       newCommentDiv.textContent = data.comment;
                       valueDiv.parentNode.appendChild(newCommentDiv);
                     }
                   } else if (commentDiv) {
                     commentDiv.remove();
                   }
                   
                   edit.classList.add('hidden');
                   
                   submitBtn.textContent = 'Сохранено!';
                   setTimeout(() => {
                     submitBtn.textContent = originalText;
                     submitBtn.disabled = false;
                   }, 2000);
                 } else {
                   throw new Error(data.message || 'Ошибка сохранения');
                 }
               })
               .catch(error => {
                 console.error('Ошибка:', error);
                 submitBtn.textContent = 'Ошибка!';
                 setTimeout(() => {
                   submitBtn.textContent = originalText;
                   submitBtn.disabled = false;
                 }, 2000);
               });
             });
           }
           
           // Маска для телефонов
           const phoneInput = contactItem.querySelector('.phone-mask');
           if (phoneInput) {
             phoneInput.addEventListener('input', () => applyPhoneMask(phoneInput));
             phoneInput.addEventListener('focus', () => applyPhoneMask(phoneInput));
           }
         }

         // Горячие клавиши
         document.addEventListener('keydown', (e) => {
           const activeContactEdit = document.querySelector('.contact-edit:not(.hidden)');
           if (activeContactEdit) {
             if (e.key === 'Enter' && e.ctrlKey) {
               e.preventDefault();
               const submitBtn = activeContactEdit.querySelector('button[type="submit"]');
               if (submitBtn) submitBtn.click();
             } else if (e.key === 'Escape') {
               e.preventDefault();
               const cancelBtn = activeContactEdit.querySelector('.contact-cancel-btn');
               if (cancelBtn) cancelBtn.click();
             }
           }
         });

               // AJAX обработка формы профиля
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
          profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            // Показываем индикатор загрузки
            submitBtn.textContent = 'Сохранение...';
            submitBtn.disabled = true;
            
            fetch(this.action, {
              method: 'POST',
              body: formData,
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              }
            })
            .then(response => response.json())
            .then(data => {
              // Обновляем значения полей без перезагрузки
              const inputs = this.querySelectorAll('input[name]');
              inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (data.profile && data.profile[name] !== undefined) {
                  if (name === 'birth_date' && data.profile[name]) {
                    input.value = data.profile[name];
                  } else {
                    input.value = data.profile[name] || '';
                  }
                }
              });
              
              // Показываем уведомление об успехе
              submitBtn.textContent = 'Сохранено!';
              setTimeout(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
              }, 2000);
            })
            .catch(error => {
              console.error('Ошибка:', error);
              submitBtn.textContent = 'Ошибка!';
              setTimeout(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
              }, 2000);
            });
          });
        }

        // Учётная запись - редактирование логина
        const editLoginBtn = document.getElementById('editLoginBtn');
        const loginDisplay = document.getElementById('loginDisplay');
        const loginEdit = document.getElementById('loginEdit');
        const cancelLoginBtn = document.getElementById('cancelLoginBtn');
        const loginForm = document.getElementById('loginForm');

        if (editLoginBtn) {
          editLoginBtn.addEventListener('click', () => {
            loginDisplay.classList.add('hidden');
            loginEdit.classList.remove('hidden');
          });
        }

        if (cancelLoginBtn) {
          cancelLoginBtn.addEventListener('click', () => {
            loginDisplay.classList.remove('hidden');
            loginEdit.classList.add('hidden');
          });
        }

        if (loginForm) {
          loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Сохранение...';
            submitBtn.disabled = true;
            
            fetch(this.action, {
              method: 'POST',
              body: formData,
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Обновляем отображаемый email
                const emailSpan = loginDisplay.querySelector('span');
                emailSpan.textContent = data.email;
                
                // Скрываем форму редактирования
                loginDisplay.classList.remove('hidden');
                loginEdit.classList.add('hidden');
                
                submitBtn.textContent = 'Сохранено!';
                setTimeout(() => {
                  submitBtn.textContent = originalText;
                  submitBtn.disabled = false;
                }, 2000);
              } else {
                throw new Error(data.message || 'Ошибка сохранения');
              }
            })
            .catch(error => {
              console.error('Ошибка:', error);
              submitBtn.textContent = 'Ошибка!';
              setTimeout(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
              }, 2000);
            });
          });
        }

        // Учётная запись - редактирование пароля
        const editPasswordBtn = document.getElementById('editPasswordBtn');
        const passwordDisplay = document.getElementById('passwordDisplay');
        const passwordEdit = document.getElementById('passwordEdit');
        const cancelPasswordBtn = document.getElementById('cancelPasswordBtn');
        const passwordForm = document.getElementById('passwordForm');
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const passwordText = document.getElementById('passwordText');

        if (editPasswordBtn) {
          editPasswordBtn.addEventListener('click', () => {
            passwordDisplay.classList.add('hidden');
            passwordEdit.classList.remove('hidden');
          });
        }

        if (cancelPasswordBtn) {
          cancelPasswordBtn.addEventListener('click', () => {
            passwordDisplay.classList.remove('hidden');
            passwordEdit.classList.add('hidden');
            // Очищаем поля формы
            passwordForm.reset();
          });
        }

        if (togglePasswordBtn) {
          let passwordVisible = false;
          togglePasswordBtn.addEventListener('click', () => {
            passwordVisible = !passwordVisible;
            if (passwordVisible) {
              passwordText.textContent = 'Пароль установлен';
              togglePasswordBtn.textContent = '🙈';
            } else {
              passwordText.textContent = '••••••••';
              togglePasswordBtn.textContent = '👁️';
            }
          });
        }

        if (passwordForm) {
          passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Сохранение...';
            submitBtn.disabled = true;
            
            fetch(this.action, {
              method: 'POST',
              body: formData,
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Скрываем форму редактирования
                passwordDisplay.classList.remove('hidden');
                passwordEdit.classList.add('hidden');
                
                // Очищаем поля формы
                passwordForm.reset();
                
                submitBtn.textContent = 'Сохранено!';
                setTimeout(() => {
                  submitBtn.textContent = originalText;
                  submitBtn.disabled = false;
                }, 2000);
              } else {
                throw new Error(data.message || 'Ошибка сохранения');
              }
            })
            .catch(error => {
              console.error('Ошибка:', error);
              submitBtn.textContent = 'Ошибка!';
              setTimeout(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
              }, 2000);
            });
          });
        }
     });
   </script>
 </div>
 @endsection


