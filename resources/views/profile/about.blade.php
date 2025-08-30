@include('layouts.navigation')
@vite(['resources/css/app.css', 'resources/css/lk-about.css', 'resources/js/app.js', 'resources/js/lk-about.js'])

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
            <div class="w-24 h-24 rounded-full bg-gray-100 grid place-items-center text-3xl text-gray-400 overflow-hidden" id="profilePhotoContainer">
              @if($u->profile && $u->profile->photo_path)
                <img src="{{ asset('storage/' . $u->profile->photo_path) }}" alt="Фото профиля" class="w-full h-full object-cover">
              @else
                <span>👤</span>
              @endif
            </div>
            <input type="file" id="profilePhotoInput" accept="image/*" class="hidden">
            <button type="button" id="uploadPhotoBtn" class="px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700">Загрузить фото</button>
          </div>
          <div class="flex-1">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
               <div>
                 <label class="block text-sm text-gray-700 mb-1">Фамилия</label>
                 <input type="text" name="last_name" value="{{ old('last_name',$u->profile->last_name ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2">
               </div>
               <div>
                 <label class="block text-sm text-gray-700 mb-1">Имя</label>
                 <input type="text" name="first_name" value="{{ old('first_name',$u->profile->first_name ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2">
               </div>
               <div>
                 <label class="block text-sm text-gray-700 mb-1">Отчество</label>
                 <input type="text" name="middle_name" value="{{ old('middle_name',$u->profile->middle_name ?? '') }}" class="w-full border border-gray-300 rounded-md px-3 py-2">
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
         <button type="button" id="addPhoneBtn" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">+ Телефон</button>
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
                  @if(auth()->user()->hasRole('admin'))
                 <button type="button" class="contact-edit-btn text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
                  @endif
               </div>
             </div>
             
             @if(auth()->user()->hasRole('admin'))
             <div class="contact-edit hidden mt-3">
               <form class="contact-form" action="{{ route('profile.primary.updatePhone') }}" method="post" data-contact-type="phone">@csrf @method('PUT')
                 <div class="space-y-3">
                   <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                       <input type="tel" name="phone" value="{{ $u->phone }}" class="w-full border border-gray-300 rounded-md px-3 py-2 phone-mask" placeholder="Введите номер телефона">
                   </div>
                 </div>
                 <div class="mt-3 flex justify-end gap-2">
                   <button type="button" class="contact-cancel-btn px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm">Отмена</button>
                   <button type="submit" class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 text-sm">Сохранить</button>
                 </div>
               </form>
             </div>
             @endif
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
                  @if(auth()->user()->hasRole('admin'))
                 <button type="button" class="contact-edit-btn text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
                  @endif
               </div>
             </div>
             
             @if(auth()->user()->hasRole('admin'))
             <div class="contact-edit hidden mt-3">
               <form class="contact-form" action="{{ route('profile.primary.updateEmail') }}" method="post" data-contact-type="email">@csrf @method('PUT')
                 <div class="space-y-3">
                   <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                       <input type="email" name="email" id="primaryEmailInput" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                   </div>
                 </div>
                 <div class="mt-3 flex justify-end gap-2">
                   <button type="button" class="contact-cancel-btn px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm">Отмена</button>
                   <button type="submit" class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 text-sm">Сохранить</button>
                 </div>
               </form>
             </div>
             @endif
           </div>
         </div>
       </div>

       <!-- Дополнительные контакты -->
       <div>
         <h3 class="text-md font-semibold text-gray-800 mb-3">Дополнительные контакты</h3>
         
                             <div class="space-y-3" id="additionalContacts">
                                             @if(($u->phones->whereNotNull('value')->count() + $u->emails->whereNotNull('value')->count()) === 0)
                        <div class="text-center text-gray-600 py-8">
                          <div class="text-4xl mb-2">📭</div>
                          <div class="text-sm">Пока ничего нет. Добавьте данные.</div>
                        </div>
                      @endif
                       @foreach($u->phones as $p)
              @if($p->value)
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
                        <input type="tel" name="value" value="{{ $p->value }}" class="w-full border border-gray-300 rounded-md px-3 py-2 phone-mask" placeholder="Введите номер телефона" required>
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
              @endif
            @endforeach

                       @foreach($u->emails as $e)
              @if($e->value)
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
              @endif
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
         @if(auth()->user()->hasRole('admin'))
         <button type="button" id="editLoginBtn" class="text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
         @endif
       </div>
       
               @if(auth()->user()->hasRole('admin'))
       <div id="loginEdit" class="hidden">
         <form id="loginForm" action="{{ route('profile.about.updateLogin') }}" method="post">@csrf @method('PUT')
           <div class="flex items-center gap-3">
                <input type="email" name="email" id="loginEmailInput" class="flex-1 border border-gray-300 rounded-md px-3 py-2" required>
             <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Сохранить</button>
             <button type="button" id="cancelLoginBtn" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Отменить</button>
           </div>
         </form>
       </div>
        @endif
     </div>
     
     <!-- Пароль -->
     <div>
         <label class="block text-sm font-medium text-gray-700 mb-2">Пароль:</label>
         <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 shadow-sm">
             <div class="flex items-center justify-between">
                 <div class="flex-1">
                     <div class="text-sm text-gray-900">••••••••</div>
                     <div class="text-xs text-gray-500 mt-1">Пароль установлен и защищён</div>
                 </div>
                 <button type="button" onclick="openChangePasswordModal()" class="text-orange-500 hover:text-orange-600 transition-colors w-8 text-center">
                     <span class="text-lg">✏️</span>
                 </button>
             </div>
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

                 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
     @if($u->documents->count() === 0)
          <div class="text-center text-gray-600 py-10 col-span-full">
         <div class="text-5xl mb-3">🗂️</div>
         <div>Пока ничего нет. Добавьте данные.</div>
       </div>
     @endif
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
                   @if($d->files && count($d->files) > 0)
                     📷 {{ count($d->files) }} фото
                   @else
                     Нет фотографий
                   @endif
                 </div>
               </div>
             </div>
             <div class="text-green-600 text-lg">✔</div>
           </div>
           
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
               'id'=>$d->id,'type'=>$d->type,'files'=>$d->files
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

       <!-- Модальное окно подтверждения удаления фотографии -->
  <div id="photoDeleteModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <div class="relative z-10 max-w-md mx-auto mt-24 bg-white rounded-xl shadow-lg p-6">
      <div class="text-center">
        <div class="text-4xl mb-4">🗑️</div>
        <h3 class="text-lg font-semibold mb-2">Удалить фотографию?</h3>
        <p class="text-gray-600 mb-2">Фотография "<span id="photoDeleteName" class="font-medium text-gray-900"></span>" будет удалена</p>
        <p class="text-gray-500 mb-6 text-sm">Действие необратимо</p>
        <div class="flex justify-center gap-3">
          <button type="button" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50" data-close>Отмена</button>
          <button type="button" id="confirmPhotoDeleteBtn" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Удалить</button>
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
               <div class="text-sm text-gray-600">Фотографии паспорта РФ</div>
             </div>
           </div>
         </button>
         <button type="button" class="w-full p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-left" data-doc-type="foreign_passport">
           <div class="flex items-center gap-3">
             <div class="text-2xl">🛂</div>
             <div>
               <div class="font-semibold">Загранпаспорт</div>
               <div class="text-sm text-gray-600">Фотографии загранпаспорта</div>
             </div>
           </div>
         </button>
         <button type="button" class="w-full p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-left" data-doc-type="driver_license">
           <div class="flex items-center gap-3">
             <div class="text-2xl">🚗</div>
             <div>
               <div class="font-semibold">Водительские права</div>
               <div class="text-sm text-gray-600">Фотографии водительских прав</div>
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
         
         <!-- Загрузка файлов -->
         <div class="mt-4">
           <label class="block text-sm font-medium text-gray-700 mb-1">Приложить фотографии</label>
           <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
             <input type="file" name="files[]" id="docFiles" multiple accept=".jpg,.jpeg,.png" class="hidden">
             <label for="docFiles" class="cursor-pointer">
               <div class="text-4xl mb-2">📷</div>
               <div class="text-sm text-gray-600">Нажмите для выбора фотографий</div>
               <div class="text-xs text-gray-500 mt-1">JPG, PNG до 10 МБ каждая</div>
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
           <span id="docViewTitle">Просмотр фотографий</span>
         </div>
         <button class="text-gray-500" data-close>✕</button>
       </div>
       
       <!-- Галерея фотографий -->
         <div>
         <div id="docViewGallery" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
             <!-- Заполняется JavaScript -->
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
         modal.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', () => {
           // Проверяем, существует ли модальное окно перед закрытием
           if (modal && modal.parentNode) {
             modal.classList.add('hidden');
           }
         }));
         modal.addEventListener('click', (e) => { 
           if (e.target === modal && modal && modal.parentNode) {
             modal.classList.add('hidden'); 
           }
         });
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
       const docViewGallery = document.getElementById('docViewGallery');

       // Открытие модального окна выбора типа
       if (addDocumentBtn) {
         addDocumentBtn.addEventListener('click', () => {
             // Проверяем, не заблокирована ли кнопка
             if (addDocumentBtn.disabled) {
               return;
             }
             
             // Обновляем состояние кнопки перед открытием модального окна
             updateAddDocumentButton();
             
             // Проверяем еще раз после обновления
             if (addDocumentBtn.disabled) {
               return;
             }
             

             
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
           
           // Проверяем, существует ли уже документ такого типа
           const existingDocuments = document.querySelectorAll('[data-view-doc]');
           let existingDocument = null;
           let existingDocumentId = null;
           
           existingDocuments.forEach(docBtn => {
             const documentElement = docBtn.closest('.border');
             const payload = documentElement.querySelector('template.payload');
             if (payload) {
               try {
                 const data = JSON.parse(payload.innerHTML.trim());
                 if (data.type === type) {
                   existingDocument = documentElement;
                   existingDocumentId = data.id;
                 }
               } catch (e) {
                 console.error('Error parsing document data:', e);
               }
             }
           });
           
           // Если документ такого типа уже существует, открываем форму редактирования
           if (existingDocument && existingDocumentId) {
             // Находим данные существующего документа
             const payload = existingDocument.querySelector('template.payload');
             const data = JSON.parse(payload.innerHTML.trim());
             
             // Заполняем форму для редактирования
             document.getElementById('docType').value = type;
             document.getElementById('docFormTitle').textContent = 'Добавить фотографии к документу';
             document.getElementById('docFormSubtitle').textContent = typeNames[type];
             
             // Устанавливаем правильный action и метод для обновления
             docForm.setAttribute('action', `/profile/documents/${existingDocumentId}`);
             docForm.setAttribute('method', 'POST');
             
             // Добавляем скрытое поле _method для PUT запроса
             let methodField = docForm.querySelector('input[name="_method"]');
             if (!methodField) {
               methodField = document.createElement('input');
               methodField.type = 'hidden';
               methodField.name = '_method';
               docForm.appendChild(methodField);
             }
             methodField.value = 'PUT';
             
             // Очищаем форму и превью для добавления новых фотографий
             docForm.reset();
             docFiles.value = '';
             filePreview.innerHTML = '';
             
             // Восстанавливаем поле _method после сброса формы
             if (methodField) {
               // Проверяем, не было ли поле удалено при сбросе
               const existingMethodField = docForm.querySelector('input[name="_method"]');
               if (!existingMethodField) {
                 docForm.appendChild(methodField);
               } else {
                 // Если поле существует, обновляем его значение
                 existingMethodField.value = 'PUT';
               }
             }
             
             // Показываем существующие фотографии
             displayExistingPhotos(data.files || [], existingDocumentId);
             
             // Переключение модальных окон
             docTypeModal.classList.add('hidden');
             docFormModal.classList.remove('hidden');
             return;
           }
           
           // Если документ не существует, создаем новый
           
           // Заполнение формы для нового документа
           document.getElementById('docType').value = type;
           document.getElementById('docFormTitle').textContent = 'Добавить документ';
           document.getElementById('docFormSubtitle').textContent = typeNames[type];
           
           // Устанавливаем правильный action и метод для создания
           docForm.setAttribute('action', '{{ route("profile.documents.store") }}');
           docForm.setAttribute('method', 'POST');
           
           // Удаляем скрытое поле _method если оно есть
           const methodField = docForm.querySelector('input[name="_method"]');
           if (methodField) {
             methodField.remove();
           }
           

           
           // Очищаем форму и превью для нового документа
           docForm.reset();
           docFiles.value = '';
           filePreview.innerHTML = '';
           
           // Переключение модальных окон
           docTypeModal.classList.add('hidden');
           docFormModal.classList.remove('hidden');
         });
       });

               // Функция для отображения существующих фотографий при редактировании
        function displayExistingPhotos(files, documentId) {
          if (!filePreview) {
            return;
          }
          
          filePreview.innerHTML = '';
          
          if (!files || files.length === 0) {
            filePreview.innerHTML = '<div class="text-gray-500 text-center p-4">Нет загруженных фотографий</div>';
            return;
          }
          
          files.forEach((file, index) => {
            // Проверяем, является ли file объектом или строкой
            const filePath = typeof file === 'object' ? file.path : file;
            const fileName = typeof file === 'object' ? file.name : `Фото ${index + 1}`;
            
            const photoDiv = document.createElement('div');
            photoDiv.className = 'relative bg-gray-50 rounded border p-2';
            photoDiv.setAttribute('data-photo-index', index);
            photoDiv.setAttribute('data-document-id', documentId);
            photoDiv.innerHTML = `
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">${fileName}</span>
                <button type="button" class="text-red-600 hover:text-red-800 text-sm font-medium delete-photo-btn">
                  🗑️ Удалить
                </button>
              </div>
              <div class="aspect-square bg-gray-100 rounded overflow-hidden">
                <img src="/storage/${filePath}" alt="${fileName}" class="w-full h-full object-cover">
              </div>
            `;
            
            // Добавляем обработчик удаления для существующих фотографий
            const deleteBtn = photoDiv.querySelector('.delete-photo-btn');
            deleteBtn.addEventListener('click', (e) => {
              e.preventDefault();
              showDeletePhotoConfirmation(documentId, index, fileName);
            });
            
            filePreview.appendChild(photoDiv);
          });
        }
        
        // Функция для показа модального окна подтверждения удаления фотографии
        function showDeletePhotoConfirmation(documentId, photoIndex, photoName) {
          const photoDeleteModal = document.getElementById('photoDeleteModal');
          const photoDeleteName = document.getElementById('photoDeleteName');
          const confirmPhotoDeleteBtn = document.getElementById('confirmPhotoDeleteBtn');
          
          if (photoDeleteName) {
            photoDeleteName.textContent = photoName;
          }
          
          photoDeleteModal.classList.remove('hidden');
          
          // Обработчик подтверждения удаления
          const handleConfirm = () => {
            const photoElement = document.querySelector(`[data-photo-index="${photoIndex}"][data-document-id="${documentId}"]`);
            if (photoElement) {
              deleteExistingPhoto(documentId, photoIndex, photoElement);
            }
            photoDeleteModal.classList.add('hidden');
            confirmPhotoDeleteBtn.removeEventListener('click', handleConfirm);
          };
          
          confirmPhotoDeleteBtn.addEventListener('click', handleConfirm);
        }

        // Функция для удаления существующей фотографии
        function deleteExistingPhoto(documentId, photoIndex, photoElement) {
          
          fetch(`/profile/documents/${documentId}/photo/${photoIndex}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(response => {
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              // Удаляем элемент из DOM
              photoElement.remove();
              
              // Обновляем данные документа в payload
              const documentElement = document.querySelector(`[data-edit-doc="${documentId}"]`)?.closest('.border.border-gray-200.rounded-lg');
              if (documentElement && data.document) {
                const payloadTemplate = documentElement.querySelector('template.payload');
                if (payloadTemplate) {
                  payloadTemplate.innerHTML = JSON.stringify(data.document);
                }
                
                // Обновляем отображение количества фотографий в карточке документа
                const photoCountElement = documentElement.querySelector('.text-sm.text-gray-600');
                if (photoCountElement && data.document.files) {
                  photoCountElement.textContent = data.document.files.length > 0 ? `📷 ${data.document.files.length} фото` : 'Нет фотографий';
                }
              }
              
              showNotification('Фотография удалена', 'success');
            } else {
              throw new Error(data.message || 'Ошибка удаления');
            }
          })
          .catch(error => {
            console.error('Ошибка при удалении фотографии:', error);
            showNotification('Ошибка при удалении фотографии: ' + error.message, 'error');
          });
        }

        // Функция для добавления документа в список
        function addDocumentToList(documentData) {
          // Проверяем, что documentData существует и содержит необходимые данные
          if (!documentData || !documentData.type) {
            console.error('Invalid document data:', documentData);
            return;
          }
          
          const documentsContainer = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3.gap-4');
          if (!documentsContainer) {
            console.error('Documents container not found');
            return;
          }
          
          const emptyState = documentsContainer.querySelector('.text-center.text-gray-600.col-span-full');
          
          // Удаляем пустое состояние, если есть
          if (emptyState) {
            emptyState.remove();
          }
          
          const typeNames = {
            'passport': 'Паспорт РФ',
            'foreign_passport': 'Загранпаспорт',
            'driver_license': 'Водительские права'
          };
          
          const typeIcons = {
            'passport': '📄',
            'foreign_passport': '🛂',
            'driver_license': '🚗'
          };
          
          const documentHtml = `
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2">
                  <div class="text-2xl">${typeIcons[documentData.type] || '📄'}</div>
                  <div>
                    <div class="font-semibold text-gray-900">${typeNames[documentData.type] || 'Документ'}</div>
                    <div class="text-sm text-gray-600">
                      ${documentData.files && documentData.files.length > 0 ? `📷 ${documentData.files.length} фото` : 'Нет фотографий'}
                    </div>
                  </div>
                </div>
                <div class="text-green-600 text-lg">✔</div>
              </div>
              
              <div class="flex items-center gap-2">
                <button class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50 text-sm" data-view-doc="${documentData.id}" title="Просмотр">
                  👁️ Просмотр
                </button>
                <button class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50 text-sm" data-edit-doc="${documentData.id}" title="Редактировать">
                  ✏️ Изменить
                </button>
                <button class="px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700 text-sm" data-delete-doc="${documentData.id}" title="Удалить">
                  🗑️ Удалить
                </button>
              </div>
              
              <template class="payload">
                ${JSON.stringify(documentData)}
              </template>
            </div>
          `;
          
          documentsContainer.insertAdjacentHTML('beforeend', documentHtml);
          
          // Настраиваем обработчики для нового документа
          const newDocument = documentsContainer.lastElementChild;
          setupDocumentHandlers(newDocument);
        }
        
        // Функция для настройки обработчиков документа
        function setupDocumentHandlers(documentElement) {
          if (!documentElement) {
            return;
          }
          
          // Просмотр
          const viewBtn = documentElement.querySelector('[data-view-doc]');
          if (viewBtn) {
            // Удаляем существующие обработчики, чтобы избежать дублирования
            const newViewBtn = viewBtn.cloneNode(true);
            viewBtn.parentNode.replaceChild(newViewBtn, viewBtn);
            
            newViewBtn.addEventListener('click', () => {
              const id = newViewBtn.getAttribute('data-view-doc');
              const payload = documentElement.querySelector('template.payload');
              if (!payload) return;
              
              const data = JSON.parse(payload.innerHTML.trim());
              
              // Заполняем галерею фотографий
              if (!docViewGallery) {
                return;
              }
              
              if (data.files && data.files.length > 0) {
                docViewGallery.innerHTML = data.files.map(file => {
                  // Проверяем, является ли file объектом или строкой
                  const filePath = typeof file === 'object' ? file.path : file;
                  const fileName = typeof file === 'object' ? file.name : 'Фото документа';
                  
                  return `
                    <div class="bg-gray-50 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition">
                      <div class="aspect-square bg-gray-100 flex items-center justify-center">
                        <img src="/storage/${filePath}" alt="${fileName}" class="w-full h-full object-cover">
                      </div>
                      <div class="p-3">
                        <div class="text-sm font-medium text-gray-900 truncate">${fileName}</div>
                        <div class="text-xs text-gray-500">Изображение</div>
                      </div>
                    </div>
                  `;
                }).join('');
                           } else {
               docViewGallery.innerHTML = '<p class="text-gray-500 text-center col-span-full">Фотографии не загружены</p>';
              }
              
              if (docViewModal) {
                docViewModal.classList.remove('hidden');
              }
            });
          }
          
          // Редактирование
          const editBtn = documentElement.querySelector('[data-edit-doc]');
          if (editBtn) {
            // Удаляем существующие обработчики, чтобы избежать дублирования
            const newEditBtn = editBtn.cloneNode(true);
            if (editBtn.parentNode) {
              editBtn.parentNode.replaceChild(newEditBtn, editBtn);
            } else {
              return;
            }
            
            newEditBtn.addEventListener('click', () => {
              const id = newEditBtn.getAttribute('data-edit-doc');
              const payload = documentElement.querySelector('template.payload');
              if (!payload) {
                return;
              }
              
              try {
                const data = JSON.parse(payload.innerHTML.trim());
                
                const typeNames = {
                  'passport': 'Паспорт РФ',
                  'foreign_passport': 'Загранпаспорт',
                  'driver_license': 'Водительские права'
                };
                
                // Заполнение формы
                const docTypeField = document.getElementById('docType');
                const docFormTitle = document.getElementById('docFormTitle');
                const docFormSubtitle = document.getElementById('docFormSubtitle');
                const docFormElement = document.getElementById('docForm');
                
                if (!docFormElement) {
                  return;
                }
                
                let methodField = docFormElement.querySelector('input[name="_method"]');
                
                if (!methodField) {
                  methodField = document.createElement('input');
                  methodField.type = 'hidden';
                  methodField.name = '_method';
                  methodField.value = 'POST';
                  docFormElement.appendChild(methodField);
                }
                
                if (!docTypeField || !docFormTitle || !docFormSubtitle) {
                  console.error('Some form elements not found:', {
                    docTypeField: !!docTypeField,
                    docFormTitle: !!docFormTitle,
                    docFormSubtitle: !!docFormSubtitle
                  });
                  return;
                }
                
                docTypeField.value = data.type;
                docFormTitle.textContent = 'Редактировать фотографии';
                docFormSubtitle.textContent = typeNames[data.type];
                methodField.value = 'PUT';
                docFormElement.setAttribute('action', `/profile/documents/${id}`);
                

                
                // Очищаем поле выбора новых файлов
                if (docFiles) {
                  docFiles.value = '';
                }
                
                // Отображаем существующие фотографии
                displayExistingPhotos(data.files || [], id);
                
                if (docFormModal) {
                  docFormModal.classList.remove('hidden');
                } else {
                  console.error('docFormModal not found');
                }
              } catch (error) {
                console.error('Error parsing document data:', error);
              }
            });
          }
          
                     // Удаление
           const deleteBtn = documentElement.querySelector('[data-delete-doc]');
           if (deleteBtn) {
             // Удаляем существующие обработчики, чтобы избежать дублирования
             const newDeleteBtn = deleteBtn.cloneNode(true);
             deleteBtn.parentNode.replaceChild(newDeleteBtn, deleteBtn);
             
                          newDeleteBtn.addEventListener('click', () => {
               const id = newDeleteBtn.getAttribute('data-delete-doc');
               
               // Показываем модальное окно подтверждения
               if (docDeleteModal) {
                 docDeleteModal.classList.remove('hidden');
               } else {
                 return;
               }
               
               // Удаляем старый обработчик и создаем новый
               const confirmBtn = document.getElementById('confirmDeleteBtn');
               const newConfirmBtn = confirmBtn.cloneNode(true);
               confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
               
               // Обработка подтверждения удаления
               newConfirmBtn.addEventListener('click', () => {
                 fetch(`/profile/documents/${id}`, {
                   method: 'DELETE',
                   headers: {
                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                     'X-Requested-With': 'XMLHttpRequest'
                   }
                 })
                 .then(response => {
                   if (!response.ok) {
                     throw new Error(`HTTP error! status: ${response.status}`);
                   }
                   return response.json();
                 })
                 .then(data => {
                   if (data.success) {
                     // Закрываем модальное окно
                     if (docDeleteModal) {
                       docDeleteModal.classList.add('hidden');
                     }
                     // Удаляем элемент из DOM
                     documentElement.remove();
                     
                     // Показываем уведомление
                     showNotification('Документ успешно удален', 'success');
                     
                     // Проверяем, нужно ли показать пустое состояние
                     const documentsContainer = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3.gap-4');
                     if (documentsContainer) {
                       const remainingDocuments = documentsContainer.querySelectorAll('.border.border-gray-200.rounded-lg');
                                            if (remainingDocuments.length === 0) {
                         documentsContainer.innerHTML = `
                           <div class="text-center text-gray-600 py-10 col-span-full">
                             <div class="text-5xl mb-3">🗂️</div>
                             <div>Пока ничего нет. Добавьте данные.</div>
                           </div>
                         `;
                       }
                     } else {
                       console.error('documentsContainer not found');
                     }
                     
                     // Обновляем состояние кнопки "Добавить документ"
                     if (typeof updateAddDocumentButton === 'function') {
                       updateAddDocumentButton();
                     } else {
                       console.error('updateAddDocumentButton function not found');
                     }
                   } else {
                     throw new Error(data.message || 'Ошибка удаления');
                   }
                 })
                 .catch(error => {
                   console.error('Ошибка при удалении:', error);
                   showNotification('Ошибка при удалении документа: ' + error.message, 'error');
                 });
               });
             });
          }
        }
        
                 // Функция для обновления состояния кнопки "Добавить документ"
         function updateAddDocumentButton() {
           const addDocumentBtn = document.getElementById('addDocumentBtn');
           if (!addDocumentBtn) return;
           
           // Проверяем количество существующих документов
           const existingDocuments = document.querySelectorAll('[data-view-doc]');
           const existingTypes = new Set();
           
           existingDocuments.forEach(btn => {
             const documentElement = btn.closest('.border');
             const payload = documentElement.querySelector('template.payload');
             if (payload) {
               try {
                 const data = JSON.parse(payload.innerHTML.trim());
                 existingTypes.add(data.type);
               } catch (e) {
                 console.error('Error parsing document data:', e);
               }
             }
           });
           
           // Если есть все 3 типа документов, блокируем кнопку
           if (existingTypes.size >= 3) {
             addDocumentBtn.disabled = true;
             addDocumentBtn.textContent = 'Все документы добавлены';
             addDocumentBtn.classList.add('opacity-50', 'cursor-not-allowed');
             addDocumentBtn.classList.remove('hover:bg-blue-700');
           } else {
             addDocumentBtn.disabled = false;
             addDocumentBtn.textContent = '+ Добавить документ';
             addDocumentBtn.classList.remove('opacity-50', 'cursor-not-allowed');
             addDocumentBtn.classList.add('hover:bg-blue-700');
           }
         }
        


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
        // Удаляем старый обработчик если есть
        const oldHandler = docForm._submitHandler;
        if (oldHandler) {
          docForm.removeEventListener('submit', oldHandler);
        }
         
         // Создаем новый обработчик
         docForm._submitHandler = function(e) {
           e.preventDefault();
           
           const formData = new FormData(this);
           const submitBtn = this.querySelector('button[type="submit"]');
           const originalText = submitBtn.textContent;
           
           // Проверяем наличие поля _method в DOM
           const methodFieldInDOM = this.querySelector('input[name="_method"]');
           
           submitBtn.textContent = 'Сохранение...';
           submitBtn.disabled = true;
           fetch(this.action, {
             method: 'POST',
             body: formData,
             headers: {
               'X-Requested-With': 'XMLHttpRequest'
             }
           })
           .then(response => {
             if (!response.ok) {
               // Пытаемся получить JSON с ошибкой
               return response.json().then(errorData => {
                 throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
               }).catch(() => {
                 throw new Error(`HTTP error! status: ${response.status}`);
               });
             }
             return response.json();
           })
           .then(data => {
             if (data.success) {
               // Закрываем модальное окно
               if (docFormModal) {
                 docFormModal.classList.add('hidden');
               }
               
                // Очищаем форму
                if (docForm) {
                  docForm.reset();
                }
                if (docFiles) {
                  docFiles.value = '';
                }
                if (filePreview) {
                  filePreview.innerHTML = '';
                }
                
                // Определяем, это создание нового документа или обновление существующего
                const methodField = docForm ? docForm.querySelector('input[name="_method"]') : null;
                const isUpdate = methodField?.value === 'PUT';
                
                if (data.document) {
                  const documentId = data.document.id;
                  const existingDocument = document.querySelector(`[data-edit-doc="${documentId}"]`)?.closest('.border.border-gray-200.rounded-lg');
                  
                  if (existingDocument) {
                    // Обновляем существующий документ (через кнопку "Изменить" или добавление к существующему)
                    const payloadTemplate = existingDocument.querySelector('template.payload');
                    if (payloadTemplate) {
                      payloadTemplate.innerHTML = JSON.stringify(data.document);
                    }
                    
                    // Обновляем отображение количества фотографий
                    const photoCountElement = existingDocument.querySelector('.text-sm.text-gray-600');
                    if (photoCountElement && data.document.files) {
                      photoCountElement.textContent = data.document.files.length > 0 ? `📷 ${data.document.files.length} фото` : 'Нет фотографий';
                    }
                    
                    const message = isUpdate ? 'Документ успешно обновлен' : 'Фотографии добавлены к существующему документу';
                    showNotification(message, 'success');
                  } else {
                    // Добавляем новый документ
                    addDocumentToList(data.document);
                    showNotification('Документ успешно добавлен', 'success');
                  }
                }
                
                // Сбрасываем состояние кнопки
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                
                // Обновляем состояние кнопки "Добавить документ"
                if (typeof updateAddDocumentButton === 'function') {
                  updateAddDocumentButton();
                } else {
                  console.error('updateAddDocumentButton function not found');
                }
             } else {
               throw new Error(data.message || 'Ошибка сохранения');
             }
           })
           .catch(error => {
             console.error('Form submission error:', error);
             console.error('Error message:', error.message);
             console.error('Error stack:', error.stack);
             
             // Показываем ошибку пользователю
             let errorMessage = 'Произошла ошибка при сохранении документа';
             if (error.message) {
               errorMessage = error.message;
             }
             
             // Если есть ошибки валидации, показываем их
             if (error.errors) {
               const validationErrors = Object.values(error.errors).flat();
               errorMessage = validationErrors.join(', ');
             }
             
             // Если документ уже существует, предлагаем использовать кнопку "Изменить"
             if (errorMessage.includes('уже существует')) {
               errorMessage += '. Используйте кнопку "Изменить" для добавления фотографий.';
             }
             
             showNotification(errorMessage, 'error');
             
             submitBtn.textContent = originalText;
             submitBtn.disabled = false;
           });
         };
         
         // Добавляем обработчик
         docForm.addEventListener('submit', docForm._submitHandler);
       }







                // Закрытие модальных окон
          [docTypeModal, docFormModal, docViewModal, docDeleteModal].forEach(modal => {
           if (modal) {
             modal.querySelectorAll('[data-close]').forEach(el => {
                el.addEventListener('click', () => {
                  // Проверяем, существует ли модальное окно перед закрытием
                  if (modal && modal.parentNode) {
                    modal.classList.add('hidden');
                  }
                });
             });
             modal.addEventListener('click', (e) => {
                if (e.target === modal && modal && modal.parentNode) {
                  modal.classList.add('hidden');
                }
             });
           }
         });


          
          // Отдельная обработка для модального окна удаления контакта
          if (contactDeleteModal) {
            contactDeleteModal.addEventListener('click', (e) => {
              if (e.target === contactDeleteModal && contactDeleteModal && contactDeleteModal.parentNode) {
                contactDeleteModal.classList.add('hidden');
              }
            });
            
            // Обработка кнопок закрытия в модальном окне удаления контакта
            contactDeleteModal.querySelectorAll('[data-close]').forEach(closeBtn => {
              closeBtn.addEventListener('click', () => {
                if (contactDeleteModal && contactDeleteModal.parentNode) {
                  contactDeleteModal.classList.add('hidden');
                }
              });
            });
          }

          // Обработка для модального окна удаления фотографии
          const photoDeleteModal = document.getElementById('photoDeleteModal');
          if (photoDeleteModal) {
            photoDeleteModal.addEventListener('click', (e) => {
              if (e.target === photoDeleteModal && photoDeleteModal && photoDeleteModal.parentNode) {
                photoDeleteModal.classList.add('hidden');
              }
            });
            
            // Обработка кнопок закрытия в модальном окне удаления фотографии
            photoDeleteModal.querySelectorAll('[data-close]').forEach(closeBtn => {
              closeBtn.addEventListener('click', () => {
                if (photoDeleteModal && photoDeleteModal.parentNode) {
                  photoDeleteModal.classList.add('hidden');
                }
              });
            });
          }

          // Обработка для модального окна "Документ уже существует"
          const documentExistsModal = document.getElementById('documentExistsModal');
          if (documentExistsModal) {
            documentExistsModal.addEventListener('click', (e) => {
              if (e.target === documentExistsModal && documentExistsModal && documentExistsModal.parentNode) {
                documentExistsModal.classList.add('hidden');
              }
            });
            
            // Обработка кнопок закрытия в модальном окне "Документ уже существует"
            documentExistsModal.querySelectorAll('[data-close]').forEach(closeBtn => {
              closeBtn.addEventListener('click', () => {
                if (documentExistsModal && documentExistsModal.parentNode) {
                  documentExistsModal.classList.add('hidden');
                }
              });
            });
          }

         // ===== СИСТЕМА КОНТАКТОВ =====
         
         // Фильтрация для телефонов - разрешаем цифры и символы +, -, (, ), пробел
         function applyPhoneMask(input) {
           // Убираем все символы кроме цифр, +, -, (, ), пробел
           input.value = input.value.replace(/[^\d+\-()\s]/g, '');
         }

         // Применяем фильтрацию ко всем полям телефонов
         document.querySelectorAll('.phone-mask').forEach(input => {
           input.addEventListener('input', () => applyPhoneMask(input));
         });

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
             
             // Заполняем поле актуальным значением для основного email
             const contactType = contactItem.getAttribute('data-contact-type');
             const isPrimary = contactItem.getAttribute('data-type') === 'primary';
             
             if (isPrimary && contactType === 'email') {
               const currentEmailDisplay = contactItem.querySelector('.font-semibold');
               const primaryEmailInput = document.getElementById('primaryEmailInput');
               if (currentEmailDisplay && primaryEmailInput) {
                 primaryEmailInput.value = currentEmailDisplay.textContent;
               }
             }
             
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

                   // Обработка форм контактов (включая основные)
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
                 'X-Requested-With': 'XMLHttpRequest',
                 'Accept': 'application/json'
               }
             })
             .then(response => {

               
               if (!response.ok) {
                 throw new Error(`HTTP error! status: ${response.status}`);
               }
               
               const contentType = response.headers.get('content-type');
               if (contentType && contentType.includes('application/json')) {
                 return response.json();
               } else {
                 throw new Error('Server returned HTML instead of JSON');
               }
             })
             .then(data => {
               if (data.success) {
                 // Обновляем отображаемые данные
                 const valueDiv = view.querySelector('.font-semibold');
                 const commentDiv = view.querySelector('.text-sm.text-gray-500');
                 
                 if (contactType === 'phone') {
                    valueDiv.textContent = data.value || data.phone || 'Не указан';
                 } else {
                   valueDiv.textContent = data.value || data.email;
                    
                    // Синхронизация: если это основной email, обновляем логин
                    const contactItem = this.closest('.contact-item');
                    if (contactItem && contactItem.getAttribute('data-type') === 'primary') {
                      const loginSpan = document.querySelector('#loginDisplay span');
                      if (loginSpan) {
                        loginSpan.textContent = data.value || data.email;
                      }
                    }
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
                 
                 let errorMessage = 'Ошибка при сохранении контакта';
                 if (error.message.includes('HTML instead of JSON')) {
                   errorMessage = 'Сервер вернул HTML вместо JSON. Возможно, произошла ошибка на сервере.';
                 } else if (error.message.includes('HTTP error')) {
                   errorMessage = `Ошибка HTTP: ${error.message}`;
                 }
                 
                 showNotification(errorMessage, 'error');
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
                 ? `/profile/phones/${contactId}`
                 : `/profile/emails/${contactId}`;
               
               fetch(deleteUrl, {
                 method: 'DELETE',
                 headers: {
                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                   'X-Requested-With': 'XMLHttpRequest',
                   'Accept': 'application/json'
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
                       const remainingContacts = additionalContacts.querySelectorAll('.contact-item[data-id]');
                       if (remainingContacts.length === 0) {
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
              
              // Обработка отмены удаления - используем уже установленные обработчики
              // Дополнительные обработчики не нужны, так как они уже установлены выше
           });
         });

         // Добавление новых контактов
         const addPhoneBtn = document.getElementById('addPhoneBtn');
         const addEmailBtn = document.getElementById('addEmailBtn');
         const additionalContacts = document.getElementById('additionalContacts');

         function createNewContactItem(type) {
           const isPhone = type === 'phone';
           const icon = isPhone ? '📱' : '✉️';
           const placeholder = isPhone ? 'Введите номер телефона' : 'you@example.com';
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
              
              // Удаляем только пустые состояния, сохраняя существующие контакты
              const emptyStates = additionalContacts.querySelectorAll('.text-center.text-gray-600');
              emptyStates.forEach(state => state.remove());
              
              // Закрываем все открытые редакторы
              closeAllContactEditors();
              
              // Добавляем новый элемент в начало (телефон всегда сверху)
              const newContactHtml = createNewContactItem('phone');
              additionalContacts.insertAdjacentHTML('afterbegin', newContactHtml);
             
             // Настраиваем обработчики для нового элемента
             const newContact = additionalContacts.firstElementChild;
             
             // Фильтрация для телефона - разрешаем цифры и символы +, -, (, ), пробел
             const phoneInput = newContact.querySelector('.phone-mask');
             if (phoneInput) {
               phoneInput.addEventListener('input', () => applyPhoneMask(phoneInput));
               setTimeout(() => phoneInput.focus(), 100);
             }
             
             // Обработчик отмены
             const cancelBtn = newContact.querySelector('.contact-cancel-btn');
             cancelBtn.addEventListener('click', () => {
               // Проверяем, существует ли элемент перед удалением
               if (newContact && newContact.parentNode) {
               newContact.remove();
               // Проверяем, нужно ли показать пустое состояние
                 const existingContacts = additionalContacts.querySelectorAll('.contact-item[data-id]');
                 if (existingContacts.length === 0) {
                 additionalContacts.innerHTML = `
                   <div class="text-center text-gray-600 py-8">
                     <div class="text-4xl mb-2">📭</div>
                     <div class="text-sm">Пока ничего нет. Добавьте данные.</div>
                   </div>
                 `;
                 }
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
                   'X-Requested-With': 'XMLHttpRequest',
                   'Accept': 'application/json'
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
                           <div class="font-semibold text-gray-900">${data.value || 'Не указан'}</div>
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
                             <input type="tel" name="value" value="${data.value}" class="w-full border border-gray-300 rounded-md px-3 py-2 phone-mask" placeholder="Введите номер телефона" required>
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
                   
                   // Перемещаем телефон наверх
                   additionalContacts.insertBefore(newContact, additionalContacts.firstChild);
                   
                   // Сортируем контакты для правильного порядка
                   sortContacts();
                   
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
              
              // Удаляем только пустые состояния, сохраняя существующие контакты
              const emptyStates = additionalContacts.querySelectorAll('.text-center.text-gray-600');
              emptyStates.forEach(state => state.remove());
              
              // Закрываем все открытые редакторы
              closeAllContactEditors();
              
              // Добавляем новый элемент в конец (email всегда снизу)
              const newContactHtml = createNewContactItem('email');
              additionalContacts.insertAdjacentHTML('beforeend', newContactHtml);
             
             // Настраиваем обработчики для нового элемента
             const newContact = additionalContacts.lastElementChild;
             
             // Фокус на поле ввода
             const emailInput = newContact.querySelector('input[type="email"]');
             if (emailInput) {
               setTimeout(() => emailInput.focus(), 100);
             }
             
             // Обработчик отмены
             const cancelBtn = newContact.querySelector('.contact-cancel-btn');
             cancelBtn.addEventListener('click', () => {
               // Проверяем, существует ли элемент перед удалением
               if (newContact && newContact.parentNode) {
               newContact.remove();
               // Проверяем, нужно ли показать пустое состояние
                 const existingContacts = additionalContacts.querySelectorAll('.contact-item[data-id]');
                 if (existingContacts.length === 0) {
                 additionalContacts.innerHTML = `
                   <div class="text-center text-gray-600 py-8">
                     <div class="text-4xl mb-2">📭</div>
                     <div class="text-sm">Пока ничего нет. Добавьте данные.</div>
                   </div>
                 `;
                 }
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
                   'X-Requested-With': 'XMLHttpRequest',
                   'Accept': 'application/json'
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
                   
                   // Email остается в конце (не перемещаем)
                   
                   // Сортируем контакты для правильного порядка
                   sortContacts();
                   
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
                   ? `/profile/phones/${contactId}`
                   : `/profile/emails/${contactId}`;
                 
                 fetch(deleteUrl, {
                   method: 'DELETE',
                   headers: {
                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                     'X-Requested-With': 'XMLHttpRequest',
                     'Accept': 'application/json'
                   }
                 })
                 .then(response => response.json())
                 .then(data => {
                                       if (data.success) {
                      contactItem.remove();
                      contactDeleteModal.classList.add('hidden');
                      
                      // Показываем уведомление об успешном удалении
                      showNotification('Контакт успешно удален', 'success');
                      
                       // Проверяем, нужно ли показать пустое состояние
                       const remainingContacts = additionalContacts.querySelectorAll('.contact-item[data-id]');
                       if (remainingContacts.length === 0) {
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
                
                // Обработка отмены удаления - используем уже установленные обработчики
                // Дополнительные обработчики не нужны, так как они уже установлены выше
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
                   'X-Requested-With': 'XMLHttpRequest',
                   'Accept': 'application/json'
                 }
               })
               .then(response => response.json())
               .then(data => {
                 if (data.success) {
                   const valueDiv = view.querySelector('.font-semibold');
                   const commentDiv = view.querySelector('.text-sm.text-gray-500');
                   
                   if (contactType === 'phone') {
                     valueDiv.textContent = data.value || 'Не указан';
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
           
           // Фильтрация для телефонов - разрешаем цифры и символы +, -, (, ), пробел
           const phoneInput = contactItem.querySelector('.phone-mask');
           if (phoneInput) {
             phoneInput.addEventListener('input', () => applyPhoneMask(phoneInput));
           }
         }

                           // Функция для синхронизации основного email и логина
        function syncEmailAndLogin(newEmail, source) {
          // Если изменился основной email, обновляем логин
          if (source === 'primary-email') {
            const loginSpan = document.querySelector('#loginDisplay span');
            if (loginSpan) {
              loginSpan.textContent = newEmail;
            }
          }
          // Если изменился логин, обновляем основной email
          else if (source === 'login') {
            const primaryEmailDiv = document.querySelector('[data-type="primary"][data-contact-type="email"] .font-semibold');
            if (primaryEmailDiv) {
              primaryEmailDiv.textContent = newEmail;
            }
          }
        }

        // Функция для сортировки контактов (телефон сверху, email снизу)
        function sortContacts() {
            const contacts = additionalContacts.querySelectorAll('.contact-item[data-id]');
            const phones = [];
            const emails = [];
            
            contacts.forEach(contact => {
              const type = contact.getAttribute('data-contact-type');
              if (type === 'phone') {
                phones.push(contact);
              } else if (type === 'email') {
                emails.push(contact);
              }
            });
            
            // Очищаем контейнер и добавляем сначала телефоны, потом email
            phones.forEach(phone => additionalContacts.appendChild(phone));
            emails.forEach(email => additionalContacts.appendChild(email));
          }
          
          // Сортируем контакты при загрузке страницы
          sortContacts();

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
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              }
            })
            .then(response => {
              if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
              }
              return response.json();
            })
            .then(data => {
              if (data.success) {
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
              } else {
                throw new Error(data.message || 'Неизвестная ошибка');
              }
            })
            .catch(error => {
              console.error('Ошибка:', error);
              console.error('Полный ответ сервера:', error.message);
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
             // Получаем текущее значение логина из отображения
             const currentLogin = loginDisplay.querySelector('span').textContent;
             
             // Заполняем поле ввода текущим значением
             const loginEmailInput = document.getElementById('loginEmailInput');
             if (loginEmailInput) {
               loginEmailInput.value = currentLogin;
             }
             
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
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Обновляем отображаемый email
                const emailSpan = loginDisplay.querySelector('span');
                emailSpan.textContent = data.email;
                 
                 // Синхронизация: обновляем основной email
                 const primaryEmailDiv = document.querySelector('[data-type="primary"][data-contact-type="email"] .font-semibold');
                 if (primaryEmailDiv) {
                   primaryEmailDiv.textContent = data.email;
                 }
                
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
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
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

        // Загрузка фото профиля
        const uploadPhotoBtn = document.getElementById('uploadPhotoBtn');
        const profilePhotoInput = document.getElementById('profilePhotoInput');
        const profilePhotoContainer = document.getElementById('profilePhotoContainer');

        if (uploadPhotoBtn && profilePhotoInput) {
          uploadPhotoBtn.addEventListener('click', () => {
            profilePhotoInput.click();
          });

          profilePhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Проверяем тип файла
            if (!file.type.startsWith('image/')) {
              showNotification('Пожалуйста, выберите изображение', 'error');
              return;
            }

            // Проверяем размер файла (максимум 5MB)
            if (file.size > 5 * 1024 * 1024) {
              showNotification('Размер файла не должен превышать 5MB', 'error');
              return;
            }

            // Показываем превью
            const reader = new FileReader();
            reader.onload = function(e) {
              profilePhotoContainer.innerHTML = `<img src="${e.target.result}" alt="Фото профиля" class="w-full h-full object-cover">`;
            };
            reader.readAsDataURL(file);

            // Отправляем файл на сервер
            const formData = new FormData();
            formData.append('photo', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            uploadPhotoBtn.textContent = 'Загрузка...';
            uploadPhotoBtn.disabled = true;

            fetch('{{ route("profile.about.updatePhoto") }}', {
              method: 'POST',
              body: formData,
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              }
            })
            .then(response => {
              if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
              }
              return response.json();
            })
                        .then(data => {
              if (data.success) {
                showNotification('Фото успешно загружено', 'success');
              } else {
                throw new Error(data.message || 'Ошибка загрузки');
              }
            })
            .catch(error => {
              console.error('Ошибка:', error);
              showNotification('Ошибка при загрузке фото', 'error');
              // Возвращаем иконку по умолчанию
              profilePhotoContainer.innerHTML = '<span>👤</span>';
            })
            .finally(() => {
              uploadPhotoBtn.textContent = 'Загрузить фото';
              uploadPhotoBtn.disabled = false;
              // Очищаем input
              profilePhotoInput.value = '';
            });
          });
        }

                 // Инициализация при загрузке страницы
         updateAddDocumentButton();
         
                 // Настраиваем обработчики для существующих документов
        // Инициализируем обработчики для существующих документов
        const documentsContainer = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3.gap-4');
        if (documentsContainer) {
          const documentElements = documentsContainer.querySelectorAll('.border.border-gray-200.rounded-lg');
          documentElements.forEach((element, index) => {
            setupDocumentHandlers(element);
          });
        }

         // Обработчики для показа/скрытия пароля
         const togglePassword = document.getElementById('togglePassword');
        const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
        const passwordInput = document.getElementById('passwordInput');
        const passwordConfirmInput = document.getElementById('passwordConfirmInput');
        const passwordEye = document.getElementById('passwordEye');
        const passwordEyeSlash = document.getElementById('passwordEyeSlash');
        const passwordConfirmEye = document.getElementById('passwordConfirmEye');
        const passwordConfirmEyeSlash = document.getElementById('passwordConfirmEyeSlash');

        // Функция для переключения видимости пароля
        function togglePasswordVisibility(input, eyeIcon, eyeSlashIcon) {
          if (input.type === 'password') {
            input.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeSlashIcon.classList.remove('hidden');
          } else {
            input.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeSlashIcon.classList.add('hidden');
          }
        }

        // Обработчик для основного поля пароля
        if (togglePassword && passwordInput && passwordEye && passwordEyeSlash) {
          togglePassword.addEventListener('click', () => {
            togglePasswordVisibility(passwordInput, passwordEye, passwordEyeSlash);
          });
        }

        // Обработчик для поля подтверждения пароля
        if (togglePasswordConfirm && passwordConfirmInput && passwordConfirmEye && passwordConfirmEyeSlash) {
          togglePasswordConfirm.addEventListener('click', () => {
            togglePasswordVisibility(passwordConfirmInput, passwordConfirmEye, passwordConfirmEyeSlash);
          });
        }

     });

     // Функции для модального окна смены пароля
     function openChangePasswordModal() {
         document.getElementById('changePasswordModal').classList.remove('hidden');
     }

     function closeModal(modalId) {
         document.getElementById(modalId).classList.add('hidden');
     }

     function togglePasswordVisibilityModal(inputId) {
         const input = document.getElementById(inputId);
         const eyeIcon = document.getElementById(inputId + '-eye');
         
         if (input.type === 'password') {
             input.type = 'text';
             eyeIcon.textContent = '🙈';
         } else {
             input.type = 'password';
             eyeIcon.textContent = '👁️';
         }
     }

     // Обработчик формы смены пароля
     document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
         e.preventDefault();
         
         const newPassword = document.getElementById('new-password').value;
         const confirmPassword = document.getElementById('confirm-password').value;
         
         if (newPassword !== confirmPassword) {
             alert('Пароли не совпадают');
             return;
         }
         
         // Валидация пароля
         if (newPassword.length < 8) {
             alert('Пароль должен содержать минимум 8 символов');
             return;
         }
         
         if (!/\d/.test(newPassword) || !/[a-zA-Z]/.test(newPassword)) {
             alert('Пароль должен содержать буквы и цифры');
             return;
         }
         
         const formData = new FormData(this);
         
         fetch('{{ route("profile.about.updatePassword") }}', {
             method: 'POST',
             headers: {
                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
             },
             body: formData
         })
         .then(response => response.json())
         .then(data => {
             if (data.success) {
                 alert('Пароль обновлён');
                 closeModal('changePasswordModal');
                 this.reset();
             } else {
                 alert(data.message || 'Ошибка при смене пароля');
             }
         })
         .catch(error => {
             console.error('Ошибка при смене пароля:', error);
             alert('Ошибка при смене пароля');
         });
     });
   </script>
 </div>

 <!-- Модальное окно смены пароля -->
 <div id="changePasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
     <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">
         <!-- Крестик закрытия -->
         <button onclick="closeModal('changePasswordModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
             </svg>
         </button>
         
         <div class="mb-6">
             <h2 class="text-2xl font-bold text-gray-900 mb-2">Сменить пароль</h2>
             <p class="text-sm text-gray-500">Введите новый пароль для своего аккаунта</p>
         </div>

         <form id="changePasswordForm" method="POST" action="{{ route('profile.about.updatePassword') }}">
             @csrf
             @method('PUT')
             
             <div class="mb-4">
                 <label for="new-password" class="block text-sm font-medium text-gray-700 mb-2">Новый пароль</label>
                 <div class="relative">
                     <input type="password" id="new-password" name="password" class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                     <button type="button" onclick="togglePasswordVisibilityModal('new-password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                         <span id="new-password-eye" class="text-lg">👁️</span>
                     </button>
                 </div>
             </div>

             <div class="mb-6">
                 <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">Подтверждение пароля</label>
                 <div class="relative">
                     <input type="password" id="confirm-password" name="password_confirmation" class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                     <button type="button" onclick="togglePasswordVisibilityModal('confirm-password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                         <span id="confirm-password-eye" class="text-lg">👁️</span>
                     </button>
                 </div>
             </div>

             <div class="mb-6 p-3 bg-blue-50 rounded-lg">
                 <p class="text-sm text-blue-700">
                     <strong>Требования к паролю:</strong><br>
                     • Минимум 8 символов<br>
                     • Хотя бы одна буква<br>
                     • Хотя бы одна цифра
                 </p>
             </div>

             <div class="flex justify-end space-x-3">
                 <button type="button" onclick="closeModal('changePasswordModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                     Отмена
                 </button>
                 <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                     Сохранить
                 </button>
             </div>
         </form>
     </div>
 </div>


