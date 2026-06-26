<?php

use App\Http\Controllers\BiodataController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KipController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StudentController::class, 'index'])->name('welcome');
Route::get('/pendaftaran-kip', [KipController::class, 'index'])->name('kip-register');
Route::get('pengumuman/daftar-ulang', [StudentController::class, 'daftarUlang'])->name('daftar-ulang');

Route::middleware('auth', 'role:student')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/document', [DashboardController::class, 'storeDocument'])->name('document.store');

    // Daftar Ulang
    Route::get('/{student}/daftar-ulang', [BiodataController::class, 'studentRegister'])->name('student.daftar-ulang');
    Route::post('/{student}/daftar-ulang', [BiodataController::class, 'storeStudentRegister'])->name('student.daftar-ulang.store');
    Route::get('/{student}/daftar-ulang/administrasi', [BiodataController::class, 'studentRegister2'])->name('student.daftar-ulang.administrasi');
    Route::post('/{student}/daftar-ulang/administrasi', [BiodataController::class, 'storeStudentRegister2'])->name('student.daftar-ulang.administrasi.store');
    Route::get('/{student}/daftar-ulang/pembayaran', [BiodataController::class, 'studentRegister3'])->name('student.daftar-ulang.pembayaran');
    Route::post('/{student}/daftar-ulang/pembayaran', [BiodataController::class, 'storeStudentRegister3'])->name('student.daftar-ulang.pembayaran.store');
    Route::post('/{student}/daftar-ulang/beasiswa', [BiodataController::class, 'storeStudentRegisterScholarship'])->name('student.daftar-ulang.scholarship.store');
    Route::get('/{student}/daftar-ulang/beasiswa/cetak-kartu', [BiodataController::class, 'studentRegisterPrintCard'])->name('student.daftar-ulang.scholarship.print');
    Route::get('/{student}/daftar-ulang/beasiswa/cetak-kartu/download', [BiodataController::class, 'downloadStudentCard'])->name('student.daftar-ulang.scholarship.printPdf');
    Route::get('okgas', function() {
        return view('pages.student.daftar-ulang.pdf.index');
    });

    // kip
    Route::post('/student/kip/upload', [KipController::class, 'storeDocument'])->name('kip.storeDocument');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/student', [StudentController::class, 'index'])->name('student.index');
Route::post('/student/register', [StudentController::class, 'store'])->name('student.store');
Route::post('/student/register/kip', [KipController::class, 'store'])->name('student.store.kip');
Route::post('/validate-promo', [StudentController::class, 'validatePromo'])->name('promo.validate');


require __DIR__ . '/auth.php';
require __DIR__ . '/admrektorat.php';
require __DIR__ . '/superadmin.php';
require __DIR__ . '/pimpinan.php';
