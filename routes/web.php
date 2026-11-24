
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\PtnController;
use App\Http\Controllers\UptController;
use App\Http\Controllers\RuangLingkupController;
use App\Http\Controllers\KerjasamaController;
use App\Http\Controllers\DokumenKerjasamaController;
use App\Http\Controllers\ImplementasiController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UptVerificationController;

Route::get('/', [PublicPortalController::class, 'index'])->name('public.index');
Route::get('/mitra/{ptn}', [PublicPortalController::class, 'show'])->name('public.ptn.show');
Route::get('/unit-upt/{upt}', [PublicPortalController::class, 'showUpt'])->name('public.upt.show');
Route::get('/publik/dokumen/{dokumenKerjasama}', [DokumenKerjasamaController::class, 'publicDownload'])->name('public.documents.download');
Route::post('/logout', [LoginController::class, 'logout'])->middleware(['auth', 'active'])->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'authenticate'])
        ->name('login.authenticate');
});

Route::middleware(['auth', 'active', 'role:admin,super_admin'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/ptn', [PtnController::class, 'index'])->name('ptn.index');
    Route::get('/ptn/export/csv', [PtnController::class, 'export'])->name('ptn.export');
    Route::post('/ptn/import/csv', [PtnController::class, 'import'])->name('ptn.import');
    Route::resource('ptn', PtnController::class)->except('index');
    Route::resource('upt', UptController::class)->except('show');
    Route::get('/ruang-lingkup', [RuangLingkupController::class, 'index'])->name('scope.index');
    Route::post('/ruang-lingkup', [RuangLingkupController::class, 'store'])->name('scope.store');
    Route::put('/ruang-lingkup/{ruangLingkup}', [RuangLingkupController::class, 'update'])->name('scope.update');
    Route::delete('/ruang-lingkup/{ruangLingkup}', [RuangLingkupController::class, 'destroy'])->name('scope.destroy');
    Route::resource('kerjasama', KerjasamaController::class);
    Route::get('/kerjasama/{kerjasama}/dokumen', [DokumenKerjasamaController::class, 'forAgreement'])->name('kerjasama.documents.index');
    Route::get('/dokumen-kerjasama', [DokumenKerjasamaController::class, 'index'])->name('documents.index');
    Route::post('/kerjasama/{kerjasama}/dokumen', [DokumenKerjasamaController::class, 'store'])->name('documents.store');
    Route::get('/dokumen-kerjasama/{dokumenKerjasama}/download', [DokumenKerjasamaController::class, 'download'])->name('documents.download');
    Route::patch('/dokumen-kerjasama/{dokumenKerjasama}/visibilitas', [DokumenKerjasamaController::class, 'updateVisibility'])->name('documents.visibility');
    Route::delete('/dokumen-kerjasama/{dokumenKerjasama}', [DokumenKerjasamaController::class, 'destroy'])->name('documents.destroy');
    Route::get('/implementasi', [ImplementasiController::class, 'index'])->name('implementation.index');
    Route::post('/implementasi', [ImplementasiController::class, 'store'])->name('implementation.store');
    Route::get('/implementasi-dokumen/{implementasiDokumen}/download', [ImplementasiController::class, 'download'])->name('implementation.documents.download');
    Route::delete('/implementasi/{implementasi}', [ImplementasiController::class, 'destroy'])->name('implementation.destroy');
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/{notifikasi}/read', [NotifikasiController::class, 'read'])->name('notifications.read');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('reports.index');
    Route::get('/laporan/export/csv', [LaporanController::class, 'export'])->name('reports.export');

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    });

});

Route::middleware(['auth', 'active', 'role:upt'])->prefix('portal-upt')->name('upt.portal.')->group(function () {
    Route::get('/verifikasi-kerjasama', [UptVerificationController::class, 'index'])->name('index');
    Route::post('/verifikasi-kerjasama/{kerjasama}', [UptVerificationController::class, 'update'])->name('verify');
    Route::get('/dokumen/{dokumenKerjasama}', [DokumenKerjasamaController::class, 'uptDownload'])->name('documents.download');
});
