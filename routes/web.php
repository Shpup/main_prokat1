<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\CounterpartyController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

require __DIR__.'/auth.php';

Route::middleware(['auth', 'subscription'])->group(function () {
    // Профиль
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Дашборд
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');

    // Проекты
    Route::get('/projects/table', [ProjectController::class, 'table'])->name('projects.table');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.status.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/{project}/equipment', [ProjectController::class, 'addEquipment'])->name('projects.addEquipment');
    Route::post('/projects/{project}/equipment/{equipment}', [ProjectController::class, 'attachEquipment'])->name('projects.equipment.attach');
    Route::delete('/projects/{project}/equipment/{equipment}', [ProjectController::class, 'detachEquipment'])->name('projects.equipment.detach');
    Route::post('/projects/{project}/staff/{user}', [ProjectController::class, 'attachStaff'])->name('projects.staff.attach');
    Route::get('/projects/{project}/staff/{user}/summary', [ProjectController::class, 'summary'])->name('projects.staff.summary');
    Route::delete('/projects/{project}/staff/{user}', [ProjectController::class, 'detachStaff'])->name('projects.staff.detach');
    Route::get('/projects/{project}/equipment-list', [ProjectController::class, 'equipmentList'])->name('projects.equipmentList');

    // Оборудование
    Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment');
    Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
    Route::get('/equipment/{equipment}/edit', [EquipmentController::class, 'edit'])->name('equipment.edit');
    Route::put('/equipment/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
    Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');

    // Менеджеры
    Route::get('/managers', [ManagerController::class, 'index'])->name('managers');
    Route::get('/managers/create', [ManagerController::class, 'create'])->name('managers.create');
    Route::post('/managers', [ManagerController::class, 'store'])->name('managers.store');

    // Категории
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Пользователи
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.updatePermissions');

    // Персонал
    Route::get('/personnel', [PersonnelController::class, 'index'])->name('personnel');
    Route::post('/personnel/assign', [PersonnelController::class, 'assign'])->name('personnel.assign');
    Route::post('/personnel/non-working', [PersonnelController::class, 'addNonWorkingDay'])->name('personnel.non-working');
    Route::post('/personnel/clear', [PersonnelController::class, 'clearInterval'])->name('personnel.clear');
    Route::get('/personnel/time-slots', [PersonnelController::class, 'getTimeSlots'])->name('personnel.time-slots');
    Route::get('/personnel/data', [PersonnelController::class, 'getData'])->name('personnel.data');

    // Комментарии
    Route::get('/comments', [PersonnelController::class, 'listComments'])->name('comments.index');
    Route::post('/comments', [PersonnelController::class, 'storeComment'])->name('comments.store');
    Route::delete('/comments', [PersonnelController::class, 'deleteComments'])->name('comments.delete');

    // Клиенты
    Route::get('/clients', [ClientController::class, 'index'])->name('clients');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    // Компании
    Route::resource('companies', CompanyController::class);
    Route::post('companies/{company}/legal', [CompanyController::class, 'updateLegal'])->name('companies.legal.update');
    Route::post('companies/{company}/tax', [CompanyController::class, 'updateTax'])->name('companies.tax.update');
    Route::post('companies/{company}/basic', [CompanyController::class, 'updateBasic'])->name('companies.basic.update');

    // Транспорт
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

    // Контрагенты
    Route::resource('counterparties', CounterpartyController::class);
    Route::post('counterparties/{counterparty}/basic', [CounterpartyController::class, 'updateBasic'])->name('counterparties.basic.update');
    Route::post('counterparties/{counterparty}/legal', [CounterpartyController::class, 'updateLegal'])->name('counterparties.legal.update');

    // Путевые листы
    Route::get('/trip-sheets', [VehicleController::class, 'index'])->name('tripSheets.index');
    Route::post('/trip-sheets', [VehicleController::class, 'storeTripSheet'])->name('tripSheets.store');
    Route::put('/trip-sheets/{tripSheet}', [VehicleController::class, 'updateTripSheet'])->name('tripSheets.update');
    Route::delete('/trip-sheets/{tripSheet}', [VehicleController::class, 'destroyTripSheet'])->name('trip-sheets.destroy');

    // Сайты
    Route::resource('sites', SiteController::class);
    Route::get('/sites', [SiteController::class, 'index'])->name('sites');
});

Route::get('/subscription/payment', [SubscriptionController::class, 'showPaymentPage'])->name('subscription.payment');
Route::post('/subscription/activate', [SubscriptionController::class, 'activate'])->name('subscription.activate');
