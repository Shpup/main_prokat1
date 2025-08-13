<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Компания: {{ $company->name }}</title>
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
<div class="bg-white shadow rounded-lg" x-data="companyDetails()" x-init="init()">
    @include('layouts.navigation')

    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <h1 class="text-2xl font-semibold">{{ $company->name }}</h1>
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
            <p class="text-gray-600">Здесь будет отображаться общая информация о компании (заглушка).</p>
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
                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                    @click="toggleSettingsBlock('tax')"
                >
                    Налог
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
                                x-model="companyForm.name"
                                required
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                        <div class="form-row">
                            <label class="block text-sm font-medium text-gray-700">Комментарий</label>
                            <textarea
                                name="comment"
                                x-model="companyForm.comment"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            ></textarea>
                        </div>
                        <div class="form-row">
                            <label class="block text-sm font-medium text-gray-700">Использовать по умолчанию</label>
                            <input
                                type="checkbox"
                                name="is_default"
                                x-model="companyForm.is_default"
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
                            <label class="block text-sm font-medium text-gray-700">Тип компании</label>
                            <select
                                name="type"
                                x-model="companyForm.type"
                                @change="updateFields()"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="ur">Юр. лицо</option>
                                <option value="ip">ИП</option>
                                <option value="fl">Физ. лицо</option>
                            </select>
                        </div>

                        <!-- Поля для Юр. лица -->
                        <template x-if="companyForm.type === 'ur'">
                            <div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Страна регистрации</label>
                                    <input
                                        type="text"
                                        name="registration_country"
                                        x-model="companyForm.registration_country"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ИНН</label>
                                    <input
                                        type="text"
                                        name="inn"
                                        x-model="companyForm.inn"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование (полное)</label>
                                    <input
                                        type="text"
                                        name="full_name"
                                        x-model="companyForm.full_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование (сокращенное)</label>
                                    <input
                                        type="text"
                                        name="short_name"
                                        x-model="companyForm.short_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Юридический адрес</label>
                                    <input
                                        type="text"
                                        name="legal_address"
                                        x-model="companyForm.legal_address"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Почтовый адрес</label>
                                    <input
                                        type="text"
                                        name="postal_address"
                                        x-model="companyForm.postal_address"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">КПП</label>
                                    <input
                                        type="text"
                                        name="kpp"
                                        x-model="companyForm.kpp"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ОГРН</label>
                                    <input
                                        type="text"
                                        name="ogrn"
                                        x-model="companyForm.ogrn"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ОКПО</label>
                                    <input
                                        type="text"
                                        name="okpo"
                                        x-model="companyForm.okpo"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">БИК</label>
                                    <input
                                        type="text"
                                        name="bik"
                                        x-model="companyForm.bik"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование банка</label>
                                    <input
                                        type="text"
                                        name="bank_name"
                                        x-model="companyForm.bank_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Корреспондентский счет</label>
                                    <input
                                        type="text"
                                        name="correspondent_account"
                                        x-model="companyForm.correspondent_account"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Расчетный счет</label>
                                    <input
                                        type="text"
                                        name="checking_account"
                                        x-model="companyForm.checking_account"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Телефон</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        x-model="companyForm.phone"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                                    <input
                                        type="email"
                                        name="email"
                                        x-model="companyForm.email"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                            </div>
                        </template>

                        <!-- Поля для ИП -->
                        <template x-if="companyForm.type === 'ip'">
                            <div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ИНН</label>
                                    <input
                                        type="text"
                                        name="inn"
                                        x-model="companyForm.inn"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование (полное)</label>
                                    <input
                                        type="text"
                                        name="full_name"
                                        x-model="companyForm.full_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование (сокращенное)</label>
                                    <input
                                        type="text"
                                        name="short_name"
                                        x-model="companyForm.short_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Адрес регистрации</label>
                                    <input
                                        type="text"
                                        name="registration_address"
                                        x-model="companyForm.registration_address"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Почтовый адрес</label>
                                    <input
                                        type="text"
                                        name="postal_address"
                                        x-model="companyForm.postal_address"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ОКПО</label>
                                    <input
                                        type="text"
                                        name="okpo"
                                        x-model="companyForm.okpo"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ОГРНИП</label>
                                    <input
                                        type="text"
                                        name="ogrnip"
                                        x-model="companyForm.ogrnip"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Номер свидетельства</label>
                                    <input
                                        type="text"
                                        name="certificate_number"
                                        x-model="companyForm.certificate_number"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Дата свидетельства</label>
                                    <input
                                        type="date"
                                        name="certificate_date"
                                        x-model="companyForm.certificate_date"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">БИК</label>
                                    <input
                                        type="text"
                                        name="bik"
                                        x-model="companyForm.bik"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование банка</label>
                                    <input
                                        type="text"
                                        name="bank_name"
                                        x-model="companyForm.bank_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Корреспондентский счет</label>
                                    <input
                                        type="text"
                                        name="correspondent_account"
                                        x-model="companyForm.correspondent_account"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Расчетный счет</label>
                                    <input
                                        type="text"
                                        name="checking_account"
                                        x-model="companyForm.checking_account"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Номер банковской карты</label>
                                    <input
                                        type="text"
                                        name="card_number"
                                        x-model="companyForm.card_number"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Телефон</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        x-model="companyForm.phone"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                                    <input
                                        type="email"
                                        name="email"
                                        x-model="companyForm.email"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                            </div>
                        </template>

                        <!-- Поля для Физ. лица -->
                        <template x-if="companyForm.type === 'fl'">
                            <div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ФИО</label>
                                    <input
                                        type="text"
                                        name="full_name"
                                        x-model="companyForm.full_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">ИНН</label>
                                    <input
                                        type="text"
                                        name="inn"
                                        x-model="companyForm.inn"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">СНИЛС</label>
                                    <input
                                        type="text"
                                        name="snils"
                                        x-model="companyForm.snils"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Паспортные данные</label>
                                    <input
                                        type="text"
                                        name="passport_data"
                                        x-model="companyForm.passport_data"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Адрес регистрации</label>
                                    <input
                                        type="text"
                                        name="registration_address"
                                        x-model="companyForm.registration_address"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">БИК</label>
                                    <input
                                        type="text"
                                        name="bik"
                                        x-model="companyForm.bik"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Наименование банка</label>
                                    <input
                                        type="text"
                                        name="bank_name"
                                        x-model="companyForm.bank_name"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Корреспондентский счет</label>
                                    <input
                                        type="text"
                                        name="correspondent_account"
                                        x-model="companyForm.correspondent_account"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Расчетный счет</label>
                                    <input
                                        type="text"
                                        name="checking_account"
                                        x-model="companyForm.checking_account"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Номер банковской карты</label>
                                    <input
                                        type="text"
                                        name="card_number"
                                        x-model="companyForm.card_number"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">Телефон</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        x-model="companyForm.phone"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                <div class="form-row">
                                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                                    <input
                                        type="email"
                                        name="email"
                                        x-model="companyForm.email"
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

                <!-- Блок: Налог -->
                <div class="settings-block p-4 bg-gray-50 rounded-lg max-w-lg" id="taxSettings" x-show="activeSettingsBlocks.includes('tax')">
                    <form @submit.prevent="submitTaxForm" enctype="multipart/form-data">
                        <div class="form-row">
                            <label class="block text-sm font-medium text-gray-700">Ставка, %</label>
                            <input
                                type="number"
                                name="tax_rate"
                                x-model.number="companyForm.tax_rate"
                                step="0.01"
                                min="0"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                        <div class="form-row">
                            <label class="block text-sm font-medium text-gray-700">Учет в смете</label>
                            <select
                                name="accounting_method"
                                x-model="companyForm.accounting_method"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Выберите метод</option>
                                <option value="osn_inclusive">ОСН, налог в стоимости</option>
                                <option value="osn_exclusive">ОСН, налог сверху</option>
                                <option value="usn_inclusive">УСН, налог в стоимости</option>
                                <option value="usn_exclusive">УСН, налог сверху</option>
                            </select>
                        </div>
                        <div class="flex justify-end space-x-2 mt-4">
                            <button
                                type="button"
                                @click="toggleSettingsBlock('tax')"
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
        Alpine.data('companyDetails', () => ({
            activeTab: 'general',
            activeSettingsBlocks: [],
            submitDisabled: false,
            companyForm: {
                name: @json($company->name ?? ''),
                comment: @json($company->comment ?? ''),
                is_default: @json($company->is_default ?? false),
                type: @json($company->type ?? 'ur'),
                registration_country: @json($company->registration_country ?? 'Российская Федерация'),
                inn: @json($company->inn ?? ''),
                full_name: @json($company->full_name ?? ''),
                short_name: @json($company->short_name ?? ''),
                legal_address: @json($company->legal_address ?? ''),
                postal_address: @json($company->postal_address ?? ''),
                kpp: @json($company->kpp ?? ''),
                ogrn: @json($company->ogrn ?? ''),
                okpo: @json($company->okpo ?? ''),
                bik: @json($company->bik ?? ''),
                bank_name: @json($company->bank_name ?? ''),
                correspondent_account: @json($company->correspondent_account ?? ''),
                checking_account: @json($company->checking_account ?? ''),
                phone: @json($company->phone ?? ''),
                email: @json($company->email ?? ''),
                registration_address: @json($company->registration_address ?? ''),
                ogrnip: @json($company->ogrnip ?? ''),
                certificate_number: @json($company->certificate_number ?? ''),
                certificate_date: @json($company->certificate_date ?? ''),
                card_number: @json($company->card_number ?? ''),
                snils: @json($company->snils ?? ''),
                passport_data: @json($company->passport_data ?? ''),
                tax_rate: @json($company->tax_rate ?? null),
                accounting_method: @json($company->accounting_method ?? ''),
            },
            init() {
                console.log('Инициализация companyDetails, companyForm:', this.companyForm);
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
                console.log('Type changed to:', this.companyForm.type);
            },
            async submitBasicForm(event) {
                event.preventDefault();
                this.submitDisabled = true;
                const form = event.target;
                const formData = new FormData(form);
                formData.set('name', this.companyForm.name);
                formData.set('comment', this.companyForm.comment);
                formData.set('is_default', this.companyForm.is_default ? '1' : '0');
                console.log('Submitting basicForm:', Object.fromEntries(formData));
                try {
                    const response = await fetch('/companies/{{ $company->id }}/basic', {
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
                formData.set('type', this.companyForm.type);
                console.log('Submitting legalForm:', Object.fromEntries(formData));
                try {
                    const response = await fetch('/companies/{{ $company->id }}/legal', {
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
            async submitTaxForm(event) {
                event.preventDefault();
                this.submitDisabled = true;
                const form = event.target;
                const formData = new FormData(form);
                formData.set('tax_rate', this.companyForm.tax_rate);
                formData.set('accounting_method', this.companyForm.accounting_method);
                console.log('Submitting taxForm:', Object.fromEntries(formData));
                try {
                    const response = await fetch('/companies/{{ $company->id }}/tax', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    if (response.ok) {
                        this.showToast('Налоговая информация сохранена');
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
