<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoxScanController;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\BoxComponentController;
use App\Http\Controllers\ComputerController;
use App\Http\Controllers\DeviceCheckController;
use App\Http\Controllers\ComponentController;
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
use App\Http\Controllers\LabScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function (\Illuminate\Http\Request $request) {
    $laboratories = \App\Models\Laboratory::orderBy('name')->get();
    $selectedLabId = $request->laboratory_id;
    $computers = collect();
    $selectedLab = null;
    $showSpec = \App\Models\Setting::publicSpecEnabled();

    if ($selectedLabId) {
        $selectedLab = \App\Models\Laboratory::find($selectedLabId);
        $computers = \App\Models\Computer::where('laboratory_id', $selectedLabId)
            ->with($showSpec ? ['laboratory', 'hardware', 'software'] : ['laboratory'])
            ->withCount(['tickets' => function ($q) {
                $q->whereIn('status', ['Open', 'In Progress']);
            }])
            ->orderBy('code')
            ->get();
    } elseif ($laboratories->isNotEmpty()) {
        $selectedLab = $laboratories->first();
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

    // User Management (Admin only)
    Route::middleware('permission:manage-users')->group(function () {
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::resource('users', UserController::class);
    });

    // Role Management (Admin only)
    Route::middleware('permission:manage-roles')->group(function () {
        Route::get('roles/export', [RoleController::class, 'export'])->name('roles.export');
        Route::resource('roles', RoleController::class)->except(['show']);
    });

    // Data Master - Admin only
    Route::middleware('permission:manage-laboratories')->group(function () {
        Route::get('laboratories/export', [LaboratoryController::class, 'export'])->name('laboratories.export');
        Route::resource('laboratories', LaboratoryController::class);
        Route::delete('laboratories/bulk-destroy', [LaboratoryController::class, 'bulkDestroy'])->name('laboratories.bulk-destroy');
    });

    Route::middleware('permission:manage-academic-years')->group(function () {
        Route::get('academic-years/export', [AcademicYearController::class, 'export'])->name('academic-years.export');
        Route::resource('academic-years', AcademicYearController::class);
        Route::delete('academic-years/bulk-destroy', [AcademicYearController::class, 'bulkDestroy'])->name('academic-years.bulk-destroy');
    });

    // Hardware (Admin + Laboran)
    Route::middleware('permission:manage-hardware')->group(function () {
        Route::delete('hardware/bulk-destroy', [HardwareController::class, 'bulkDestroy'])->name('hardware.bulk-destroy');
        Route::get('hardware/export', [HardwareController::class, 'export'])->name('hardware.export');
        Route::get('hardware/template', [HardwareController::class, 'template'])->name('hardware.template');
        Route::get('hardware/import', [HardwareController::class, 'import'])->name('hardware.import');
        Route::post('hardware/import', [HardwareController::class, 'storeImport'])->name('hardware.store-import');
        Route::resource('hardware', HardwareController::class);
    });

    // Software (Admin + Laboran)
    Route::middleware('permission:manage-software')->group(function () {
        Route::delete('software/bulk-destroy', [SoftwareController::class, 'bulkDestroy'])->name('software.bulk-destroy');
        Route::get('software/export', [SoftwareController::class, 'export'])->name('software.export');
        Route::get('software/template', [SoftwareController::class, 'template'])->name('software.template');
        Route::get('software/import', [SoftwareController::class, 'import'])->name('software.import');
        Route::post('software/import', [SoftwareController::class, 'storeImport'])->name('software.store-import');
        Route::resource('software', SoftwareController::class);
    });

    // Components (Admin + Laboran)
    Route::middleware('permission:manage-components')->group(function () {
        Route::delete('components/bulk-destroy', [ComponentController::class, 'bulkDestroy'])->name('components.bulk-destroy');
        Route::get('components/export', [ComponentController::class, 'export'])->name('components.export');
        Route::get('components/template', [ComponentController::class, 'template'])->name('components.template');
        Route::get('components/import', [ComponentController::class, 'import'])->name('components.import');
        Route::post('components/import', [ComponentController::class, 'storeImport'])->name('components.store-import');
        Route::resource('components', ComponentController::class);

        // Box routes
        Route::get('boxes/export', [BoxController::class, 'export'])->name('boxes.export');
        Route::get('boxes', [BoxController::class, 'index'])->name('boxes.index');
        Route::get('boxes/print-labels', [BoxController::class, 'printLabels'])->name('boxes.print-labels');
        Route::post('boxes', [BoxController::class, 'store'])->name('boxes.store');
        Route::put('boxes/{box}', [BoxController::class, 'update'])->name('boxes.update');
        Route::get('boxes/{box}', [BoxController::class, 'show'])->name('boxes.show');
        Route::delete('boxes/{box}', [BoxController::class, 'destroy'])->name('boxes.destroy');
        Route::post('boxes/bulk-add-component', [BoxController::class, 'bulkAddComponent'])->name('boxes.bulk-add-component');

        // Box component routes
        Route::post('boxes/{box}/components', [BoxComponentController::class, 'store'])->name('boxes.components.store');
        Route::delete('boxes/{box}/components/{boxComponent}', [BoxComponentController::class, 'destroy'])->name('boxes.components.destroy');
    });

    // Reports (Admin + Laboran)
    Route::middleware('permission:view-reports')->group(function () {
        Route::get('/reports/card-control', [ComputerController::class, 'reportCardControl'])->name('reports.card-control');
        Route::get('/reports/card-control/print', [ComputerController::class, 'reportCardControlPrint'])->name('reports.card-control-print');
    });

    // Computers (Admin + Laboran)
    Route::middleware('permission:manage-computers')->group(function () {
        Route::get('/computers/export', [ComputerController::class, 'export'])->name('computers.export');

        Route::get('/computers/list', function (\Illuminate\Http\Request $request) {
            $query = \App\Models\Computer::select('id', 'code');
            if ($labId = $request->laboratory_id) {
                $query->where('laboratory_id', $labId);
            }
            return response()->json($query->orderBy('code')->get());
        })->name('computers.list');

        Route::get('/computers/generate', [ComputerController::class, 'generate'])->name('computers.generate');
        Route::post('/computers/generate', [ComputerController::class, 'storeGenerate'])->name('computers.store-generate');
        Route::get('/computers/qr-stiker', [ComputerController::class, 'qrStiker'])->name('computers.qr-stiker');
        Route::get('/computers/praktikum-labels', [ComputerController::class, 'praktikumLabels'])->name('computers.praktikum-labels');
        Route::delete('/computers/bulk-destroy', [ComputerController::class, 'bulkDestroy'])->name('computers.bulk-destroy');
        Route::get('/computers/bulk-assign', [ComputerController::class, 'bulkAssign'])->name('computers.bulk-assign');
        Route::post('/computers/bulk-assign', [ComputerController::class, 'storeBulkAssign'])->name('computers.store-bulk-assign');

        // Saklar tampil/sembunyi tombol "Lihat Spesifikasi" di halaman beranda publik
        Route::patch('/settings/public-spec', [SettingController::class, 'togglePublicSpec'])->name('settings.public-spec');
        // URL jadwal real-time yang ditautkan dari halaman jadwal publik
        Route::post('/settings/realtime-schedule-url', [SettingController::class, 'updateRealtimeScheduleUrl'])->name('settings.realtime-schedule-url');
        Route::get('/computers/{computer}/card/print', [ComputerController::class, 'cardPrint'])->name('computers.card-print');
        Route::post('/computers/{computer}/check', [ComputerController::class, 'storeCheck'])->name('computers.check');
        Route::resource('computers', ComputerController::class);
    });

    // Maintenance (Admin + Laboran)
    Route::middleware('permission:manage-maintenance')->group(function () {
        Route::get('maintenance/export', [MaintenanceController::class, 'export'])->name('maintenance.export');
        Route::get('device-checks/export', [DeviceCheckController::class, 'export'])->name('device-checks.export');
        Route::get('maintenance/{maintenance}/print', [MaintenanceController::class, 'print'])->name('maintenance.print');
        Route::post('maintenance/{maintenance}/computer/{computer}', [MaintenanceController::class, 'saveComputer'])->name('maintenance.save-computer');
        Route::resource('maintenance', MaintenanceController::class);

        // Pengecekan perangkat (matriks komputer × item perangkat per lab & periode)
        Route::get('device-checks/report', [DeviceCheckController::class, 'report'])->name('device-checks.report');
        Route::get('device-checks/report/print', [DeviceCheckController::class, 'reportPrint'])->name('device-checks.report-print');
        Route::get('device-checks/{deviceCheck}/print', [DeviceCheckController::class, 'print'])->name('device-checks.print');
        Route::resource('device-checks', DeviceCheckController::class)->parameters(['device-checks' => 'deviceCheck']);
    });

    // Tickets (Admin + Laboran)
    Route::middleware('permission:manage-tickets')->group(function () {
        Route::get('tickets/export', [TicketController::class, 'export'])->name('tickets.export');
        Route::delete('tickets/bulk-destroy', [TicketController::class, 'bulkDestroy'])->name('tickets.bulk-destroy');
        Route::patch('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.update-status');
        Route::post('tickets/{ticket}/comments', [TicketController::class, 'addComment'])->name('tickets.add-comment');
        Route::delete('tickets/{ticket}/comments/{comment}', [TicketController::class, 'deleteComment'])->name('tickets.delete-comment');
        Route::resource('tickets', TicketController::class);
    });

    // Peminjaman Komputer (Admin + Laboran)
    Route::middleware('permission:manage-borrowings')->group(function () {
        Route::get('/borrowings/export', [AdminBorrowingController::class, 'export'])->name('borrowings.export');
        Route::get('/borrowings', [AdminBorrowingController::class, 'index'])->name('borrowings.index');
        Route::patch('/borrowings/{borrowing}/status', [AdminBorrowingController::class, 'updateStatus'])->name('borrowings.update-status');
    });

    // Jadwal Penggunaan Laboratorium (Admin only)
    Route::middleware('permission:manage-lab-schedules')->group(function () {
        Route::get('lab-schedules/export', [LabScheduleController::class, 'export'])->name('lab-schedules.export');
        Route::post('lab-schedules/import', [LabScheduleController::class, 'import'])->name('lab-schedules.import');
        // Drag & drop: pindah/tukar slot jadwal.
        Route::post('lab-schedules/{schedule}/move', [LabScheduleController::class, 'move'])->name('lab-schedules.move');
        // show dipakai modal Edit (fetch JSON detail jadwal).
        // parameters(): nama param route diubah ke "schedule" agar cocok dengan
        // signature controller (show/update/destroy), bukan model kosong dari DI.
        Route::resource('lab-schedules', LabScheduleController::class)
            ->parameters(['lab-schedules' => 'schedule'])
            ->except(['create', 'edit']);
    });

    // Pengembalian Box — HANYA ADMIN
    Route::middleware('role:admin')->group(function () {
        Route::post('box-scan/return/{boxUsageId}', [BoxScanController::class, 'returnBox'])
            ->name('box-scan.return');
        Route::post('box-scan/multi-return', [BoxScanController::class, 'multiReturn'])
            ->name('box-scan.multi-return');
        Route::get('box-scan/{boxCode}/active-usages', [BoxScanController::class, 'activeUsagesByNim'])
            ->name('box-scan.active-usages');
    });
});
