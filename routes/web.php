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
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

require __DIR__.'/auth.php';

Route::middleware(['auth', 'subscription'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
    Route::get('managers', [ManagerController::class, 'index'])->name('managers');
    Route::resource('projects', ProjectController::class)->names('projects');
    Route::post('/projects/{project}/equipment', [ProjectController::class, 'addEquipment'])->name('projects.addEquipment');
    Route::resource('equipment', EquipmentController::class)->names('equipment');
    Route::resource('equipment', EquipmentController::class)->except(['show']);
    Route::resource('managers', ManagerController::class)->names('managers')->only(['index', 'create', 'store']);
    Route::resource('categories', CategoryController::class)->names('categories')->only(['index', 'create', 'store']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('auth');
    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('auth');
    Route::post('/users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.updatePermissions')->middleware('auth');
    Route::get('/categories/{id}', [CategoryController::class, 'show'])->middleware('auth');
    Route::middleware('auth:sanctum')->get('/categories', [CategoryController::class, 'index']);
    Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment');
    Route::get('/equipment/{equipment}/edit', [EquipmentController::class, 'edit'])->name('equipment.edit');
    Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
    Route::put('/equipment/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
    Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');
    Route::get('personnel', [PersonnelController::class, 'index'])->name('personnel');
    Route::post('/personnel/assign', [PersonnelController::class, 'assign'])->name('personnel.assign');
    Route::post('/personnel/non-working', [PersonnelController::class, 'addNonWorkingDay'])->name('personnel.non-working');
    Route::get('/personnel/time-slots', [PersonnelController::class, 'getTimeSlots'])->name('personnel.time-slots');
});

Route::get('/subscription/payment', [SubscriptionController::class, 'showPaymentPage'])->name('subscription.payment');
Route::post('/subscription/activate', [SubscriptionController::class, 'activate'])->name('subscription.activate');


