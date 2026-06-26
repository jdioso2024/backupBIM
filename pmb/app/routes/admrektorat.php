<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KipDashboardController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\DashboardController as ControllersDashboardController;
use App\Http\Controllers\ExamDataController;
use App\Http\Controllers\KipController;
use App\Http\Controllers\StudentController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admrektorat'])->name('admrektorat.')->prefix('admrektorat')->group(function () {
   Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
   Route::get('/student/{student}', [StudentController::class, 'show'])->name('student.show');
   Route::get('/student/{student}/edit', [StudentController::class, 'edit'])->name('student.edit');
   Route::put('/student/{student}', [StudentController::class, 'update'])->name('student.update');
   Route::delete('/student/{student}', [StudentController::class, 'destroy'])->name('student.destroy');
   Route::get('/student/daftar-ulang/{student}', [StudentController::class, 'showDocument'])->name('student.showDocument');

   Route::post('change-status/{student}', [StudentController::class, 'changeStatus'])->name('student.change-status');
   // Route::post('rejected-scholarship/{student}', [StudentController::class, 'rejectedScholarship'])->name('student.rejected-scholarship');

   Route::post('exam-data/{student}', [ExamDataController::class, 'store'])->name('exam-data.store');
   Route::get('/export-student', [StudentController::class, 'exportStudent'])->name('export.student');
   Route::post('reblast-email/all', [StudentController::class, 'reblastEmail'])->name('reblast-email');
   Route::post('reblast-email/{student}', [StudentController::class, 'reblastEmailStudent'])->name('reblast-email-student');
   Route::post('reblast-wa/{student}', [StudentController::class, 'reblastWaStudent'])->name('reblast-wa-student');

   Route::post('/document', [ControllersDashboardController::class, 'storeDocument'])->name('document.store');

   Route::resource('promo-code', PromoCodeController::class);

   Route::prefix('kip')->name('kip.')->group(function () {
      Route::get('/', [KipDashboardController::class, 'index'])->name('dashboard');
      Route::get('/show/{student}', [KipDashboardController::class, 'show'])->name('kip-show');
      Route::get('/show/{student}/edit', [KipDashboardController::class, 'edit'])->name('kip-edit');
      Route::post('/document', [KipDashboardController::class, 'storeDocument'])->name('document.store');
      Route::get('/export-student', [KipController::class, 'exportStudent'])->name('export.student');

   });

});