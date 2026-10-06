<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BoxScanController;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\BoxComponentController;
use App\Http\Controllers\ComputerController;
use App\Http\Controllers\DeviceCheckController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\ComponentBorrowingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HardwareController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\SoftwareController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PublicTicketController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\PublicTrackerController;
use App\Http\Controllers\PublicComputerCardController;
use App\Http\Controllers\PublicLabScheduleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\BorrowingController as AdminBorrowingController;
use App\Http\Controllers\Admin\BoxUsageController as AdminBoxUsageController;
use App\Http\Controllers\LabScheduleController;
use App\Http\Controllers\LabScanController;
use App\Http\Controllers\Admin\LabUsageController as AdminLabUsageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function (\Illuminate\Http\Request $request) {
    $laboratories = \App\Models\Laboratory::where('status', 'Aktif')
        ->where('show_in_schedule', true)
        ->orderBy('name')
        ->get();
    $showSpec = \App\Models\Setting::publicSpecEnabled();

    $selectedLabId = $request->laboratory_id;
    $selectedLab = null;
    if ($selectedLabId !== null && $selectedLabId !== '') {
        $selectedLab = $laboratories->firstWhere('id', $selectedLabId);
    }
    if (! $selectedLab && $laboratories->isNotEmpty()) {
        $selectedLab = $laboratories->first();
    }

    $computers = collect();
    if ($selectedLab) {
        $computers = \App\Models\Computer::where('laboratory_id', $selectedLab->id)
            ->with($showSpec ? ['laboratory', 'hardware', 'software'] : ['laboratory'])
            ->withCount(['tickets' => function ($q) {
                $q->whereIn('status', ['Open', 'In Progress']);
            }])
            ->orderBy('code')
            ->get();
    }

    return view('welcome', compact('laboratories', 'computers', 'selectedLab', 'showSpec'));
});

// Auth Routes (guest only)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public Routes (Lapor Kendala - tanpa login)
Route::prefix('lapor-kendala')->name('lapor-kendala.')->group(function () {
    Route::get('/', [PublicTicketController::class, 'index'])->name('index');
    Route::get('/computer/{computer}', [PublicTicketController::class, 'create'])->name('create');
    Route::post('/store', [PublicTicketController::class, 'store'])->name('store');
    Route::get('/track', fn() => redirect()->route('track.index'))->name('track-form');
    Route::get('/track/{tracking_code}', fn($tracking_code) => redirect()->route('track.search', ['tracking_code' => $tracking_code]))->name('track');
});

// Public Routes (Pinjam Komputer - tanpa login)
Route::prefix('pinjam-komputer')->name('pinjam-komputer.')->group(function () {
    Route::get('/computer/{computer}', [BorrowController::class, 'create'])->name('create');
    Route::post('/store', [BorrowController::class, 'store'])->name('store');
    Route::get('/track', fn() => redirect()->route('track.index'))->name('track-form');
    Route::get('/track/{tracking_code}', fn($tracking_code) => redirect()->route('track.search', ['tracking_code' => $tracking_code]))->name('track');
});

// Public Routes (Unified Tracking - gabungan tiket & peminjaman)
Route::get('/track', [PublicTrackerController::class, 'index'])->name('track.index');
Route::get('/track/search', [PublicTrackerController::class, 'search'])->name('track.search');

// Public Routes (Box Scan - tanpa login)
Route::prefix('box-scan')->name('box-scan.')->group(function () {
    Route::get('/{boxCode}', [BoxScanController::class, 'scan'])->name('scan');
    Route::post('/{boxCode}/use', [BoxScanController::class, 'useBox'])->name('use');
    Route::post('/{boxCode}/quick-use', [BoxScanController::class, 'quickUse'])->name('quick-use');
    Route::get('/{boxCode}/available', [BoxScanController::class, 'available'])->name('available');
    Route::post('/multi-use', [BoxScanController::class, 'multiUse'])->name('multi-use');
    Route::post('/multi-quick-use', [BoxScanController::class, 'multiQuickUse'])->name('multi-quick-use');
});

// Public Routes (Lab Scan - check-in penggunaan lab via QR, tanpa login)
Route::prefix('lab-scan')->name('lab-scan.')->group(function () {
    Route::get('/', [LabScanController::class, 'pick'])->name('pick');
    Route::get('/{labCode}', [LabScanController::class, 'scan'])->name('scan');
    Route::post('/{labCode}/check-in', [LabScanController::class, 'checkIn'])->name('check-in');
});

// Public Routes (Jadwal Lab - tanpa login, read-only)
Route::get('/jadwal-lab', [PublicLabScheduleController::class, 'index'])->name('jadwal-lab.index');

// Public Routes (Kartu Kendali - tanpa login, read-only riwayat pengecekan)
Route::get('/kartu/{computer}', [PublicComputerCardController::class, 'show'])->name('kartu.show');

// Link lama /computers/{computer}/card (QR stiker yang sudah beredar).
// Tamu & user tanpa izin dialihkan ke halaman publik oleh ComputerController@card,
// sedangkan admin/laboran tetap mendapat halaman kartu kendali lengkap.
Route::get('/computers/{computer}/card', [ComputerController::class, 'card'])->name('computers.card');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil (akun sendiri): ganti email & password
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/email', [ProfileController::class, 'updateEmail'])->name('profile.update-email');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // Notifikasi realtime (polling)
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Halaman semua notifikasi + hapus (global, semua user login)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // User Management — per aksi: view/create/edit/delete-users
    Route::get('users/export', [UserController::class, 'export'])
        ->middleware('permission:view-users')
        ->name('users.export');
    Route::resource('users', UserController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-users')
        ->middlewareFor(['create', 'store'], 'permission:create-users')
        ->middlewareFor(['edit', 'update'], 'permission:edit-users')
        ->middlewareFor(['destroy'], 'permission:delete-users');

    // Role Management — per aksi: view/create/edit/delete-roles
    Route::get('roles/export', [RoleController::class, 'export'])
        ->middleware('permission:view-roles')
        ->name('roles.export');
    Route::resource('roles', RoleController::class)
        ->except(['show'])
        ->middlewareFor(['index'], 'permission:view-roles')
        ->middlewareFor(['create', 'store'], 'permission:create-roles')
        ->middlewareFor(['edit', 'update'], 'permission:edit-roles')
        ->middlewareFor(['destroy'], 'permission:delete-roles');

    // Data Master - Admin only
    Route::get('laboratories/export', [LaboratoryController::class, 'export'])
        ->middleware('permission:view-laboratories')
        ->name('laboratories.export');
    Route::delete('laboratories/bulk-destroy', [LaboratoryController::class, 'bulkDestroy'])
        ->middleware('permission:delete-laboratories')
        ->name('laboratories.bulk-destroy');
    Route::resource('laboratories', LaboratoryController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-laboratories')
        ->middlewareFor(['create', 'store'], 'permission:create-laboratories')
        ->middlewareFor(['edit', 'update'], 'permission:edit-laboratories')
        ->middlewareFor(['destroy'], 'permission:delete-laboratories');

    Route::get('academic-years/export', [AcademicYearController::class, 'export'])
        ->middleware('permission:view-academic-years')
        ->name('academic-years.export');
    Route::delete('academic-years/bulk-destroy', [AcademicYearController::class, 'bulkDestroy'])
        ->middleware('permission:delete-academic-years')
        ->name('academic-years.bulk-destroy');
    Route::resource('academic-years', AcademicYearController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-academic-years')
        ->middlewareFor(['create', 'store'], 'permission:create-academic-years')
        ->middlewareFor(['edit', 'update'], 'permission:edit-academic-years')
        ->middlewareFor(['destroy'], 'permission:delete-academic-years');

    // Hardware (Admin + Laboran)
    Route::middleware('permission:create-hardware')->group(function () {
        Route::get('hardware/import', [HardwareController::class, 'import'])->name('hardware.import');
        Route::post('hardware/import', [HardwareController::class, 'storeImport'])->name('hardware.store-import');
    });
    Route::middleware('permission:view-hardware')->group(function () {
        Route::get('hardware/export', [HardwareController::class, 'export'])->name('hardware.export');
        Route::get('hardware/template', [HardwareController::class, 'template'])->name('hardware.template');
    });
    Route::delete('hardware/bulk-destroy', [HardwareController::class, 'bulkDestroy'])
        ->middleware('permission:delete-hardware')
        ->name('hardware.bulk-destroy');
    Route::resource('hardware', HardwareController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-hardware')
        ->middlewareFor(['create', 'store'], 'permission:create-hardware')
        ->middlewareFor(['edit', 'update'], 'permission:edit-hardware')
        ->middlewareFor(['destroy'], 'permission:delete-hardware');

    // Software (Admin + Laboran)
    Route::middleware('permission:create-software')->group(function () {
        Route::get('software/import', [SoftwareController::class, 'import'])->name('software.import');
        Route::post('software/import', [SoftwareController::class, 'storeImport'])->name('software.store-import');
    });
    Route::middleware('permission:view-software')->group(function () {
        Route::get('software/export', [SoftwareController::class, 'export'])->name('software.export');
        Route::get('software/template', [SoftwareController::class, 'template'])->name('software.template');
    });
    Route::delete('software/bulk-destroy', [SoftwareController::class, 'bulkDestroy'])
        ->middleware('permission:delete-software')
        ->name('software.bulk-destroy');
    Route::resource('software', SoftwareController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-software')
        ->middlewareFor(['create', 'store'], 'permission:create-software')
        ->middlewareFor(['edit', 'update'], 'permission:edit-software')
        ->middlewareFor(['destroy'], 'permission:delete-software');

    // Components (Admin + Laboran)
    Route::middleware('permission:create-components')->group(function () {
        Route::get('components/import', [ComponentController::class, 'import'])->name('components.import');
        Route::post('components/import', [ComponentController::class, 'storeImport'])->name('components.store-import');
        Route::post('boxes', [BoxController::class, 'store'])->name('boxes.store');
        Route::post('boxes/bulk-add-component', [BoxController::class, 'bulkAddComponent'])->name('boxes.bulk-add-component');
        Route::post('boxes/{box}/components', [BoxComponentController::class, 'store'])->name('boxes.components.store');
        Route::post('component-borrowings', [ComponentBorrowingController::class, 'store'])->name('component-borrowings.store');
    });
    Route::middleware('permission:view-components')->group(function () {
        Route::get('components/export', [ComponentController::class, 'export'])->name('components.export');
        Route::get('components/template', [ComponentController::class, 'template'])->name('components.template');
        Route::get('boxes/export', [BoxController::class, 'export'])->name('boxes.export');
        Route::get('boxes', [BoxController::class, 'index'])->name('boxes.index');
        Route::get('boxes/print-labels', [BoxController::class, 'printLabels'])->name('boxes.print-labels');
        Route::get('boxes/{box}', [BoxController::class, 'show'])->name('boxes.show');
    });
    Route::middleware('permission:edit-components')->group(function () {
        Route::put('boxes/{box}', [BoxController::class, 'update'])->name('boxes.update');
        Route::post('component-borrowings/{borrowing}/return', [ComponentBorrowingController::class, 'returnBorrowing'])->name('component-borrowings.return');
        // Peminjaman box manual (edit) - HANYA ADMIN/SUPERADMIN
        Route::put('/box-usages/{usage}', [AdminBoxUsageController::class, 'update'])
            ->middleware('role:admin|superadmin')
            ->name('box-usages.update');
    });
    Route::middleware('permission:delete-components')->group(function () {
        Route::delete('components/bulk-destroy', [ComponentController::class, 'bulkDestroy'])->name('components.bulk-destroy');
        Route::delete('boxes/{box}', [BoxController::class, 'destroy'])->name('boxes.destroy');
        Route::delete('boxes/{box}/components/{boxComponent}', [BoxComponentController::class, 'destroy'])->name('boxes.components.destroy');
        // Peminjaman box manual (hapus) - HANYA ADMIN/SUPERADMIN
        Route::delete('/box-usages/{usage}', [AdminBoxUsageController::class, 'destroy'])
            ->middleware('role:admin|superadmin')
            ->name('box-usages.destroy');
    });
    // Peminjaman box manual (tambah) - HANYA ADMIN/SUPERADMIN
    Route::post('/box-usages', [AdminBoxUsageController::class, 'store'])
        ->middleware('permission:create-components')
        ->middleware('role:admin|superadmin')
        ->name('box-usages.store');
    Route::resource('components', ComponentController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-components')
        ->middlewareFor(['create', 'store'], 'permission:create-components')
        ->middlewareFor(['edit', 'update'], 'permission:edit-components')
        ->middlewareFor(['destroy'], 'permission:delete-components');

    // Reports (Admin + Laboran)
    Route::middleware('permission:view-reports')->group(function () {
        Route::get('/reports/card-control', [ComputerController::class, 'reportCardControl'])->name('reports.card-control');
        Route::get('/reports/card-control/print', [ComputerController::class, 'reportCardControlPrint'])->name('reports.card-control-print');
    });

    // Computers (Admin + Laboran)
    Route::middleware('permission:view-computers')->group(function () {
        Route::get('/computers/export', [ComputerController::class, 'export'])->name('computers.export');
        Route::get('/computers/spec-export', [ComputerController::class, 'specExport'])->name('computers.spec-export');
        Route::get('/computers/spec-template', [ComputerController::class, 'specTemplate'])->name('computers.spec-template');
        Route::get('/computers/check-template', [ComputerController::class, 'checkTemplate'])->name('computers.check-template');

        Route::get('/computers/list', function (\Illuminate\Http\Request $request) {
            $query = \App\Models\Computer::select('id', 'code');
            if ($labId = $request->laboratory_id) {
                $query->where('laboratory_id', $labId);
            }
            return response()->json($query->orderBy('code')->get());
        })->name('computers.list');

        Route::get('/computers/qr-stiker', [ComputerController::class, 'qrStiker'])->name('computers.qr-stiker');
        Route::get('/computers/praktikum-labels', [ComputerController::class, 'praktikumLabels'])->name('computers.praktikum-labels');
        Route::get('/computers/{computer}/card/print', [ComputerController::class, 'cardPrint'])->name('computers.card-print');
    });
    Route::middleware('permission:create-computers')->group(function () {
        Route::get('/computers/generate', [ComputerController::class, 'generate'])->name('computers.generate');
        Route::post('/computers/generate', [ComputerController::class, 'storeGenerate'])->name('computers.store-generate');
    });
    Route::middleware('permission:edit-computers')->group(function () {
        Route::get('/computers/bulk-assign', [ComputerController::class, 'bulkAssign'])->name('computers.bulk-assign');
        Route::post('/computers/bulk-assign', [ComputerController::class, 'storeBulkAssign'])->name('computers.store-bulk-assign');
        Route::post('/computers/{computer}/check', [ComputerController::class, 'storeCheck'])->name('computers.check');
        // Import spesifikasi mengubah data komputer, sehingga memakai izin edit
        Route::get('/computers/spec-import', [ComputerController::class, 'specImport'])->name('computers.spec-import');
        Route::post('/computers/spec-import', [ComputerController::class, 'storeSpecImport'])->name('computers.store-spec-import');
        // Import kartu kendali historis menambah/memperbarui data pengecekan
        Route::get('/computers/check-import', [ComputerController::class, 'checkImport'])->name('computers.check-import');
        Route::post('/computers/check-import', [ComputerController::class, 'storeCheckImport'])->name('computers.store-check-import');
        // Saklar tampil/sembunyi tombol "Lihat Spesifikasi" di halaman beranda publik
        Route::patch('/settings/public-spec', [SettingController::class, 'togglePublicSpec'])->name('settings.public-spec');
        // URL jadwal real-time yang ditautkan dari halaman jadwal publik
        Route::post('/settings/realtime-schedule-url', [SettingController::class, 'updateRealtimeScheduleUrl'])->name('settings.realtime-schedule-url');
    });
    Route::middleware('permission:delete-computers')->group(function () {
        Route::delete('/computers/bulk-destroy', [ComputerController::class, 'bulkDestroy'])->name('computers.bulk-destroy');
    });
    Route::resource('computers', ComputerController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-computers')
        ->middlewareFor(['create', 'store'], 'permission:create-computers')
        ->middlewareFor(['edit', 'update'], 'permission:edit-computers')
        ->middlewareFor(['destroy'], 'permission:delete-computers');

    // Maintenance (Admin + Laboran)
    Route::middleware('permission:view-maintenance')->group(function () {
        Route::get('maintenance/export', [MaintenanceController::class, 'export'])->name('maintenance.export');
        Route::get('device-checks/export', [DeviceCheckController::class, 'export'])->name('device-checks.export');
        Route::get('maintenance/{maintenance}/print', [MaintenanceController::class, 'print'])->name('maintenance.print');
        Route::get('device-checks/report', [DeviceCheckController::class, 'report'])->name('device-checks.report');
        Route::get('device-checks/report/print', [DeviceCheckController::class, 'reportPrint'])->name('device-checks.report-print');
        Route::get('device-checks/{deviceCheck}/print', [DeviceCheckController::class, 'print'])->name('device-checks.print');
    });
    Route::middleware('permission:edit-maintenance')->group(function () {
        Route::post('maintenance/{maintenance}/computer/{computer}', [MaintenanceController::class, 'saveComputer'])->name('maintenance.save-computer');
    });
    Route::resource('maintenance', MaintenanceController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-maintenance')
        ->middlewareFor(['create', 'store'], 'permission:create-maintenance')
        ->middlewareFor(['edit', 'update'], 'permission:edit-maintenance')
        ->middlewareFor(['destroy'], 'permission:delete-maintenance');
    // Pengecekan perangkat (matriks komputer × item perangkat per lab & periode)
    Route::resource('device-checks', DeviceCheckController::class)
        ->parameters(['device-checks' => 'deviceCheck'])
        ->middlewareFor(['index', 'show'], 'permission:view-maintenance')
        ->middlewareFor(['create', 'store'], 'permission:create-maintenance')
        ->middlewareFor(['edit', 'update'], 'permission:edit-maintenance')
        ->middlewareFor(['destroy'], 'permission:delete-maintenance');

    // Tickets (Admin + Laboran)
    Route::middleware('permission:view-tickets')->group(function () {
        Route::get('tickets/export', [TicketController::class, 'export'])->name('tickets.export');
    });
    Route::middleware('permission:edit-tickets')->group(function () {
        Route::patch('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.update-status');
        Route::post('tickets/{ticket}/comments', [TicketController::class, 'addComment'])->name('tickets.add-comment');
        Route::delete('tickets/{ticket}/comments/{comment}', [TicketController::class, 'deleteComment'])->name('tickets.delete-comment');
    });
    Route::middleware('permission:delete-tickets')->group(function () {
        Route::delete('tickets/bulk-destroy', [TicketController::class, 'bulkDestroy'])->name('tickets.bulk-destroy');
    });
    Route::resource('tickets', TicketController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-tickets')
        ->middlewareFor(['create', 'store'], 'permission:create-tickets')
        ->middlewareFor(['edit', 'update'], 'permission:edit-tickets')
        ->middlewareFor(['destroy'], 'permission:delete-tickets');

    // Peminjaman Komputer (Admin + Laboran)
    Route::middleware('permission:view-borrowings')->group(function () {
        Route::get('/borrowings/export', [AdminBorrowingController::class, 'export'])->name('borrowings.export');
        Route::get('/borrowings', [AdminBorrowingController::class, 'index'])->name('borrowings.index');
    });
    Route::middleware('permission:edit-borrowings')->group(function () {
        Route::patch('/borrowings/{borrowing}/status', [AdminBorrowingController::class, 'updateStatus'])->name('borrowings.update-status');
        Route::patch('/borrowings/{borrowing}/mark-returned', [AdminBorrowingController::class, 'markReturned'])->name('borrowings.mark-returned');
    });

    // Penggunaan Laboratorium - check-in via QR, validasi keluar oleh admin/laboran
    Route::middleware('permission:view-lab-usages')->group(function () {
        Route::get('/lab-usages/export', [AdminLabUsageController::class, 'export'])->name('lab-usages.export');
        Route::get('/lab-usages/qr-stiker', [AdminLabUsageController::class, 'qrStiker'])->name('lab-usages.qr-stiker');
        Route::get('/lab-usages', [AdminLabUsageController::class, 'index'])->name('lab-usages.index');
    });
    Route::middleware('permission:edit-lab-usages')->group(function () {
        Route::post('/lab-usages/{usage}/validate-out', [AdminLabUsageController::class, 'validateOut'])->name('lab-usages.validate-out');
        // Input manual (edit) - HANYA ADMIN/SUPERADMIN
        Route::put('/lab-usages/{usage}', [AdminLabUsageController::class, 'update'])
            ->middleware('role:admin|superadmin')
            ->name('lab-usages.update');
    });
    // Input manual (tambah) - HANYA ADMIN/SUPERADMIN
    Route::post('/lab-usages', [AdminLabUsageController::class, 'store'])
        ->middleware('permission:create-lab-usages')
        ->middleware('role:admin|superadmin')
        ->name('lab-usages.store');
    // Input manual (hapus) - HANYA ADMIN/SUPERADMIN
    Route::delete('/lab-usages/{usage}', [AdminLabUsageController::class, 'destroy'])
        ->middleware('permission:delete-lab-usages')
        ->middleware('role:admin|superadmin')
        ->name('lab-usages.destroy');

    // Jadwal Penggunaan Laboratorium (Admin only)
    Route::middleware('permission:view-lab-schedules')->group(function () {
        Route::get('lab-schedules/export', [LabScheduleController::class, 'export'])->name('lab-schedules.export');
    });
    Route::middleware('permission:create-lab-schedules')->group(function () {
        Route::post('lab-schedules/import', [LabScheduleController::class, 'import'])->name('lab-schedules.import');
    });
    Route::middleware('permission:edit-lab-schedules')->group(function () {
        // Drag & drop: pindah/tukar slot jadwal.
        Route::post('lab-schedules/{schedule}/move', [LabScheduleController::class, 'move'])->name('lab-schedules.move');
        // Sembunyikan/tampilkan satu entri jadwal di halaman publik /jadwal-lab.
        Route::patch('lab-schedules/{schedule}/visibility', [LabScheduleController::class, 'toggleVisibility'])->name('lab-schedules.toggle-visibility');
    });
    // show dipakai modal Edit (fetch JSON detail jadwal).
    // parameters(): nama param route diubah ke "schedule" agar cocok dengan
    // signature controller (show/update/destroy), bukan model kosong dari DI.
    Route::resource('lab-schedules', LabScheduleController::class)
        ->parameters(['lab-schedules' => 'schedule'])
        ->except(['create', 'edit'])
        ->middlewareFor(['index', 'show'], 'permission:view-lab-schedules')
        ->middlewareFor(['store'], 'permission:create-lab-schedules')
        ->middlewareFor(['update'], 'permission:edit-lab-schedules')
        ->middlewareFor(['destroy'], 'permission:delete-lab-schedules');

    // Backup & Restore Data — per aksi: view/edit/delete-backups
    Route::middleware('permission:view-backups')->group(function () {
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::get('backups/download', [BackupController::class, 'download'])->name('backups.download');
        Route::get('backups/files/{filename}', [BackupController::class, 'downloadFile'])->name('backups.files.download');
    });
    Route::middleware('permission:edit-backups')->group(function () {
        Route::post('backups/restore', [BackupController::class, 'restore'])->name('backups.restore');
    });
    Route::middleware('permission:delete-backups')->group(function () {
        Route::delete('backups/files/{filename}', [BackupController::class, 'destroyFile'])->name('backups.files.destroy');
    });

    // Pengembalian Box — HANYA ADMIN/SUPERADMIN
    Route::middleware('role:admin|superadmin')->group(function () {
        Route::post('box-scan/return/{boxUsageId}', [BoxScanController::class, 'returnBox'])
            ->name('box-scan.return');
        Route::post('box-scan/multi-return', [BoxScanController::class, 'multiReturn'])
            ->name('box-scan.multi-return');
        Route::get('box-scan/{boxCode}/active-usages', [BoxScanController::class, 'activeUsagesByNim'])
            ->name('box-scan.active-usages');
    });
});
