<?php

use App\Http\Controllers\Superadmin\AksesController;
use App\Http\Controllers\Superadmin\JalurPendaftaranController;
use App\Http\Controllers\Superadmin\ProgramController;
use App\Http\Controllers\Superadmin\ProgramStudiController;
use App\Http\Controllers\Superadmin\RegisterPeriodController;
use App\Http\Controllers\Superadmin\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:superadmin'])->name('superadmin.')->prefix('superadmin')->group(function () {
    Route::get('/', function () {
        return view('pages.superadmin.index');
    })->name('dashboard');

    Route::resource('jalur-pendaftaran', JalurPendaftaranController::class);
    Route::resource('program-pilihan', ProgramController::class);
    Route::resource('program-studi', ProgramStudiController::class);
    Route::resource('register-periode', RegisterPeriodController::class);
    // Route::resource('akses-user', AksesController::class);

    Route::get('assign-register-period', [RegisterPeriodController::class, 'assignRegisterPeriodAllStudent'])->name('assign-register-period');

    Route::get('setting', [SettingController::class, 'index'])->name('setting.index');
    Route::put('setting', [SettingController::class, 'update'])->name('setting.update');

    Route::get('akses-user', [AksesController::class, 'index'])->name('akses-user.index');
    Route::get('akses-user/user', [AksesController::class, 'user'])->name('akses-user.user.index');
    Route::post('akses-user/user/create', [AksesController::class, 'userStore'])->name('akses-user.user.store');

    Route::post('akses-user/role', [AksesController::class, 'storeRole'])->name('akses-user.store-role');
    Route::post('akses-user/permission', [AksesController::class, 'storePermission'])->name('akses-user.store-permission');
    Route::post('akses-user/assign/{user}', [AksesController::class, 'assign'])->name('akses-user.assign');
    // Route::post('akses-user/assign-permission/{user}', [AksesController::class, 'assignPermission'])->name('akses-user.assign-permission');

    Route::put('akses-user/role/{role}', [AksesController::class, 'updateRole'])->name('akses-user.update-role');
    Route::put('akses-user/permission/{permission}', [AksesController::class, 'updatePermission'])->name('akses-user.update-permission');
    Route::put('akses-user/assign/{user}', [AksesController::class, 'updateAssign'])->name('akses-user.update-assign');

    Route::delete('akses-user/role/{role}', [AksesController::class, 'destroyRole'])->name('akses-user.destroy-role');
    Route::delete('akses-user/permission/{permission}', [AksesController::class, 'destroyPermission'])->name('akses-user.destroy-permission');
});

// middleware(['auth', 'can:superadmin-access'])