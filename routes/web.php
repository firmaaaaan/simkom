<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $stats = [
        'komputer' => \App\Models\Komputer::count(),
        'laboratorium' => \App\Models\Laboratorium::count(),
        'laporan' => \App\Models\LaporanKendalaKomputer::count(),
        'peminjaman' => \App\Models\PeminjamanKomputer::count(),
    ];

    return view('welcome', compact('stats'));
})->name('home');

Route::get('login', [\App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', \App\Http\Middleware\LogActivity::class])->group(function () {
    Route::get('/admin/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::resource('admin/user', \App\Http\Controllers\Admin\UserController::class)
        ->names('admin.user')
        ->except(['show']);

    Route::post('admin/user/{user}/toggle-active', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])
        ->name('admin.user.toggle-active');

    Route::get('admin/activity-log', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])
        ->name('admin.activity-log.index');

    Route::get('admin/activity-log/user/{user}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'show'])
        ->name('admin.activity-log.show');

    Route::resource('admin/laboratorium', \App\Http\Controllers\LaboratoriumController::class)
        ->names('admin.laboratorium');

    Route::resource('admin/tahun-ajaran', \App\Http\Controllers\TahunAjaranController::class)
        ->names('admin.tahun-ajaran');

    Route::resource('admin/hardware', \App\Http\Controllers\HardwareController::class)
        ->names('admin.hardware')
        ->except(['show']);

    Route::get('admin/hardware/import', [\App\Http\Controllers\HardwareController::class, 'importForm'])
        ->name('admin.hardware.import.form');

    Route::post('admin/hardware/import', [\App\Http\Controllers\HardwareController::class, 'import'])
        ->name('admin.hardware.import');

    Route::get('admin/hardware/export/excel', [\App\Http\Controllers\HardwareController::class, 'exportExcel'])
        ->name('admin.hardware.export.excel');

    Route::get('admin/hardware/export/pdf', [\App\Http\Controllers\HardwareController::class, 'exportPdf'])
        ->name('admin.hardware.export.pdf');

    Route::get('admin/hardware/export/word', [\App\Http\Controllers\HardwareController::class, 'exportWord'])
        ->name('admin.hardware.export.word');

    Route::get('admin/hardware/{hardware}', [\App\Http\Controllers\HardwareController::class, 'show'])
        ->name('admin.hardware.show');

    Route::resource('admin/software', \App\Http\Controllers\SoftwareController::class)
        ->names('admin.software')
        ->except(['show']);

    Route::get('admin/software/import', [\App\Http\Controllers\SoftwareController::class, 'importForm'])
        ->name('admin.software.import.form');

    Route::post('admin/software/import', [\App\Http\Controllers\SoftwareController::class, 'import'])
        ->name('admin.software.import');

    Route::get('admin/software/export/excel', [\App\Http\Controllers\SoftwareController::class, 'exportExcel'])
        ->name('admin.software.export.excel');

    Route::get('admin/software/export/pdf', [\App\Http\Controllers\SoftwareController::class, 'exportPdf'])
        ->name('admin.software.export.pdf');

    Route::get('admin/software/export/word', [\App\Http\Controllers\SoftwareController::class, 'exportWord'])
        ->name('admin.software.export.word');

    Route::get('admin/software/{software}', [\App\Http\Controllers\SoftwareController::class, 'show'])
        ->name('admin.software.show');

    Route::resource('admin/komponen-iot', \App\Http\Controllers\KomponenIotController::class)
        ->names('admin.komponen-iot')
        ->except(['show']);

    Route::get('admin/komponen-iot/import', [\App\Http\Controllers\KomponenIotController::class, 'importForm'])
        ->name('admin.komponen-iot.import.form');

    Route::post('admin/komponen-iot/import', [\App\Http\Controllers\KomponenIotController::class, 'import'])
        ->name('admin.komponen-iot.import');

    Route::get('admin/komponen-iot/export/excel', [\App\Http\Controllers\KomponenIotController::class, 'exportExcel'])
        ->name('admin.komponen-iot.export.excel');

    Route::get('admin/komponen-iot/export/pdf', [\App\Http\Controllers\KomponenIotController::class, 'exportPdf'])
        ->name('admin.komponen-iot.export.pdf');

    Route::get('admin/komponen-iot/export/word', [\App\Http\Controllers\KomponenIotController::class, 'exportWord'])
        ->name('admin.komponen-iot.export.word');

    Route::get('admin/komponen-iot/{komponen_iot}', [\App\Http\Controllers\KomponenIotController::class, 'show'])
        ->name('admin.komponen-iot.show');

    Route::resource('admin/komponen-jaringan', \App\Http\Controllers\KomponenJaringanController::class)
        ->names('admin.komponen-jaringan')
        ->except(['show']);

    Route::get('admin/komponen-jaringan/import', [\App\Http\Controllers\KomponenJaringanController::class, 'importForm'])
        ->name('admin.komponen-jaringan.import.form');

    Route::post('admin/komponen-jaringan/import', [\App\Http\Controllers\KomponenJaringanController::class, 'import'])
        ->name('admin.komponen-jaringan.import');

    Route::get('admin/komponen-jaringan/export/excel', [\App\Http\Controllers\KomponenJaringanController::class, 'exportExcel'])
        ->name('admin.komponen-jaringan.export.excel');

    Route::get('admin/komponen-jaringan/export/pdf', [\App\Http\Controllers\KomponenJaringanController::class, 'exportPdf'])
        ->name('admin.komponen-jaringan.export.pdf');

    Route::get('admin/komponen-jaringan/export/word', [\App\Http\Controllers\KomponenJaringanController::class, 'exportWord'])
        ->name('admin.komponen-jaringan.export.word');

    Route::get('admin/komponen-jaringan/{komponen_jaringan}', [\App\Http\Controllers\KomponenJaringanController::class, 'show'])
        ->name('admin.komponen-jaringan.show');

    Route::get('admin/inventaris-iot-jaringan/import', [\App\Http\Controllers\InventarisIoTJaringanController::class, 'importForm'])
        ->name('admin.inventaris-iot-jaringan.import.form');

    Route::post('admin/inventaris-iot-jaringan/import/excel', [\App\Http\Controllers\InventarisIoTJaringanController::class, 'importExcel'])
        ->name('admin.inventaris-iot-jaringan.import.excel');

    Route::post('admin/inventaris-iot-jaringan/import/pdf', [\App\Http\Controllers\InventarisIoTJaringanController::class, 'importPdf'])
        ->name('admin.inventaris-iot-jaringan.import.pdf');

    Route::post('admin/inventaris-iot-jaringan/import/word', [\App\Http\Controllers\InventarisIoTJaringanController::class, 'importWord'])
        ->name('admin.inventaris-iot-jaringan.import.word');

    Route::get('admin/inventaris-iot-jaringan/export/excel', [\App\Http\Controllers\InventarisIoTJaringanController::class, 'exportExcel'])
        ->name('admin.inventaris-iot-jaringan.export.excel');

    Route::get('admin/inventaris-iot-jaringan/export/pdf', [\App\Http\Controllers\InventarisIoTJaringanController::class, 'exportPdf'])
        ->name('admin.inventaris-iot-jaringan.export.pdf');

    Route::get('admin/inventaris-iot-jaringan/export/word', [\App\Http\Controllers\InventarisIoTJaringanController::class, 'exportWord'])
        ->name('admin.inventaris-iot-jaringan.export.word');

    Route::get('admin/inventaris-iot-jaringan/qr-stiker', [\App\Http\Controllers\InventarisIoTJaringanController::class, 'qrStikerByKategori'])
        ->name('admin.inventaris-iot-jaringan.qr-stiker');

    Route::resource('admin/inventaris-iot-jaringan', \App\Http\Controllers\InventarisIoTJaringanController::class)
        ->names('admin.inventaris-iot-jaringan');

    Route::get('admin/komputer/{komputer}/kartu-kendali/create', [\App\Http\Controllers\KomputerController::class, 'createKartuKendali'])
        ->name('admin.komputer.kartu-kendali.create');

    Route::post('admin/komputer/{komputer}/kartu-kendali', [\App\Http\Controllers\KomputerController::class, 'storeKartuKendali'])
        ->name('admin.komputer.kartu-kendali.store');

    Route::get('admin/komputer/{komputer}/kartu-kendali/print', [\App\Http\Controllers\KomputerController::class, 'printKartuKendali'])
        ->name('admin.komputer.kartu-kendali.print');

    Route::get('admin/komputer/{komputer}/qr', [\App\Http\Controllers\KomputerController::class, 'qrCode'])
        ->name('admin.komputer.qr');

    Route::get('admin/komputer/{komputer}/pemeliharaan', [\App\Http\Controllers\KomputerController::class, 'riwayatPemeliharaan'])
        ->name('admin.komputer.pemeliharaan');

    Route::get('admin/pemeliharaan-komputer', [\App\Http\Controllers\PemeliharaanKomputerController::class, 'index'])
        ->name('admin.pemeliharaan-komputer.index');

    Route::get('admin/pemeliharaan-komputer/export/excel', [\App\Http\Controllers\PemeliharaanKomputerController::class, 'exportExcel'])
        ->name('admin.pemeliharaan-komputer.export.excel');

    Route::get('admin/pemeliharaan-komputer/create', [\App\Http\Controllers\PemeliharaanKomputerController::class, 'create'])
        ->name('admin.pemeliharaan-komputer.create');

    Route::post('admin/pemeliharaan-komputer', [\App\Http\Controllers\PemeliharaanKomputerController::class, 'store'])
        ->name('admin.pemeliharaan-komputer.store');

    Route::get('admin/pemeliharaan-komputer/{pemeliharaan}/edit', [\App\Http\Controllers\PemeliharaanKomputerController::class, 'edit'])
        ->name('admin.pemeliharaan-komputer.edit');

    Route::put('admin/pemeliharaan-komputer/{pemeliharaan}', [\App\Http\Controllers\PemeliharaanKomputerController::class, 'update'])
        ->name('admin.pemeliharaan-komputer.update');

    Route::delete('admin/pemeliharaan-komputer/{pemeliharaan}', [\App\Http\Controllers\PemeliharaanKomputerController::class, 'destroy'])
        ->name('admin.pemeliharaan-komputer.destroy');

    Route::get('admin/komputer/{komputer}/pemeliharaan/export/excel', [\App\Http\Controllers\PemeliharaanKomputerController::class, 'exportExcelByComputer'])
        ->name('admin.komputer.pemeliharaan.export.excel');

    Route::get('admin/komputer/qr-stiker', [\App\Http\Controllers\KomputerController::class, 'qrStikerByLaboratorium'])
        ->name('admin.komputer.qr-stiker');

    Route::resource('admin/komputer', \App\Http\Controllers\KomputerController::class)
        ->names('admin.komputer');

    Route::get('admin/komputer/export/excel', [\App\Http\Controllers\KomputerController::class, 'exportExcel'])
        ->name('admin.komputer.export.excel');

    Route::get('admin/komputer/export/pdf', [\App\Http\Controllers\KomputerController::class, 'exportPdf'])
        ->name('admin.komputer.export.pdf');

    Route::get('admin/komputer/export/word', [\App\Http\Controllers\KomputerController::class, 'exportWord'])
        ->name('admin.komputer.export.word');

    Route::get('admin/inventaris-iot-jaringan/{inventaris_iot_jaringan}/kartu-kendali/create', [\App\Http\Controllers\InventarisIoTJaringanController::class, 'createKartuKendali'])
        ->name('admin.inventaris-iot-jaringan.kartu-kendali.create');

    Route::post('admin/inventaris-iot-jaringan/{inventaris_iot_jaringan}/kartu-kendali', [\App\Http\Controllers\InventarisIoTJaringanController::class, 'storeKartuKendali'])
        ->name('admin.inventaris-iot-jaringan.kartu-kendali.store');

    Route::get('admin/inventaris-iot-jaringan/{inventaris_iot_jaringan}/kartu-kendali/print', [\App\Http\Controllers\InventarisIoTJaringanController::class, 'printKartuKendali'])
        ->name('admin.inventaris-iot-jaringan.kartu-kendali.print');

    Route::get('admin/kartu-kendali', [\App\Http\Controllers\KartuKendaliController::class, 'index'])
        ->name('admin.kartu-kendali.index');

    Route::get('admin/kartu-kendali/export/komputer', [\App\Http\Controllers\KartuKendaliController::class, 'exportKomputer'])
        ->name('admin.kartu-kendali.export.komputer');

    Route::get('admin/kartu-kendali/export/iot', [\App\Http\Controllers\KartuKendaliController::class, 'exportIot'])
        ->name('admin.kartu-kendali.export.iot');

    Route::get('admin/inventaris-iot-jaringan/{inventaris_iot_jaringan}/qr', [\App\Http\Controllers\InventarisIoTJaringanController::class, 'qrCode'])
        ->name('admin.inventaris-iot-jaringan.qr');

    Route::get('admin/peminjaman-iot-jaringan', [\App\Http\Controllers\PeminjamanInventarisIoTJaringanController::class, 'index'])
        ->name('admin.peminjaman-iot-jaringan.index');

    Route::get('admin/peminjaman-iot-jaringan/export', [\App\Http\Controllers\PeminjamanInventarisIoTJaringanController::class, 'exportExcel'])
        ->name('admin.peminjaman-iot-jaringan.export');

    Route::post('admin/peminjaman-iot-jaringan/{peminjaman}/return', [\App\Http\Controllers\PeminjamanInventarisIoTJaringanController::class, 'returnItem'])
        ->name('admin.peminjaman-iot-jaringan.return');

    Route::get('admin/laporan-kendala-komputer', [\App\Http\Controllers\LaporanKendalaKomputerController::class, 'index'])
        ->name('admin.laporan-kendala-komputer.index');

    Route::get('admin/laporan-kendala-komputer/export', [\App\Http\Controllers\LaporanKendalaKomputerController::class, 'exportExcel'])
        ->name('admin.laporan-kendala-komputer.export');

    Route::get('admin/laporan-kendala-komputer/{laporanKendalaKomputer}', [\App\Http\Controllers\LaporanKendalaKomputerController::class, 'show'])
        ->name('admin.laporan-kendala-komputer.show');

    Route::post('admin/laporan-kendala-komputer/{laporanKendalaKomputer}/status', [\App\Http\Controllers\LaporanKendalaKomputerController::class, 'updateStatus'])
        ->name('admin.laporan-kendala-komputer.update-status');

    Route::get('admin/jadwal-kuliah', [\App\Http\Controllers\JadwalKuliahController::class, 'adminIndex'])
        ->name('admin.jadwal-kuliah.index');

    Route::post('admin/jadwal-kuliah', [\App\Http\Controllers\JadwalKuliahController::class, 'adminStore'])
        ->name('admin.jadwal-kuliah.store');

    Route::delete('admin/jadwal-kuliah/{id}', [\App\Http\Controllers\JadwalKuliahController::class, 'adminDestroy'])
        ->name('admin.jadwal-kuliah.destroy');
});

Route::get('inventaris-iot-jaringan/{inventaris_iot_jaringan}/peminjaman', [\App\Http\Controllers\PeminjamanInventarisIoTJaringanController::class, 'create'])
    ->name('inventaris-iot-jaringan.peminjaman.create');

Route::post('inventaris-iot-jaringan/{inventaris_iot_jaringan}/peminjaman', [\App\Http\Controllers\PeminjamanInventarisIoTJaringanController::class, 'store'])
    ->name('inventaris-iot-jaringan.peminjaman.store');

Route::get('inventaris-iot-jaringan/{inventaris_iot_jaringan}/peminjaman/sukses', [\App\Http\Controllers\PeminjamanInventarisIoTJaringanController::class, 'success'])
    ->name('inventaris-iot-jaringan.peminjaman.success');

Route::get('peminjaman-komputer', [\App\Http\Controllers\PeminjamanKomputerController::class, 'index'])
    ->name('peminjaman-komputer.index');

Route::get('peminjaman-komputer/{komputer}', [\App\Http\Controllers\PeminjamanKomputerController::class, 'create'])
    ->name('peminjaman-komputer.create');

Route::post('peminjaman-komputer', [\App\Http\Controllers\PeminjamanKomputerController::class, 'store'])
    ->name('peminjaman-komputer.store');

Route::get('peminjaman-komputer/sukses', [\App\Http\Controllers\PeminjamanKomputerController::class, 'success'])
    ->name('peminjaman-komputer.success');

Route::get('peminjaman-komputer/verifikasi/{kode_tracker}', [\App\Http\Controllers\PeminjamanKomputerController::class, 'verifikasi'])
    ->name('peminjaman-komputer.verifikasi');

Route::get('admin/peminjaman-komputer', [\App\Http\Controllers\PeminjamanKomputerController::class, 'adminIndex'])
    ->name('admin.peminjaman-komputer.index');

Route::get('admin/peminjaman-komputer/export/excel', [\App\Http\Controllers\PeminjamanKomputerController::class, 'exportExcel'])
    ->name('admin.peminjaman-komputer.export.excel');

Route::get('admin/peminjaman-komputer/export/pdf', [\App\Http\Controllers\PeminjamanKomputerController::class, 'exportPdf'])
    ->name('admin.peminjaman-komputer.export.pdf');

Route::get('admin/peminjaman-komputer/export/word', [\App\Http\Controllers\PeminjamanKomputerController::class, 'exportWord'])
    ->name('admin.peminjaman-komputer.export.word');

Route::post('admin/peminjaman-komputer/{peminjaman}/return', [\App\Http\Controllers\PeminjamanKomputerController::class, 'returnItem'])
    ->name('admin.peminjaman-komputer.return');

Route::post('admin/peminjaman-komputer/{peminjaman}/approve', [\App\Http\Controllers\PeminjamanKomputerController::class, 'approve'])
    ->name('admin.peminjaman-komputer.approve');

Route::post('admin/peminjaman-komputer/{peminjaman}/reject', [\App\Http\Controllers\PeminjamanKomputerController::class, 'reject'])
    ->name('admin.peminjaman-komputer.reject');

Route::get('lapor-kendala-komputer', [\App\Http\Controllers\LaporanKendalaKomputerController::class, 'create'])
    ->name('laporan-kendala-komputer.create');

Route::post('lapor-kendala-komputer', [\App\Http\Controllers\LaporanKendalaKomputerController::class, 'store'])
    ->name('laporan-kendala-komputer.store');

Route::get('lapor-kendala-komputer/sukses', [\App\Http\Controllers\LaporanKendalaKomputerController::class, 'success'])
    ->name('laporan-kendala-komputer.success');

Route::get('lapor-kendala-komputer/track', [\App\Http\Controllers\LaporanKendalaKomputerController::class, 'track'])
    ->name('laporan-kendala-komputer.track');

Route::get('jadwal-kuliah', [\App\Http\Controllers\JadwalKuliahController::class, 'index'])
    ->name('jadwal-kuliah.index');

Route::get('jadwal-kuliah/api/fetch', [\App\Http\Controllers\JadwalKuliahController::class, 'fetchJadwal'])
    ->name('jadwal-kuliah.api.fetch');

Route::middleware(['auth'])->group(function () {
    Route::get('admin/api/notifications', [\App\Http\Controllers\DashboardController::class, 'notifications'])
        ->name('admin.api.notifications');

    Route::get('admin/settings', [\App\Http\Controllers\SettingsController::class, 'index'])
        ->name('admin.settings.index');

    Route::post('admin/settings/password', [\App\Http\Controllers\SettingsController::class, 'updatePassword'])
        ->name('admin.settings.password.update');

    Route::post('admin/settings/password/reset', [\App\Http\Controllers\SettingsController::class, 'resetToDefault'])
        ->name('admin.settings.password.reset');
});
