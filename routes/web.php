<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\VehicleController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

require __DIR__.'/auth.php';

Route::middleware(['auth', 'subscription'])->group(function () {
    Route::patch('/projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.updateStatus');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
    Route::resource('projects', ProjectController::class)->names('projects');
    Route::post('/projects/{project}/equipment', [ProjectController::class, 'addEquipment'])->name('projects.addEquipment');
    Route::resource('equipment', EquipmentController::class)->names('equipment');
    Route::resource('equipment', EquipmentController::class)->except(['show']);
    Route::resource('managers', ManagerController::class)->names('managers')->only(['index', 'create', 'store'])->middleware('auth');
    Route::get('/managers', [ManagerController::class, 'index'])->name('managers');
    Route::resource('categories', CategoryController::class)->names('categories')->only(['index', 'create', 'store']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('auth');
    Route::post('/users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.updatePermissions')->middleware('auth');
    Route::get('/users', [UserController::class, 'index'])->name('users')->middleware('auth');
    Route::get('/categories/{id}', [CategoryController::class, 'show'])->middleware('auth');
    Route::middleware('auth:sanctum')->get('/categories', [CategoryController::class, 'index']);
    Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment');
    Route::get('/equipment/{equipment}/edit', [EquipmentController::class, 'edit'])->name('equipment.edit');
    Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
    Route::put('/equipment/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
    Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');
    Route::get('/personnel', [PersonnelController::class, 'index'])->name('personnel');
    Route::post('/personnel/assign', [PersonnelController::class, 'assign'])->name('personnel.assign');
    Route::post('/personnel/non-working', [PersonnelController::class, 'addNonWorkingDay'])->name('personnel.non-working');
    Route::post('/personnel/clear', [PersonnelController::class, 'clearInterval'])->name('personnel.clear');
    Route::get('/personnel/time-slots', [PersonnelController::class, 'getTimeSlots'])->name('personnel.time-slots');
    Route::get('/personnel/data', [PersonnelController::class, 'getData'])->name('personnel.data');
    // Комментарии к интервалам (проект/персонал)
    Route::get('/comments', [PersonnelController::class, 'listComments'])->name('comments.index');
    Route::post('/comments', [PersonnelController::class, 'storeComment'])->name('comments.store');
    Route::delete('/comments', [PersonnelController::class, 'deleteComments'])->name('comments.delete');

    Route::resource('clients', ClientController::class)->except(['create', 'show']);
    Route::get('/clients', [ClientController::class, 'index'])->name('clients');

    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

    Route::get('/trip-sheets', [VehicleController::class, 'index'])->name('tripSheets.index');
    Route::post('/trip-sheets', [VehicleController::class, 'storeTripSheet'])->name('tripSheets.store');
    Route::put('/trip-sheets/{tripSheet}', [VehicleController::class, 'updateTripSheet'])->name('tripSheets.update');
    Route::delete('/trip-sheets/{tripSheet}', [VehicleController::class, 'destroyTripSheet'])->name('trip-sheets.destroy');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::resource('sites', SiteController::class);
    Route::get('/sites', [SiteController::class, 'index'])->name('sites');
    Route::get('/projects/{project}/equipment-list', [ProjectController::class, 'equipmentList'])
        ->name('projects.equipmentList');
    Route::post('/projects/{project}/equipment/{equipment}', [ProjectController::class, 'attachEquipment'])
        ->name('projects.equipment.attach');
    Route::delete('/projects/{project}/equipment/{equipment}', [ProjectController::class, 'detachEquipment'])
        ->name('projects.equipment.detach');
    // Привязка/отвязка сотрудников к проекту
    Route::post('/projects/{project}/staff/{user}', function(\Illuminate\Http\Request $request, \App\Models\Project $project, \App\Models\User $user){
        // Привязываем к проекту
        $project->staff()->syncWithoutDetaching([$user->id]);

        // Если прилетел тип ставки — применяем его ко всем рабочим интервалам (busy) сотрудника в этом проекте
        $rateType = $request->input('rate_type');
        $rate     = $request->input('rate');
        if (in_array($rateType, ['hour','project'], true)) {
            \App\Models\WorkInterval::where('employee_id', $user->id)
                ->where('project_id', $project->id)
                ->where('type', 'busy')
                ->update([
                    'hour_rate'    => $rateType === 'hour' ? ($rate !== null ? (float)$rate : null) : null,
                    'project_rate' => $rateType === 'project' ? ($rate !== null ? (float)$rate : null) : null,
                ]);
        }

        return response()->json(['success'=>true]);
    })->name('projects.staff.attach');

    // Возвращает агрегированную сумму для сотрудника по проекту с учётом ставок/интервалов
    Route::get('/projects/{project}/staff/{user}/summary', function(\App\Models\Project $project, \App\Models\User $user){
        $ivs = \App\Models\WorkInterval::where('employee_id', $user->id)
            ->where('project_id', $project->id)
            ->where('type', 'busy')
            ->get(['start_time','end_time','hour_rate','project_rate']);
        $minutes = 0; $hourRate = null; $projectRate = null;
        foreach ($ivs as $iv) {
            $s = strtotime((string)$iv->start_time); $e = strtotime((string)$iv->end_time);
            if ($e > $s) { $minutes += ($e - $s)/60; }
            if (!is_null($iv->project_rate)) { $projectRate = (float)$iv->project_rate; }
            if (is_null($hourRate) && !is_null($iv->hour_rate)) { $hourRate = (float)$iv->hour_rate; }
        }
        $sum = null; $rateType = null; $rate = null;
        if (!is_null($projectRate)) { $sum = $projectRate; $rateType='project'; $rate=$projectRate; }
        elseif (!is_null($hourRate)) { $sum = $hourRate*($minutes/60.0); $rateType='hour'; $rate=$hourRate; }
        return response()->json(['success'=>true, 'sum'=>$sum, 'minutes'=>$minutes, 'rate_type'=>$rateType, 'rate'=>$rate]);
    })->name('projects.staff.summary');
    Route::delete('/projects/{project}/staff/{user}', function(\App\Models\Project $project, \App\Models\User $user){
        $project->staff()->detach($user->id);
        return response()->json(['success'=>true]);
    })->name('projects.staff.detach');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('categories.show');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
    Route::get('/equipment/{id}/edit', [EquipmentController::class, 'edit'])->name('equipment.edit');
    Route::put('/equipment/{id}', [EquipmentController::class, 'update'])->name('equipment.update');
    Route::delete('/equipment/{id}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');
});

Route::get('/subscription/payment', [SubscriptionController::class, 'showPaymentPage'])->name('subscription.payment');
Route::post('/subscription/activate', [SubscriptionController::class, 'activate'])->name('subscription.activate');


