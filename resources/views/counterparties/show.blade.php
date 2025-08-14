<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Контрагент: {{ $counterparty->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .settings-block { display: none; min-width: 400px; }
        .settings-block.active { display: block; }
        .form-row { display: flex; align-items: center; margin-bottom: 1rem; }
        .form-row label { width: 200px; flex-shrink: 0; }
        .form-row input, .form-row select, .form-row textarea { flex-grow: 1; }
        .settings-container { display: flex; flex-direction: row; gap: 1rem; overflow-x: auto; }
    </style>
</head>
<body class="bg-gray-100">
<div class="bg-white shadow rounded-lg" x-data="counterpartyDetails()" x-init="init()">
    @include('layouts.navigation')

    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <h1 class="text-2xl font-semibold">{{ $counterparty->name }}</h1>
        <div class="flex space-x-2 mt-4">
            <button
                class="tab-button px-4 py-2 text-sm font-medium rounded-md"
                :class="{ 'bg-blue-500 text-white': activeTab === 'general', 'bg-gray-200 text-gray-700': activeTab !== 'general' }"
                @click="openTab('general')"
            >
                Общая информация
            </button>
            <button
                class="tab-button px-4 py-2 text-sm font-medium rounded-md"
                :class="{ 'bg-blue-500 text-white': activeTab === 'settings', 'bg-gray-200 text-gray-700': activeTab !== 'settings' }"
                @click="openTab('settings')"
            >
                Настройки
            </button>
        </div>
    </div>

    <div class="p-6">
        <!-- Вкладка: Общая информация -->
        <div class="tab-content" id="generalTab" x-show="activeTab === 'general'">
            <p class="text-gray-600">Здесь будет отображаться общая информация о контрагенте (заглушка).</p>
        </div>

        <!-- Вкладка: Настройки -->
        <div class="tab-content" id="settingsTab" x-show="activeTab === 'settings'">
            <div class="mb-4 flex space-x-2">
                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                    @click="toggleSettingsBlock('basic')"
                >
                    Основная информация
                </button>
                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                    @click="toggleSettingsBlock('legal')"
                >
                    Юридическая информация
                </button>
            </div>

            <div class="settings-container">
                <!-- Блок: Основная информация -->
                <div class="settings-block p-4 bg-gray-50 rounded-lg max-w-lg" id="basicSettings" x-show="activeSettingsBlocks.includes('basic')">
                    <form @submit.prevent="submitBasicForm" enctype="multipart/form-data">
                        <div class="form-row">
                            <label class="block text-sm font-medium text-gray-700">Наименование</label>
                            <input
                                type="text"
                                name="name"
                                x-model="counterpartyForm.name"
                                required
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                        <div class="form-row">
                            <label class="block text-sm font-medium text-gray-700">Менеджер</label>
                            <select
                                name="manager_id"
                                x-model="counterpartyForm.manager_id"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Выберите менеджера</option>
                                @foreach (\App\Models\User::where('role', 'manager')->get() as $manager)
                                    <option :value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-row">
                            <label class="block text-sm font-medium text-gray-700">Код</label>
                            <input
                                type="text"
                                name="code"
                                x-model="counterpartyForm.code"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                        <div class="form-row">
                            <label class="block text-sm font-medium text-gray-700">Статус</label>
                            <select
                                name="status"
                                x-model="counterpartyForm.status"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Выберите статус</option>
                                <option value="new">Новый</option>
                                <option value="verified">Проверенный</option>
                                <option value="dangerous">Опасный</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <label class="block text-sm font-medium text-gray-700">Фактический адрес</label>
                            <input
                                type="text"
                                name="actual_address"
                                x-model="counterpartyForm.actual_address"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                        <div class="form-row">
                            <label class="block text-sm font-medium text-gray-700">Комментарий</label>
                            <textarea
                                name="comment"
                                x-model="counterpartyForm.comment"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            ></textarea>
                        </div>
                        <div class="form-row">
                            <label class="block text-sm font-medium text-gray-700">Доступен для субаренды</label>
                            <input
                                type="checkbox"
                                name="is_available_for_sublease"
                                x-model="counterpartyForm.is_available_for_sublease"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                        </div>
                        <div class="flex justify-end space-x-2 mt-4">
                            <button
                                type="button"
                                @click="toggleSettingsBlock('basic')"
                                class="px-4 py-2 border rounded hover:bg-gray-100"
                            >
                                Закрыть
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                :disabled="submitDisabled"
                            >
                                Сохранить
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Блок: Юридическая информация -->
                <div class="settings-block p-4 bg-gray-50 rounded-lg max-w-lg" id="legalSettings" x-show="activeSettingsBlocks.includes('legal')">
                    <form @submit.prevent="submitLegalForm" enctype="multipart/form-data">
                        <div class="form-row">
                            <label class="block text-sm font-medium text-gray-700">Тип контрагента</label>
                            <select
                                name="type"
                                x-model="counterpartyForm.type"
                                @change="updateFields()"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="ur">Юр. лицо</option>
                                <option value="ip">ИП</option>
                                <option value="fl">Физ. лицо</option>
                            </select>
                        </div>

                        <!-- Поля для Юр. лица -->
                        <template x-if="counterpartyForm.type === 'ur'">
                            <div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Страна регистрации</label>
                                    <input
                                        type="text"
                                        name="registration_country"
                                        x-model="counterpartyForm.registration_country"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ИНН</label>
                                    <input
                                        type="text"
                                        name="inn"
                                        x-model="counterpartyForm.inn"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование (полное)</label>
                                    <input
                                        type="text"
                                        name="full_name"
                                        x-model="counterpartyForm.full_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование (сокращенное)</label>
                                    <input
                                        type="text"
                                        name="short_name"
                                        x-model="counterpartyForm.short_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Юридический адрес</label>
                                    <input
                                        type="text"
                                        name="legal_address"
                                        x-model="counterpartyForm.legal_address"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Почтовый адрес</label>
                                    <input
                                        type="text"
                                        name="postal_address"
                                        x-model="counterpartyForm.postal_address"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">КПП</label>
                                    <input
                                        type="text"
                                        name="kpp"
                                        x-model="counterpartyForm.kpp"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ОГРН</label>
                                    <input
                                        type="text"
                                        name="ogrn"
                                        x-model="counterpartyForm.ogrn"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ОКПО</label>
                                    <input
                                        type="text"
                                        name="okpo"
                                        x-model="counterpartyForm.okpo"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">БИК</label>
                                    <input
                                        type="text"
                                        name="bik"
                                        x-model="counterpartyForm.bik"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование банка</label>
                                    <input
                                        type="text"
                                        name="bank_name"
                                        x-model="counterpartyForm.bank_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Корреспондентский счет</label>
                                    <input
                                        type="text"
                                        name="correspondent_account"
                                        x-model="counterpartyForm.correspondent_account"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Расчетный счет</label>
                                    <input
                                        type="text"
                                        name="checking_account"
                                        x-model="counterpartyForm.checking_account"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Телефон</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        x-model="counterpartyForm.phone"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                                    <input
                                        type="email"
                                        name="email"
                                        x-model="counterpartyForm.email"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                            </div>
                        </template>

                        <!-- Поля для ИП -->
                        <template x-if="counterpartyForm.type === 'ip'">
                            <div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ИНН</label>
                                    <input
                                        type="text"
                                        name="inn"
                                        x-model="counterpartyForm.inn"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование (полное)</label>
                                    <input
                                        type="text"
                                        name="full_name"
                                        x-model="counterpartyForm.full_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование (сокращенное)</label>
                                    <input
                                        type="text"
                                        name="short_name"
                                        x-model="counterpartyForm.short_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Адрес регистрации</label>
                                    <input
                                        type="text"
                                        name="registration_address"
                                        x-model="counterpartyForm.registration_address"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Почтовый адрес</label>
                                    <input
                                        type="text"
                                        name="postal_address"
                                        x-model="counterpartyForm.postal_address"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ОКПО</label>
                                    <input
                                        type="text"
                                        name="okpo"
                                        x-model="counterpartyForm.okpo"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ОГРНИП</label>
                                    <input
                                        type="text"
                                        name="ogrnip"
                                        x-model="counterpartyForm.ogrnip"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Номер свидетельства</label>
                                    <input
                                        type="text"
                                        name="certificate_number"
                                        x-model="counterpartyForm.certificate_number"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Дата свидетельства</label>
                                    <input
                                        type="date"
                                        name="certificate_date"
                                        x-model="counterpartyForm.certificate_date"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">БИК</label>
                                    <input
                                        type="text"
                                        name="bik"
                                        x-model="counterpartyForm.bik"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование банка</label>
                                    <input
                                        type="text"
                                        name="bank_name"
                                        x-model="counterpartyForm.bank_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Корреспондентский счет</label>
                                    <input
                                        type="text"
                                        name="correspondent_account"
                                        x-model="counterpartyForm.correspondent_account"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Расчетный счет</label>
                                    <input
                                        type="text"
                                        name="checking_account"
                                        x-model="counterpartyForm.checking_account"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Номер банковской карты</label>
                                    <input
                                        type="text"
                                        name="card_number"
                                        x-model="counterpartyForm.card_number"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Телефон</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        x-model="counterpartyForm.phone"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                                    <input
                                        type="email"
                                        name="email"
                                        x-model="counterpartyForm.email"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                            </div>
                        </template>

                        <!-- Поля для Физ. лица -->
                        <template x-if="counterpartyForm.type === 'fl'">
                            <div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ФИО</label>
                                    <input
                                        type="text"
                                        name="full_name"
                                        x-model="counterpartyForm.full_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ИНН</label>
                                    <input
                                        type="text"
                                        name="inn"
                                        x-model="counterpartyForm.inn"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">СНИЛС</label>
                                    <input
                                        type="text"
                                        name="snils"
                                        x-model="counterpartyForm.snils"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Паспортные данные</label>
                                    <input
                                        type="text"
                                        name="passport_data"
                                        x-model="counterpartyForm.passport_data"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Адрес регистрации</label>
                                    <input
                                        type="text"
                                        name="registration_address"
                                        x-model="counterpartyForm.registration_address"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">БИК</label>
                                    <input
                                        type="text"
                                        name="bik"
                                        x-model="counterpartyForm.bik"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование банка</label>
                                    <input
                                        type="text"
                                        name="bank_name"
                                        x-model="counterpartyForm.bank_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Корреспондентский счет</label>
                                    <input
                                        type="text"
                                        name="correspondent_account"
                                        x-model="counterpartyForm.correspondent_account"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Расчетный счет</label>
                                    <input
                                        type="text"
                                        name="checking_account"
                                        x-model="counterpartyForm.checking_account"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Номер банковской карты</label>
                                    <input
                                        type="text"
                                        name="card_number"
                                        x-model="counterpartyForm.card_number"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Телефон</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        x-model="counterpartyForm.phone"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                                    <input
                                        type="email"
                                        name="email"
                                        x-model="counterpartyForm.email"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                            </div>
                        </template>

                        <div class="flex justify-end space-x-2 mt-4">
                            <button
                                type="button"
                                @click="toggleSettingsBlock('legal')"
                                class="px-4 py-2 border rounded hover:bg-gray-100"
                            >
                                Закрыть
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                :disabled="submitDisabled"
                            >
                                Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Тост-уведомление -->
    <div
        id="toast"
        x-cloak
        class="fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded shadow-lg transform transition-transform translate-y-full"
    >
        <span id="toastMessage"></span>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('counterpartyDetails', () => ({
            activeTab: 'general',
            activeSettingsBlocks: [],
            submitDisabled: false,
            counterpartyForm: {
                name: @json($counterparty->name ?? ''),
                manager_id: @json($counterparty->manager_id ?? ''),
                code: @json($counterparty->code ?? ''),
                status: @json($counterparty->status ?? ''),
                actual_address: @json($counterparty->actual_address ?? ''),
                comment: @json($counterparty->comment ?? ''),
                is_available_for_sublease: @json($counterparty->is_available_for_sublease ?? false),
                type: @json($counterparty->type ?? 'ur'),
                registration_country: @json($counterparty->registration_country ?? 'Российская Федерация'),
                inn: @json($counterparty->inn ?? ''),
                full_name: @json($counterparty->full_name ?? ''),
                short_name: @json($counterparty->short_name ?? ''),
                legal_address: @json($counterparty->legal_address ?? ''),
                postal_address: @json($counterparty->postal_address ?? ''),
                kpp: @json($counterparty->kpp ?? ''),
                ogrn: @json($counterparty->ogrn ?? ''),
                okpo: @json($counterparty->okpo ?? ''),
                bik: @json($counterparty->bik ?? ''),
                bank_name: @json($counterparty->bank_name ?? ''),
                correspondent_account: @json($counterparty->correspondent_account ?? ''),
                checking_account: @json($counterparty->checking_account ?? ''),
                phone: @json($counterparty->phone ?? ''),
                email: @json($counterparty->email ?? ''),
                registration_address: @json($counterparty->registration_address ?? ''),
                ogrnip: @json($counterparty->ogrnip ?? ''),
                certificate_number: @json($counterparty->certificate_number ?? ''),
                certificate_date: @json($counterparty->certificate_date ?? ''),
                card_number: @json($counterparty->card_number ?? ''),
                snils: @json($counterparty->snils ?? ''),
                passport_data: @json($counterparty->passport_data ?? ''),
            },
            init() {
                console.log('Инициализация counterpartyDetails, counterpartyForm:', this.counterpartyForm);
            },
            openTab(tab) {
                this.activeTab = tab;
                document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
                document.getElementById(tab + 'Tab').classList.add('active');
                document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('bg-blue-500', 'text-white'));
                document.querySelector(`.tab-button[onclick*="${tab}"]`)?.classList.add('bg-blue-500', 'text-white');
            },
            toggleSettingsBlock(block) {
                if (this.activeSettingsBlocks.includes(block)) {
                    this.activeSettingsBlocks = this.activeSettingsBlocks.filter(b => b !== block);
                } else {
                    this.activeSettingsBlocks.push(block);
                }
                document.querySelectorAll('.settings-block').forEach(el => el.classList.remove('active'));
                this.activeSettingsBlocks.forEach(b => {
                    document.getElementById(b + 'Settings').classList.add('active');
                });
            },
            updateFields() {
                console.log('Type changed to:', this.counterpartyForm.type);
            },
            async submitBasicForm(event) {
                event.preventDefault();
                this.submitDisabled = true;
                const form = event.target;
                const formData = new FormData(form);
                formData.set('name', this.counterpartyForm.name);
                formData.set('manager_id', this.counterpartyForm.manager_id);
                formData.set('code', this.counterpartyForm.code);
                formData.set('status', this.counterpartyForm.status);
                formData.set('actual_address', this.counterpartyForm.actual_address);
                formData.set('comment', this.counterpartyForm.comment);
                formData.set('is_available_for_sublease', this.counterpartyForm.is_available_for_sublease ? '1' : '0');
                console.log('Submitting basicForm:', Object.fromEntries(formData));
                try {
                    const response = await fetch('/counterparties/{{ $counterparty->id }}/basic', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    if (response.ok) {
                        this.showToast('Основная информация сохранена');
                    } else {
                        const error = await response.json();
                        console.error('Server error:', error);
                        this.showToast('Ошибка: ' + (error.message || 'Неизвестная ошибка'));
                    }
                } catch (e) {
                    console.error('Error submitting form:', e);
                    this.showToast('Ошибка при сохранении');
                } finally {
                    this.submitDisabled = false;
                }
            },
            async submitLegalForm(event) {
                event.preventDefault();
                this.submitDisabled = true;
                const form = event.target;
                const formData = new FormData(form);
                formData.set('type', this.counterpartyForm.type);
                console.log('Submitting legalForm:', Object.fromEntries(formData));
                try {
                    const response = await fetch('/counterparties/{{ $counterparty->id }}/legal', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    if (response.ok) {
                        this.showToast('Юридическая информация сохранена');
                    } else {
                        const error = await response.json();
                        console.error('Server error:', error);
                        this.showToast('Ошибка: ' + (error.message || 'Неизвестная ошибка'));
                    }
                } catch (e) {
                    console.error('Error submitting form:', e);
                    this.showToast('Ошибка при сохранении');
                } finally {
                    this.submitDisabled = false;
                }
            },
            showToast(message) {
                const toast = document.getElementById('toast');
                const toastMessage = document.getElementById('toastMessage');
                toastMessage.textContent = message;
                toast.classList.remove('translate-y-full');
                setTimeout(() => {
                    toast.classList.add('translate-y-full');
                }, 2000);
            }
        }));
    });
</script>
</body>
</html>
