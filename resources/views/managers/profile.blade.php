<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $user->name }} - Профиль</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="antialiased">
<div class="min-h-screen bg-gray-50">
    @include('layouts.navigation')
    
    <div class="container mx-auto p-6">
        <!-- Хлебные крошки -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="/managers" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        Сотрудники
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $user->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Заголовок -->
        <div class="flex justify-between items-start mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                <p class="text-lg text-gray-500 mt-2">О себе</p>
            </div>
        </div>

        <!-- Основное -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Основное</h2>
                <div>
                    @php
                        $status = $user->employeeStatus?->status ?? 'free';
                        $statusConfig = [
                            'free' => ['text' => 'Свободен', 'class' => 'bg-green-100 text-green-800'],
                            'unavailable' => ['text' => 'Недоступен', 'class' => 'bg-red-100 text-red-800'],
                            'assigned' => ['text' => 'Назначен на проекты', 'class' => 'bg-blue-100 text-blue-800']
                        ];
                        $config = $statusConfig[$status] ?? $statusConfig['free'];
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $config['class'] }}">
                        Статус: {{ $config['text'] }}
                    </span>
                </div>
            </div>

                         <form id="main-form" class="space-y-6">
                 @csrf
                 <input type="hidden" name="user_id" value="{{ $user->id }}">
                
                <!-- Первая строка: ФИО -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Фамилия</label>
                                                 <input type="text" name="lastname" value="{{ $user->profile?->last_name ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ auth()->user()->hasRole('admin') ? '' : 'disabled:bg-gray-100 disabled:cursor-not-allowed' }}" {{ auth()->user()->hasRole('admin') ? '' : 'disabled' }}>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Имя</label>
                                                 <input type="text" name="firstname" value="{{ $user->profile?->first_name ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ auth()->user()->hasRole('admin') ? '' : 'disabled:bg-gray-100 disabled:cursor-not-allowed' }}" {{ auth()->user()->hasRole('admin') ? '' : 'disabled' }}>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Отчество</label>
                                                 <input type="text" name="middlename" value="{{ $user->profile?->middle_name ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ auth()->user()->hasRole('admin') ? '' : 'disabled:bg-gray-100 disabled:cursor-not-allowed' }}" {{ auth()->user()->hasRole('admin') ? '' : 'disabled' }}>
                    </div>
                </div>

                <!-- Вторая строка: Дата рождения, Город, Роль -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                         <div>
                         <label class="block text-sm font-medium text-gray-700 mb-2">Дата рождения</label>
                         <input type="date" name="birth_date" value="{{ $user->profile?->birth_date?->format('Y-m-d') ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ auth()->user()->hasRole('admin') ? '' : 'disabled:bg-gray-100 disabled:cursor-not-allowed' }}" {{ auth()->user()->hasRole('admin') ? '' : 'disabled' }}>
                     </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Город</label>
                        <input type="text" name="city" value="{{ $user->profile?->city ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ auth()->user()->hasRole('admin') ? '' : 'disabled:bg-gray-100 disabled:cursor-not-allowed' }}" {{ auth()->user()->hasRole('admin') ? '' : 'disabled' }}>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Роль</label>
                        <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ auth()->user()->hasRole('admin') ? '' : 'disabled:bg-gray-100 disabled:cursor-not-allowed' }}" {{ auth()->user()->hasRole('admin') ? '' : 'disabled' }}>
                            <option value="">Выберите роль</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ $user->roles->first() && $user->roles->first()->name === $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                                 <!-- Футер карточки -->
                 <div class="flex justify-end space-x-3 pt-6">
                     <button type="button" onclick="cancelEdit('main')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                         Отмена
                     </button>
                     <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                         Сохранить
                     </button>
                 </div>
            </form>
        </div>

        <!-- Контакты -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">📞</span>
                    <h2 class="text-xl font-semibold text-gray-900">Контакты</h2>
                </div>
                <div class="flex space-x-2">
                    <button type="button" id="addPhoneBtn" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">+ Телефон</button>
                    <button type="button" id="addEmailBtn" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">+ E‑mail</button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Основные контакты -->
                <div>
                    <h3 class="text-md font-semibold text-gray-800 mb-3">Основные контакты</h3>
                    
                    <div class="space-y-3">
                                                 <!-- Основной телефон -->
                                                   <div class="contact-item border border-gray-200 rounded-lg p-4 bg-gray-50 min-h-[80px]" data-type="primary" data-contact-type="phone">
                              <div class="flex items-start justify-between gap-3">
                                  <div class="contact-view flex items-center gap-3">
                                      <div class="text-xl">📱</div>
                                      <div>
                                          <div class="font-semibold text-gray-900">{{ $user->phone ?: 'Не указан' }}</div>
                                          @if($user->phone)
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
                                   <form class="contact-form" action="/employees/{{ $user->id }}/update-primary-phone" method="post" data-contact-type="phone">
                                       @csrf
                                       @method('PUT')
                                       <input type="hidden" name="type" value="phone">
                                       <input type="hidden" name="is_primary" value="1">
                                       <div class="space-y-3">
                                           <div>
                                               <label class="block text-sm font-medium text-gray-700 mb-1">Телефон:</label>
                                               <input type="tel" name="phone" value="{{ $user->phone }}" class="w-full border border-gray-300 rounded-md px-3 py-2 phone-mask" placeholder="Введите номер телефона">
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
                              <div class="flex items-start justify-between gap-3">
                                  <div class="contact-view flex items-center gap-3">
                                      <div class="text-xl">✉️</div>
                                      <div>
                                          <div class="font-semibold text-gray-900">{{ $user->email ?: 'Не указан' }}</div>
                                          @if($user->email)
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
                                   <form class="contact-form" action="/employees/{{ $user->id }}/update-primary-email" method="post" data-contact-type="email">
                                       @csrf
                                       @method('PUT')
                                       <input type="hidden" name="type" value="email">
                                       <input type="hidden" name="is_primary" value="1">
                                       <div class="space-y-3">
                                           <div>
                                               <label class="block text-sm font-medium text-gray-700 mb-1">Email:</label>
                                               <input type="email" name="email" value="{{ $user->email }}" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Введите email" required>
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
                                                 @php
                             $additionalPhones = $user->contacts->where('type', 'phone')->where('is_primary', 0);
                             $additionalEmails = $user->contacts->where('type', 'email')->where('is_primary', 0);
                         @endphp
                        
                        @if(($additionalPhones->count() + $additionalEmails->count()) === 0)
                            <div class="text-center text-gray-600 py-8">
                                <div class="text-4xl mb-2">📭</div>
                                <div class="text-sm">Пока ничего нет. Добавьте данные.</div>
                            </div>
                        @endif
                        
                        @foreach($additionalPhones as $phone)
                                                         <div class="contact-item border border-gray-200 rounded-lg p-4 bg-gray-50 hover:shadow-sm transition min-h-[80px]" data-type="additional" data-contact-type="phone" data-id="{{ $phone->id }}">
                                 <div class="flex items-start justify-between gap-3">
                                     <div class="contact-view flex items-center gap-3">
                                         <div class="text-xl">📱</div>
                                         <div>
                                             <div class="font-semibold text-gray-900">{{ $phone->value }}</div>
                                             @if($phone->comment)
                                                 <div class="text-sm text-gray-500">{{ $phone->comment }}</div>
                                             @endif
                                         </div>
                                     </div>
                                     <div class="flex items-center gap-2">
                                         <button type="button" class="contact-edit-btn text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
                                         <button type="button" class="contact-delete-btn text-red-600 hover:text-red-700" title="Удалить">🗑</button>
                                     </div>
                                 </div>
                                
                                <div class="contact-edit hidden mt-3">
                                                                         <form class="contact-form" action="/employees/{{ $user->id }}/update-contact" method="post" data-contact-type="phone">
                                         @csrf
                                         <input type="hidden" name="contact_id" value="{{ $phone->id }}">
                                         <input type="hidden" name="type" value="phone">
                                         <input type="hidden" name="is_primary" value="0">
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                                                <input type="tel" name="value" value="{{ $phone->value }}" class="w-full border border-gray-300 rounded-md px-3 py-2 phone-mask" placeholder="Введите номер телефона" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
                                                <input type="text" name="comment" value="{{ $phone->comment }}" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Комментарий">
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

                        @foreach($additionalEmails as $email)
                                                         <div class="contact-item border border-gray-200 rounded-lg p-4 bg-gray-50 hover:shadow-sm transition min-h-[80px]" data-type="additional" data-contact-type="email" data-id="{{ $email->id }}">
                                 <div class="flex items-start justify-between gap-3">
                                     <div class="contact-view flex items-center gap-3">
                                         <div class="text-xl">✉️</div>
                                         <div>
                                             <div class="font-semibold text-gray-900">{{ $email->value }}</div>
                                             @if($email->comment)
                                                 <div class="text-sm text-gray-500">{{ $email->comment }}</div>
                                             @endif
                                         </div>
                                     </div>
                                     <div class="flex items-center gap-2">
                                         <button type="button" class="contact-edit-btn text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
                                         <button type="button" class="contact-delete-btn text-red-600 hover:text-red-700" title="Удалить">🗑</button>
                                     </div>
                                 </div>
                                
                                <div class="contact-edit hidden mt-3">
                                                                         <form class="contact-form" action="/employees/{{ $user->id }}/update-contact" method="post" data-contact-type="email">
                                         @csrf
                                         <input type="hidden" name="contact_id" value="{{ $email->id }}">
                                         <input type="hidden" name="type" value="email">
                                         <input type="hidden" name="is_primary" value="0">
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                                <input type="email" name="value" value="{{ $email->value }}" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Введите email" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
                                                <input type="text" name="comment" value="{{ $email->comment }}" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Комментарий">
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
        </div>

        <!-- Учётная запись -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center space-x-2 mb-6">
                <span class="text-2xl">🔒</span>
                <h2 class="text-xl font-semibold text-gray-900">Учётная запись</h2>
            </div>

             <!-- Логин -->
             <div class="mb-6">
                 <label class="block text-sm text-gray-700 mb-2">Логин (email):</label>
                                   <div id="loginDisplay" class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-md">
                      <span class="text-gray-700">{{ $user->email }}</span>
                      @if(auth()->user()->hasRole('admin'))
                      <button type="button" id="editLoginBtn" class="text-blue-600 hover:text-blue-700 w-8 text-center" title="Редактировать">✏️</button>
                      @endif
                  </div>
                 
                 @if(auth()->user()->hasRole('admin'))
                 <div id="loginEdit" class="hidden">
                     <form id="loginForm" action="/employees/{{ $user->id }}/update-account" method="post">
                         @csrf
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
        </div>

        <!-- Кнопка "Назад" -->
        <div class="flex justify-start">
            <a href="/managers" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Назад к сотрудникам
            </a>
        </div>
    </div>
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
            <p class="text-sm text-gray-500">Введите новый пароль для сотрудника</p>
        </div>

        <form id="changePasswordForm">
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            
            <div class="mb-4">
                <label for="new-password" class="block text-sm font-medium text-gray-700 mb-2">Новый пароль</label>
                <div class="relative">
                    <input type="password" id="new-password" name="new_password" class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <button type="button" onclick="togglePasswordVisibility('new-password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <span id="new-password-eye" class="text-lg">👁️</span>
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">Подтверждение пароля</label>
                <div class="relative">
                    <input type="password" id="confirm-password" name="confirm_password" class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <button type="button" onclick="togglePasswordVisibility('confirm-password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
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

 <!-- Модальное окно подтверждения удаления контакта -->
 <div id="deleteContactModal" class="fixed inset-0 z-50 hidden">
     <div class="absolute inset-0 bg-black/40" data-close></div>
     <div class="relative z-10 max-w-md mx-auto mt-24 bg-white rounded-xl shadow-lg p-6">
         <div class="text-center">
             <div class="text-4xl mb-4">⚠️</div>
             <h3 class="text-lg font-semibold mb-2">Удалить контакт?</h3>
             <p class="text-gray-600 mb-6">Действие необратимо</p>
             <div class="flex justify-center gap-3">
                 <button type="button" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50" data-close>Отмена</button>
                 <button type="button" id="confirmDeleteContact" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Удалить</button>
             </div>
         </div>
     </div>
 </div>

   <!-- Toast уведомления -->
  <div id="toast" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 hidden">
      <span id="toast-message"></span>
  </div>

<script>
 // Глобальные переменные
 let isEditing = false;
 let hasUnsavedChanges = false;
 let contactToDelete = null; // Для хранения данных о контакте для удаления

// Функции модалок
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Переключение режима редактирования
function toggleEditMode(section) {
    const form = document.getElementById(section + '-form');
    const footer = document.getElementById(section + '-form-footer');
    const btn = document.getElementById('edit-' + section + '-btn');
    
    if (isEditing) {
        // Выход из режима редактирования
        disableForm(form);
        footer.classList.add('hidden');
        btn.textContent = 'Редактировать';
        isEditing = false;
        hasUnsavedChanges = false;
    } else {
        // Вход в режим редактирования
        enableForm(form);
        footer.classList.remove('hidden');
        btn.textContent = 'Отменить';
        isEditing = true;
    }
}

// Отмена редактирования
function cancelEdit(section) {
    const form = document.getElementById(section + '-form');
    const footer = document.getElementById(section + '-form-footer');
    const btn = document.getElementById('edit-' + section + '-btn');
    
    // Сброс формы к исходным значениям
    form.reset();
    
    disableForm(form);
    footer.classList.add('hidden');
    btn.textContent = 'Редактировать';
    isEditing = false;
    hasUnsavedChanges = false;
}

// Включение редактирования формы
function enableForm(form) {
    const inputs = form.querySelectorAll('input, select, textarea');
    const buttons = form.querySelectorAll('button[type="button"]');
    
    inputs.forEach(input => {
        if (!input.hasAttribute('data-original-disabled')) {
            input.removeAttribute('disabled');
        }
    });
    
    buttons.forEach(button => {
        if (!button.hasAttribute('data-original-disabled')) {
            button.removeAttribute('disabled');
        }
    });
}

// Отключение редактирования формы
function disableForm(form) {
    const inputs = form.querySelectorAll('input, select, textarea');
    const buttons = form.querySelectorAll('button[type="button"]');
    
    inputs.forEach(input => {
        input.setAttribute('disabled', 'disabled');
    });
    
    buttons.forEach(button => {
        button.setAttribute('disabled', 'disabled');
    });
}

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
                                 <form class="contact-form" action="/employees/{{ $user->id }}/update-contact" method="post" data-contact-type="${type}">
                     @csrf
                     <input type="hidden" name="type" value="${type}">
                     <input type="hidden" name="is_primary" value="0">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">${isPhone ? 'Телефон' : 'Email'}</label>
                            <input type="${inputType}" name="${inputName}" class="w-full border border-gray-300 rounded-md px-3 py-2 ${inputClass}" placeholder="${placeholder}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
                            <input type="text" name="comment" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Комментарий">
                        </div>
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
            showToast('У вас уже есть дополнительный телефон. Вы можете удалить или отредактировать его.', 'error');
            return;
        }
        
        // Удаляем пустое состояние, если оно есть
        const emptyStates = additionalContacts.querySelectorAll('.text-center.text-gray-600');
        emptyStates.forEach(state => state.remove());
        
        // Закрываем все открытые редакторы
        closeAllContactEditors();
        
        // Добавляем новый элемент в начало (телефон всегда сверху)
        const newContactHtml = createNewContactItem('phone');
        additionalContacts.insertAdjacentHTML('afterbegin', newContactHtml);
        
        // Настраиваем обработчики для нового элемента
        const newContact = additionalContacts.querySelector('[data-contact-type="phone"]');
        setupContactFormHandlers(newContact);
    });
}

if (addEmailBtn) {
    addEmailBtn.addEventListener('click', () => {
        // Проверяем, есть ли уже дополнительный email
        const existingEmail = additionalContacts.querySelector('[data-contact-type="email"]');
        if (existingEmail) {
            // Показываем уведомление
            showToast('У вас уже есть дополнительный email. Вы можете удалить или отредактировать его.', 'error');
            return;
        }
        
        // Удаляем пустое состояние, если оно есть
        const emptyStates = additionalContacts.querySelectorAll('.text-center.text-gray-600');
        emptyStates.forEach(state => state.remove());
        
        // Закрываем все открытые редакторы
        closeAllContactEditors();
        
        // Добавляем новый элемент в конец (email всегда снизу)
        const newContactHtml = createNewContactItem('email');
        additionalContacts.insertAdjacentHTML('beforeend', newContactHtml);
        
        // Настраиваем обработчики для нового элемента
        const newContact = additionalContacts.querySelector('[data-contact-type="email"]:last-child');
        setupContactFormHandlers(newContact);
    });
}

// Функции для работы с контактами
function closeAllContactEditors() {
    const openEditors = document.querySelectorAll('.contact-edit:not(.hidden)');
    openEditors.forEach(editor => {
        editor.classList.add('hidden');
    });
}

function setupContactFormHandlers(contactItem) {
    const form = contactItem.querySelector('.contact-form');
    const cancelBtn = contactItem.querySelector('.contact-cancel-btn');
    const editBtn = contactItem.querySelector('.contact-edit-btn');
    const deleteBtn = contactItem.querySelector('.contact-delete-btn');
    
         if (cancelBtn) {
         cancelBtn.addEventListener('click', function() {
             // Если это новый контакт (без data-id), удаляем его
             if (!contactItem.hasAttribute('data-id')) {
                 contactItem.remove();
             } else {
                 // Если это существующий контакт, просто скрываем редактор
                 const edit = contactItem.querySelector('.contact-edit');
                 edit.classList.add('hidden');
             }
         });
     }
    
    // Добавляем обработчик для кнопки редактирования
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            const view = contactItem.querySelector('.contact-view');
            const edit = contactItem.querySelector('.contact-edit');
            
            // Закрываем все другие редакторы
            closeAllContactEditors();
            
            // Показываем редактор, но НЕ скрываем view (чтобы карандашик остался видимым)
            edit.classList.remove('hidden');
        });
    }
    
    // Добавляем обработчик для кнопки удаления
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            const contactId = contactItem.getAttribute('data-id');
            
            // Сохраняем данные о контакте для удаления
            contactToDelete = {
                element: contactItem,
                id: contactId
            };
            
            // Показываем модалку подтверждения
            const contactDeleteModal = document.getElementById('deleteContactModal');
            if (contactDeleteModal) {
                contactDeleteModal.classList.remove('hidden');
            }
        });
    }
    
    if (form) {
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
                         .then(response => {
                 if (!response.ok) {
                     return response.json().then(data => {
                         throw new Error(data.message || `HTTP error! status: ${response.status}`);
                     });
                 }
                 return response.json();
             })
             .then(data => {
                 if (data.success) {
                     showToast('Контакт добавлен');
                     // Обновляем отображение без перезагрузки страницы
                     const contactItem = this.closest('.contact-item');
                     const view = contactItem.querySelector('.contact-view');
                     const edit = contactItem.querySelector('.contact-edit');
                     
                                           // Создаем новый элемент отображения
                      const contactType = this.getAttribute('data-contact-type');
                      const isPhone = contactType === 'phone';
                      const icon = isPhone ? '📱' : '✉️';
                      
                      const newViewHtml = `
                          <div class="contact-view flex items-start justify-between gap-3">
                              <div class="flex items-center gap-3">
                                  <div class="text-xl">${icon}</div>
                                  <div>
                                      <div class="font-semibold text-gray-900">${formData.get('value')}</div>
                                      ${formData.get('comment') ? `<div class="text-sm text-gray-500">${formData.get('comment')}</div>` : ''}
                                  </div>
                              </div>
                              <div class="flex items-center gap-2">
                                  <button type="button" class="contact-edit-btn text-blue-600 hover:text-blue-700" title="Редактировать">✏️</button>
                                  <button type="button" class="contact-delete-btn text-red-600 hover:text-red-700" title="Удалить">🗑</button>
                              </div>
                          </div>
                      `;
                      
                      // Создаем новую форму редактирования с правильными значениями
                      const newEditHtml = `
                          <div class="contact-edit hidden mt-3">
                              <form class="contact-form" action="/employees/{{ $user->id }}/update-contact" method="post" data-contact-type="${contactType}">
                                  @csrf
                                  <input type="hidden" name="contact_id" value="${data.contact_id}">
                                  <input type="hidden" name="type" value="${contactType}">
                                  <input type="hidden" name="is_primary" value="0">
                                  <div class="space-y-3">
                                      <div>
                                          <label class="block text-sm font-medium text-gray-700 mb-1">${isPhone ? 'Телефон' : 'Email'}</label>
                                          <input type="${isPhone ? 'tel' : 'email'}" name="value" value="${formData.get('value')}" class="w-full border border-gray-300 rounded-md px-3 py-2 ${isPhone ? 'phone-mask' : ''}" placeholder="${isPhone ? 'Введите номер телефона' : 'Введите email'}" required>
                                      </div>
                                      <div>
                                          <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
                                          <input type="text" name="comment" value="${formData.get('comment') || ''}" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Комментарий">
                                      </div>
                                  </div>
                                  <div class="mt-3 flex justify-end gap-2">
                                      <button type="button" class="contact-cancel-btn px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm">Отмена</button>
                                      <button type="submit" class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 text-sm">Сохранить</button>
                                  </div>
                              </form>
                          </div>
                      `;
                      
                      // Заменяем содержимое
                      contactItem.innerHTML = newViewHtml + newEditHtml;
                     
                     // Добавляем data-id к элементу (если есть в ответе)
                     if (data.contact_id) {
                         contactItem.setAttribute('data-id', data.contact_id);
                     }
                     
                     // Настраиваем обработчики для нового элемента
                     setupContactFormHandlers(contactItem);
                     
                     // Скрываем редактор
                     contactItem.querySelector('.contact-edit').classList.add('hidden');
                 } else {
                     throw new Error(data.message || 'Ошибка сохранения');
                 }
             })
            .catch(error => {
                console.error('Ошибка при добавлении контакта:', error);
                showToast(error.message || 'Ошибка при добавлении контакта', 'error');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
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
                   .then(response => {
              if (!response.ok) {
                  return response.json().then(data => {
                      throw new Error(data.message || `HTTP error! status: ${response.status}`);
                  });
              }
              return response.json();
          })
          .then(data => {
              if (data.success) {
                  // Обновляем отображаемый email
                  const emailSpan = loginDisplay.querySelector('span');
                  emailSpan.textContent = data.email;
                  
                                     // Синхронизация: обновляем основной email в контактах
                   const primaryEmailDiv = document.querySelector('[data-type="primary"][data-contact-type="email"] .font-semibold');
                   if (primaryEmailDiv) {
                       primaryEmailDiv.textContent = data.email;
                   }
                   
                   // Синхронизация: обновляем поле ввода в форме редактирования основного email
                   const primaryEmailInput = document.querySelector('[data-type="primary"][data-contact-type="email"] input[name="email"]');
                   if (primaryEmailInput) {
                       primaryEmailInput.value = data.email;
                   }
                  
                  // Скрываем форму редактирования
                  loginDisplay.classList.remove('hidden');
                  loginEdit.classList.add('hidden');
                  
                  submitBtn.textContent = 'Сохранено!';
                  setTimeout(() => {
                      submitBtn.textContent = originalText;
                      submitBtn.disabled = false;
                  }, 2000);
                  
                  showToast('Логин обновлен');
              } else {
                  throw new Error(data.message || 'Ошибка сохранения');
              }
          })
         .catch(error => {
             console.error('Ошибка при обновлении логина:', error);
             showToast(error.message || 'Ошибка при обновлении логина', 'error');
             submitBtn.textContent = originalText;
             submitBtn.disabled = false;
         });
     });
 }

// Валидация email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Валидация телефона
function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
    return phoneRegex.test(phone);
}



// Открытие модалки смены пароля
function openChangePasswordModal() {
    openModal('changePasswordModal');
}

// Функция переключения видимости пароля
function togglePasswordVisibility(inputId) {
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

// Обработчик формы основного раздела
document.getElementById('main-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/employees/{{ $user->id }}/update-main', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('Сохранено');
            hasUnsavedChanges = false;
        } else {
            showToast(data.message || 'Ошибка', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка при сохранении:', error);
        showToast('Ошибка при сохранении: ' + error.message, 'error');
    });
});

// Обработчик формы смены пароля
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (newPassword !== confirmPassword) {
        showToast('Пароли не совпадают', 'error');
        return;
    }
    
    // Валидация пароля
    if (newPassword.length < 8) {
        showToast('Пароль должен содержать минимум 8 символов', 'error');
        return;
    }
    
    if (!/\d/.test(newPassword) || !/[a-zA-Z]/.test(newPassword)) {
        showToast('Пароль должен содержать буквы и цифры', 'error');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('/employees/{{ $user->id }}/change-password', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Пароль обновлён');
            closeModal('changePasswordModal');
            this.reset();
        } else {
            showToast(data.message || 'Ошибка при смене пароля', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка при смене пароля:', error);
        showToast('Ошибка при смене пароля', 'error');
    });
});

// Отслеживание изменений в формах
document.addEventListener('input', function() {
    if (isEditing) {
        hasUnsavedChanges = true;
    }
});

// Предупреждение о несохраненных изменениях
window.addEventListener('beforeunload', function(e) {
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// Обработчики для контактов
document.addEventListener('DOMContentLoaded', function() {
         // Обработчики для кнопок редактирования контактов
     document.querySelectorAll('.contact-edit-btn').forEach(btn => {
         btn.addEventListener('click', function() {
             const contactItem = this.closest('.contact-item');
             const view = contactItem.querySelector('.contact-view');
             const edit = contactItem.querySelector('.contact-edit');
             
             // Закрываем все другие редакторы
             closeAllContactEditors();
             
             // Показываем редактор, но НЕ скрываем view (чтобы карандашик остался видимым)
             edit.classList.remove('hidden');
         });
     });
    
         // Обработчики для кнопок отмены
     document.querySelectorAll('.contact-cancel-btn').forEach(btn => {
         btn.addEventListener('click', function() {
             const contactItem = this.closest('.contact-item');
             const edit = contactItem.querySelector('.contact-edit');
             
             // Скрываем редактор
             edit.classList.add('hidden');
         });
     });
    
         // Обработчики для кнопок удаления
     document.querySelectorAll('.contact-delete-btn').forEach(btn => {
         btn.addEventListener('click', function() {
             const contactItem = this.closest('.contact-item');
             const contactId = contactItem.getAttribute('data-id');
             
             // Сохраняем данные о контакте для удаления
             contactToDelete = {
                 element: contactItem,
                 id: contactId
             };
             
             // Показываем модалку подтверждения
             const contactDeleteModal = document.getElementById('deleteContactModal');
             if (contactDeleteModal) {
                 contactDeleteModal.classList.remove('hidden');
             }
         });
     });
    
         // Обработчики для форм контактов
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
             const isPrimary = contactItem.getAttribute('data-type') === 'primary';
             
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
                      return response.json().then(data => {
                          throw new Error(data.message || `HTTP error! status: ${response.status}`);
                      });
                  }
                  return response.json();
              })
                             .then(data => {
                   console.log('Ответ сервера:', data); // Добавляем логирование
                   if (data.success) {
                       // Обновляем отображаемые данные
                       const valueDiv = view.querySelector('.font-semibold');
                       
                       if (contactType === 'phone') {
                           valueDiv.textContent = data.phone || data.value || 'Не указан';
                       } else {
                           valueDiv.textContent = data.email || data.value || 'Не указан';
                           
                           // Синхронизация: если это основной email, обновляем логин
                           if (isPrimary) {
                               const loginSpan = document.querySelector('#loginDisplay span');
                               if (loginSpan) {
                                   loginSpan.textContent = data.email || data.value;
                               }
                           }
                       }
                      
                                             // Обновляем комментарий
                       const commentDiv = view.querySelector('.text-sm.text-gray-500');
                       const commentValue = data.comment || formData.get('comment');
                       
                       if (commentValue && commentValue.trim() !== '') {
                           if (commentDiv) {
                               commentDiv.textContent = commentValue;
                           } else {
                               // Создаем новый элемент комментария
                               const newCommentDiv = document.createElement('div');
                               newCommentDiv.className = 'text-sm text-gray-500';
                               newCommentDiv.textContent = commentValue;
                               valueDiv.parentNode.appendChild(newCommentDiv);
                           }
                       } else {
                           // Удаляем комментарий, если он пустой
                           if (commentDiv) {
                               commentDiv.remove();
                           }
                       }
                       
                                              // Скрываем редактор
                       edit.classList.add('hidden');
                      
                      // Показываем уведомление об успешном сохранении
                      showToast('Контакт успешно сохранен', 'success');
                      
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
                  console.error('Ошибка при обновлении контакта:', error);
                  console.error('Полный объект ошибки:', error);
                  showToast(error.message || 'Ошибка при обновлении контакта', 'error');
                  submitBtn.textContent = originalText;
                  submitBtn.disabled = false;
              });
         });
     });
});

 // Обработчики для модалки удаления контакта
 document.addEventListener('DOMContentLoaded', function() {
     const contactDeleteModal = document.getElementById('deleteContactModal');
     
     // Закрытие модального окна при клике на фон или кнопку отмены
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
     
     // Кнопка подтверждения удаления
     const confirmDeleteBtn = document.getElementById('confirmDeleteContact');
     if (confirmDeleteBtn) {
         confirmDeleteBtn.addEventListener('click', function() {
             if (!contactToDelete) {
                 contactDeleteModal.classList.add('hidden');
                 return;
             }
             
             // Блокируем кнопку на время запроса
             this.disabled = true;
             this.textContent = 'Удаление...';
             
             // Выполняем удаление
             fetch(`/employees/{{ $user->id }}/delete-contact`, {
                 method: 'POST',
                 headers: {
                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                     'Content-Type': 'application/json'
                 },
                 body: JSON.stringify({ contact_id: contactToDelete.id })
             })
             .then(response => response.json())
             .then(data => {
                 if (data.success) {
                     showToast('Контакт удален');
                     contactToDelete.element.remove();
                     
                     // Проверяем, нужно ли показать пустое состояние
                     const additionalContacts = document.getElementById('additionalContacts');
                     const remainingContacts = additionalContacts.querySelectorAll('.contact-item');
                     
                     if (remainingContacts.length === 0) {
                         // Показываем пустое состояние
                         const emptyStateHtml = `
                             <div class="text-center text-gray-600 py-8">
                                 <div class="text-4xl mb-2">📭</div>
                                 <div class="text-sm">Пока ничего нет. Добавьте данные.</div>
                             </div>
                         `;
                         additionalContacts.innerHTML = emptyStateHtml;
                     }
                 } else {
                     showToast(data.message || 'Ошибка при удалении контакта', 'error');
                 }
             })
             .catch(error => {
                 console.error('Ошибка при удалении контакта:', error);
                 showToast('Ошибка при удалении контакта', 'error');
             })
             .finally(() => {
                 // Восстанавливаем кнопку
                 this.disabled = false;
                 this.textContent = 'Удалить';
                 
                 // Закрываем модалку
                 contactDeleteModal.classList.add('hidden');
                 contactToDelete = null; // Очищаем данные
             });
         });
     }
 });

   // Утилиты
  function showToast(message, type = 'success') {
     const toast = document.getElementById('toast');
     const toastMessage = document.getElementById('toast-message');
     
     // Меняем цвет в зависимости от типа
     if (type === 'error') {
         toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50';
     } else {
         toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50';
     }
     
     toastMessage.textContent = message;
     
     // Показываем toast
     toast.classList.remove('hidden');
     toast.classList.remove('translate-x-full');

     // Автоматически скрываем через 3 секунды
     setTimeout(() => {
         toast.classList.add('translate-x-full');
         // Полностью скрываем после анимации
         setTimeout(() => {
             toast.classList.add('hidden');
         }, 300);
     }, 3000);
 }
</script>
</body>
</html>
