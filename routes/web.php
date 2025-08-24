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
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/autocomplete', [ProfileController::class, 'autocompleteProjects'])->name('profile.autocomplete');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Профиль: раздел «О себе» (всё в одном контроллере)
    Route::get('/profile/about', [ProfileController::class, 'aboutEdit'])->name('profile.about.edit');
    Route::put('/profile/about/info', [ProfileController::class, 'aboutUpdateInfo'])->name('profile.about.updateInfo');
    Route::put('/profile/about/password', [ProfileController::class, 'aboutUpdatePassword'])->name('profile.about.updatePassword');
    Route::put('/profile/about/login', [ProfileController::class, 'aboutUpdateLogin'])->name('profile.about.updateLogin');
    Route::post('/profile/about/photo', [ProfileController::class, 'aboutUpdatePhoto'])->name('profile.about.updatePhoto');

    // Основные контакты
    Route::put('/profile/primary/email', [ProfileController::class, 'updatePrimaryEmail'])->name('profile.primary.updateEmail');
    Route::put('/profile/primary/phone', [ProfileController::class, 'updatePrimaryPhone'])->name('profile.primary.updatePhone');

    // Контакты
    Route::post('/profile/phones', [ProfileController::class, 'storePhone'])->name('profile.phones.store');
    Route::put('/profile/phones/{phone}', [ProfileController::class, 'updatePhone'])->name('profile.phones.update');
    Route::delete('/profile/phones/{phone}', [ProfileController::class, 'destroyPhone'])->name('profile.phones.destroy');

    Route::post('/profile/emails', [ProfileController::class, 'storeEmail'])->name('profile.emails.store');
    Route::put('/profile/emails/{email}', [ProfileController::class, 'updateEmail'])->name('profile.emails.update');
    Route::delete('/profile/emails/{email}', [ProfileController::class, 'destroyEmail'])->name('profile.emails.destroy');

    // Документы
    Route::post('/profile/documents', [ProfileController::class, 'storeDocument'])->name('profile.documents.store');
    Route::put('/profile/documents/{document}', [ProfileController::class, 'updateDocument'])->name('profile.documents.update');
    Route::delete('/profile/documents/{document}', [ProfileController::class, 'destroyDocument'])->name('profile.documents.destroy');
    Route::delete('/profile/documents/{document}/photo/{photoIndex}', [ProfileController::class, 'destroyDocumentPhoto'])->name('profile.documents.photo.destroy');

    // Дашборд
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');

    // Проекты
    Route::get('/projects/table', [ProjectController::class, 'table'])->name('projects.table');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::get('/projects/catalog', [ProjectController::class, 'getCatalog'])->name('projects.catalog'); // Переставили выше show
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
    Route::post('/projects/{project}/estimates', [ProjectController::class, 'createEstimate'])->name('projects.estimates.create');
    Route::patch('/estimates/{estimate}', [ProjectController::class, 'updateEstimate'])->name('estimates.update');
    Route::delete('/estimates/{estimate}', [ProjectController::class, 'deleteEstimate'])->name('estimates.delete');
    Route::get('/estimates/{estimate}/export', [ProjectController::class, 'exportEstimate'])->name('estimates.export');
    Route::post('/estimates/{estimate}/add-equipment', [ProjectController::class, 'addToEstimate'])->name('estimates.add_equipment');
    Route::post('/estimates/{estimate}/remove-equipment', [ProjectController::class, 'removeFromEstimate'])->name('estimates.remove_equipment');
    Route::get('/estimates/{estimate}/export-excel', [ProjectController::class, 'exportExcel'])->name('estimates.exportExcel');
    Route::post('/estimates/{estimate}/update-equipment', [ProjectController::class, 'updateEquipmentPivot'])->name('estimates.update_equipment');
    Route::get('/estimates/{estimate}/render', function (App\Models\Estimate $estimate) {
        $project = $estimate->project;
        $estimate->calculated = $estimate->getEstimate();
        $html = view('estimates.render', [
            'currentEstimate' => $estimate,
            'project' => $project
        ])->render();
        return response()->json([
            'html' => $html,
            'calculated' => $estimate->calculated
        ]);
    })->middleware('auth');
    Route::post('/estimates/{estimate}/update-staff', function (App\Models\Estimate $estimate) {
        try {
            $data = request()->validate([
                'employee_id' => 'required|integer',
                'rate' => 'nullable|numeric|min:0',
                'coefficient' => 'nullable|numeric|min:0.1',
                'discount' => 'nullable|numeric|min:0|max:100'
            ]);

            $estimate->updateStaff($data['employee_id'], key(array_intersect_key($data, array_flip(['rate', 'coefficient', 'discount']))), $data[key(array_intersect_key($data, array_flip(['rate', 'coefficient', 'discount'])))]);
            $estimate->calculated = $estimate->getEstimate();

            return response()->json([
                'calculated' => $estimate->calculated
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    })->middleware('auth')->name('estimates.update_staff');
    // Оборудование
    Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment');
    Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
    Route::get('/equipment/{equipment}/edit', [EquipmentController::class, 'edit'])->name('equipment.edit');
    Route::put('/equipment/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
    Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');
    Route::get('/equipment/create', [EquipmentController::class, 'create'])->name('equipment.create');

    // Менеджеры
    Route::get('/managers', [ManagerController::class, 'index'])->name('managers');
    Route::get('/managers/create', [ManagerController::class, 'create'])->name('managers.create');
    Route::post('/managers', [ManagerController::class, 'store'])->name('managers.store');
    Route::get('/managers/{user}', [ManagerController::class, 'show'])->name('managers.show');
    Route::patch('/managers/{user}', [ManagerController::class, 'update'])->name('managers.update');
    Route::delete('/managers/{user}', [ManagerController::class, 'destroy'])->name('managers.destroy');
    Route::patch('/managers/{user}/status', [ManagerController::class, 'updateStatus'])->name('managers.status.update');
    Route::post('/managers/{user}/status-comment', [ManagerController::class, 'updateStatusComment'])->name('managers.status-comment.update');
    Route::delete('/managers/{user}/status-comment', [ManagerController::class, 'deleteStatusComment'])->name('managers.status-comment.delete');
    Route::get('/managers/{user}/assignments', [ManagerController::class, 'getAssignments'])->name('managers.assignments');
    Route::post('/assignments', [ManagerController::class, 'createAssignment'])->name('assignments.store');
    Route::get('/managers/projects/autocomplete', [ManagerController::class, 'autocompleteProjects'])->name('managers.projects.autocomplete');

    // Профиль сотрудника
    Route::get('/employees/{user}', [ManagerController::class, 'showProfile'])->name('employees.show');
    Route::post('/employees/{user}/update-main', [ManagerController::class, 'updateMain'])->name('employees.update-main');
    Route::post('/employees/{user}/update-contacts', [ManagerController::class, 'updateContacts'])->name('employees.update-contacts');
    Route::post('/employees/{user}/update-contact', [ManagerController::class, 'updateContact'])->name('employees.update-contact');
    Route::put('/employees/{user}/update-primary-email', [ManagerController::class, 'updatePrimaryEmail'])->name('employees.update-primary-email');
    Route::put('/employees/{user}/update-primary-phone', [ManagerController::class, 'updatePrimaryPhone'])->name('employees.update-primary-phone');
    Route::post('/employees/{user}/update-account', [ManagerController::class, 'updateAccount'])->name('employees.update-account');
    Route::post('/employees/{user}/change-password', [ManagerController::class, 'changePassword'])->name('employees.change-password');
    Route::post('/employees/{user}/avatar', [ManagerController::class, 'uploadAvatar'])->name('employees.avatar');
    Route::post('/employees/{user}/delete-contact', [ManagerController::class, 'deleteContact'])->name('employees.delete-contact');

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
    Route::put('/projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.status.update');
    // Комментарии к интервалам (проект/персонал)
    Route::get('/comments', [PersonnelController::class, 'listComments'])->name('comments.index');
    Route::get('/comments/all', [PersonnelController::class, 'listAllComments'])->name('comments.all');
    Route::get('/comments/range', [PersonnelController::class, 'listCommentsRange'])->name('comments.range');
    Route::post('/comments', [PersonnelController::class, 'storeComment'])->name('comments.store');
    Route::put('/comments/{comment}', [PersonnelController::class, 'updateComment'])->name('comments.update');
    Route::delete('/comments', [PersonnelController::class, 'deleteComments'])->name('comments.delete');
    Route::delete('/comments/{comment}', [PersonnelController::class, 'destroyComment'])->name('comments.destroy');
    // Общий комментарий проекта
    Route::get('/projects/{project}/comment', [PersonnelController::class, 'getProjectComment'])->name('projects.comment.show');
    Route::post('/projects/{project}/comment', [PersonnelController::class, 'saveProjectComment'])->name('projects.comment.save');

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
