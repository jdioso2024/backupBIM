<?php

use App\Http\Controllers\Pimpinan\DashboardController;
use App\Http\Controllers\Pimpinan\MonitorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:pimpinan'])->name('pimpinan.')->prefix('pimpinan')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Monitor PMB
    Route::get('/rekap',[MonitorController::class, 'rekap'])->name('monitor.rekap');
    Route::get('/rekap-pasca',[MonitorController::class, 'rekapPasca'])->name('monitor.rekap-pasca');
    Route::get('/program-studi',[MonitorController::class, 'programStudi'])->name('monitor.program-studi');
    Route::get('/laporan-registrasi',[MonitorController::class, 'laporanRegistrasi'])->name('monitor.laporan-registrasi');
    Route::get('/data-detail',[MonitorController::class, 'dataDetail'])->name('monitor.data-detail');
    Route::get('/perbandingan-tahun',[MonitorController::class, 'perbandinganTahun'])->name('monitor.perbandingan-tahun');
    Route::get('/sebaran-domisili',[MonitorController::class, 'sebaranDomisili'])->name('monitor.sebaran-domisili');
    Route::get('/sebaran-sekolah',[MonitorController::class, 'sebaranSekolah'])->name('monitor.sebaran-sekolah');
});
