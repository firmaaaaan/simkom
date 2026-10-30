<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\Admin\BorrowingController as AdminBorrowingController;
use App\Http\Controllers\Admin\BoxUsageController as AdminBoxUsageController;
use App\Http\Controllers\Admin\LabUsageController as AdminLabUsageController;
use App\Http\Controllers\AkademikJadwalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\BoxComponentController;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\BoxScanController;
use App\Http\Controllers\ComponentBorrowingController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\ComputerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceCheckController;
use App\Http\Controllers\HardwareController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\LabScanController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MonitoringPcController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PengadaanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicComputerCardController;
use App\Http\Controllers\PublicSpecController;
use App\Http\Controllers\PublicTicketController;
use App\Http\Controllers\PublicTrackerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\SoftwareController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TodoColumnController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\TodoLabelController;
use App\Http\Controllers\TodoTaskController;
use App\Http\Controllers\UserController;
use App\Models\Computer;
use App\Models\Laboratory;
use App\Models\PcMonitor;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Halaman pertama (landing) = Jadwal Laboratorium publik.
Route::get('/', fn () => redirect()->route('jadwal.index'));

// Daftar Komputer publik (halaman welcome lama) — pindah dari '/'.
Route::get('/daftar-komputer', function (Request $request) {
    $laboratories = Laboratory::where('status', 'Aktif')
        ->where('show_in_computer_list', true)
        ->orderBy('name')
        ->get();
    $showSpec = Setting::publicSpecEnabled();

    $selectedLabId = $request->laboratory_id;
    $selectedLab = null;
    if ($selectedLabId !== null && $selectedLabId !== '') {
        $selectedLab = $laboratories->firstWhere('id', $selectedLabId);
    }
    if (! $selectedLab && $laboratories->isNotEmpty()) {
        $selectedLab = $laboratories->first();
    }

    $computers = collect();
    $detectedByCode = [];
    if ($selectedLab) {
        $computers = Computer::where('laboratory_id', $selectedLab->id)
            ->with('laboratory')
            ->withCount(['tickets' => function ($q) {
                $q->whereIn('status', ['Open', 'In Progress']);
            }])
            ->orderBy('code')
            ->get();

        // Software terdeteksi agent: hostname = kode komputer (case-insensitive).
        if ($showSpec && $computers->isNotEmpty()) {
            $codesLower = $computers->pluck('code')->map(fn ($code) => strtolower($code))->all();
            $whitelistLower = array_map('strtolower', Setting::publicSoftwareWhitelist());
            $monitors = PcMonitor::whereIn(DB::raw('LOWER(hostname)'), $codesLower)
                ->with(['softwares' => function ($q) use ($whitelistLower) {
                    $q->whereIn(DB::raw('LOWER(name)'), $whitelistLower);
                }])
                ->get();
            foreach ($monitors as $monitor) {
                $detectedByCode[strtolower($monitor->hostname)] = [
                    'synced_at' => $monitor->software_synced_at,
                    'items' => $monitor->softwares,
                ];
            }
        }
    }

    return view('welcome', compact('laboratories', 'computers', 'selectedLab', 'showSpec', 'detectedByCode'));
})->name('daftar-komputer');

// Auth Routes (guest only)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public Routes (Lapor Kendala - tanpa login)
Route::prefix('lapor-kendala')->name('lapor-kendala.')->group(function () {
    Route::get('/', [PublicTicketController::class, 'index'])->name('index');
    Route::get('/computer/{computer}', [PublicTicketController::class, 'create'])->name('create');
    Route::post('/store', [PublicTicketController::class, 'store'])->name('store');
    Route::get('/track', fn () => redirect()->route('track.index'))->name('track-form');
    Route::get('/track/{tracking_code}', fn ($tracking_code) => redirect()->route('track.search', ['tracking_code' => $tracking_code]))->name('track');
});

// Public Routes (Pinjam Komputer - tanpa login)
Route::prefix('pinjam-komputer')->name('pinjam-komputer.')->group(function () {
    Route::get('/computer/{computer}', [BorrowController::class, 'create'])->name('create');
    Route::post('/store', [BorrowController::class, 'store'])->name('store');
    Route::get('/track', fn () => redirect()->route('track.index'))->name('track-form');
    Route::get('/track/{tracking_code}', fn ($tracking_code) => redirect()->route('track.search', ['tracking_code' => $tracking_code]))->name('track');
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

// Public Routes (Jadwal Laboratorium - tanpa login, read-only)
// Jadwal mingguan dari API eksternal (SIMPTT) — publik, tanpa login.
Route::get('/jadwal', [AkademikJadwalController::class, 'publik'])->name('jadwal.index');

// Public Routes (Kartu Kendali - tanpa login, read-only riwayat pengecekan)
Route::get('/kartu/{computer}', [PublicComputerCardController::class, 'show'])->name('kartu.show');

// Public Routes (Rincian Spesifikasi per Laboratorium - tanpa login, read-only)
Route::get('/spesifikasi/{laboratory}', [PublicSpecController::class, 'show'])->name('public.spec');

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

    // Akademik — Jadwal Laboratorium dari API eksternal (SIMPTT), versi admin
    Route::get('/akademik/jadwal', [AkademikJadwalController::class, 'index'])->name('akademik.jadwal');

    // Halaman semua notifikasi + hapus (global, semua user login)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // To-Do List (Kanban pribadi) — per aksi: view/create/edit/delete-todos
    Route::middleware('permission:view-todos')->group(function () {
        Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');
        Route::get('/todos/tasks/{task}/detail', [TodoTaskController::class, 'detail'])->name('todos.tasks.detail');
        Route::get('/todos/attachments/{attachment}/download', [TodoTaskController::class, 'downloadAttachment'])->name('todos.attachments.download');
    });
    Route::middleware('permission:create-todos')->group(function () {
        Route::post('/todos/columns', [TodoColumnController::class, 'store'])->name('todos.columns.store');
        Route::post('/todos/quick-add', [TodoTaskController::class, 'quickAdd'])->name('todos.quick-add');
        Route::post('/todos/labels', [TodoLabelController::class, 'store'])->name('todos.labels.store');
        Route::post('/todos/tasks/{task}/checklist', [TodoTaskController::class, 'storeChecklistItem'])->name('todos.tasks.checklist.store');
        Route::post('/todos/tasks/{task}/attachments', [TodoTaskController::class, 'storeAttachment'])->name('todos.tasks.attachments.store');
        Route::post('/todos/tasks/{task}/comments', [TodoTaskController::class, 'storeComment'])->name('todos.tasks.comments.store');
    });
    Route::middleware('permission:edit-todos')->group(function () {
        Route::patch('/todos/columns/reorder', [TodoColumnController::class, 'reorder'])->name('todos.columns.reorder');
        Route::put('/todos/columns/{id}', [TodoColumnController::class, 'update'])->name('todos.columns.update');
        Route::put('/todos/tasks/{task}', [TodoTaskController::class, 'update'])->name('todos.tasks.update');
        Route::patch('/todos/tasks/{task}/move', [TodoTaskController::class, 'move'])->name('todos.tasks.move');
        Route::patch('/todos/tasks/{task}/labels', [TodoTaskController::class, 'syncLabels'])->name('todos.tasks.labels');
        Route::patch('/todos/checklist-items/{item}', [TodoTaskController::class, 'toggleChecklistItem'])->name('todos.checklist.toggle');
        Route::put('/todos/labels/{id}', [TodoLabelController::class, 'update'])->name('todos.labels.update');
    });
    Route::middleware('permission:delete-todos')->group(function () {
        Route::delete('/todos/columns/{id}', [TodoColumnController::class, 'destroy'])->name('todos.columns.destroy');
        Route::delete('/todos/tasks/{task}', [TodoTaskController::class, 'destroy'])->name('todos.tasks.destroy');
        Route::delete('/todos/checklist-items/{item}', [TodoTaskController::class, 'destroyChecklistItem'])->name('todos.checklist.destroy');
        Route::delete('/todos/attachments/{attachment}', [TodoTaskController::class, 'destroyAttachment'])->name('todos.attachments.destroy');
        Route::delete('/todos/comments/{comment}', [TodoTaskController::class, 'destroyComment'])->name('todos.comments.destroy');
        Route::delete('/todos/labels/{id}', [TodoLabelController::class, 'destroy'])->name('todos.labels.destroy');
    });

    // User Management — per aksi: view/create/edit/delete-users
    Route::get('users/export', [UserController::class, 'export'])
        ->middleware('permission:export-users')
        ->name('users.export');
    Route::resource('users', UserController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-users')
        ->middlewareFor(['create', 'store'], 'permission:create-users')
        ->middlewareFor(['edit', 'update'], 'permission:edit-users')
        ->middlewareFor(['destroy'], 'permission:delete-users');

    // Role Management — per aksi: view/create/edit/delete-roles
    Route::get('roles/export', [RoleController::class, 'export'])
        ->middleware('permission:export-roles')
        ->name('roles.export');
    Route::resource('roles', RoleController::class)
        ->except(['show'])
        ->middlewareFor(['index'], 'permission:view-roles')
        ->middlewareFor(['create', 'store'], 'permission:create-roles')
        ->middlewareFor(['edit', 'update'], 'permission:edit-roles')
        ->middlewareFor(['destroy'], 'permission:delete-roles');

    // Data Master - Admin only
    Route::get('laboratories/export', [LaboratoryController::class, 'export'])
        ->middleware('permission:export-laboratories')
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
        ->middleware('permission:export-academic-years')
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
    Route::middleware('permission:import-hardware')->group(function () {
        Route::get('hardware/import', [HardwareController::class, 'import'])->name('hardware.import');
        Route::post('hardware/import', [HardwareController::class, 'storeImport'])->name('hardware.store-import');
    });
    Route::middleware('permission:export-hardware')->group(function () {
        Route::get('hardware/export', [HardwareController::class, 'export'])->name('hardware.export');
    });
    // Template dipakai proses import juga, jadi boleh export ATAU import.
    Route::get('hardware/template', [HardwareController::class, 'template'])
        ->middleware('permission:export-hardware|import-hardware')
        ->name('hardware.template');
    Route::delete('hardware/bulk-destroy', [HardwareController::class, 'bulkDestroy'])
        ->middleware('permission:delete-hardware')
        ->name('hardware.bulk-destroy');
    Route::resource('hardware', HardwareController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-hardware')
        ->middlewareFor(['create', 'store'], 'permission:create-hardware')
        ->middlewareFor(['edit', 'update'], 'permission:edit-hardware')
        ->middlewareFor(['destroy'], 'permission:delete-hardware');

    // Software (Admin + Laboran)
    Route::middleware('permission:import-software')->group(function () {
        Route::get('software/import', [SoftwareController::class, 'import'])->name('software.import');
        Route::post('software/import', [SoftwareController::class, 'storeImport'])->name('software.store-import');
    });
    Route::middleware('permission:export-software')->group(function () {
        Route::get('software/export', [SoftwareController::class, 'export'])->name('software.export');
    });
    // Template dipakai proses import juga, jadi boleh export ATAU import.
    Route::get('software/template', [SoftwareController::class, 'template'])
        ->middleware('permission:export-software|import-software')
        ->name('software.template');
    Route::delete('software/bulk-destroy', [SoftwareController::class, 'bulkDestroy'])
        ->middleware('permission:delete-software')
        ->name('software.bulk-destroy');
    Route::resource('software', SoftwareController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-software')
        ->middlewareFor(['create', 'store'], 'permission:create-software')
        ->middlewareFor(['edit', 'update'], 'permission:edit-software')
        ->middlewareFor(['destroy'], 'permission:delete-software');

    // Components (Admin + Laboran)
    Route::middleware('permission:import-components')->group(function () {
        Route::get('components/import', [ComponentController::class, 'import'])->name('components.import');
        Route::post('components/import', [ComponentController::class, 'storeImport'])->name('components.store-import');
    });
    Route::middleware('permission:create-components')->group(function () {
        Route::post('boxes', [BoxController::class, 'store'])->name('boxes.store');
        Route::post('boxes/bulk-add-component', [BoxController::class, 'bulkAddComponent'])->name('boxes.bulk-add-component');
        Route::post('boxes/{box}/components', [BoxComponentController::class, 'store'])->name('boxes.components.store');
        Route::post('component-borrowings', [ComponentBorrowingController::class, 'store'])->name('component-borrowings.store');
    });
    // Route export/cetak boxes harus didefinisikan SEBELUM boxes/{box}
    // supaya "boxes/export" tidak tertangkap route show.
    Route::middleware('permission:export-components')->group(function () {
        Route::get('components/export', [ComponentController::class, 'export'])->name('components.export');
        Route::get('boxes/export', [BoxController::class, 'export'])->name('boxes.export');
        Route::get('boxes/used-export', [BoxController::class, 'exportUsedBoxes'])->name('boxes.export-used');
    });
    // Template dipakai proses import juga, jadi boleh export ATAU import.
    Route::get('components/template', [ComponentController::class, 'template'])
        ->middleware('permission:export-components|import-components')
        ->name('components.template');
    Route::get('boxes/print-labels', [BoxController::class, 'printLabels'])
        ->middleware('permission:print-components')
        ->name('boxes.print-labels');
    Route::middleware('permission:view-components')->group(function () {
        Route::get('boxes', [BoxController::class, 'index'])->name('boxes.index');
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
    // Laporan kerusakan komponen dalam pemakaian box - ADMIN/LABORAN
    Route::post('/box-usages/{usage}/damages', [AdminBoxUsageController::class, 'storeDamage'])
        ->middleware('permission:edit-components')
        ->name('box-usages.store-damage');
    Route::resource('components', ComponentController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-components')
        ->middlewareFor(['create', 'store'], 'permission:create-components')
        ->middlewareFor(['edit', 'update'], 'permission:edit-components')
        ->middlewareFor(['destroy'], 'permission:delete-components');

    // Reports (Admin + Laboran)
    Route::middleware('permission:view-reports')->group(function () {
        Route::get('/reports/card-control', [ComputerController::class, 'reportCardControl'])->name('reports.card-control');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/laporan', [ReportController::class, 'index'])->name('reports.laporan');
    });
    Route::get('/reports/inventory/print', [ReportController::class, 'inventoryPrint'])
        ->middleware('permission:print-reports')
        ->name('reports.inventory-print');
    Route::get('/reports/card-control/print', [ComputerController::class, 'reportCardControlPrint'])
        ->middleware('permission:print-reports')
        ->name('reports.card-control-print');
    Route::get('/reports/laporan/download', [ReportController::class, 'download'])
        ->middleware('permission:print-reports')
        ->name('reports.laporan-download');

    // Computers (Admin + Laboran)
    Route::middleware('permission:view-computers')->group(function () {
        Route::get('/computers/list', function (Request $request) {
            $query = Computer::select('id', 'code');
            if ($labId = $request->laboratory_id) {
                $query->where('laboratory_id', $labId);
            }

            return response()->json($query->orderBy('code')->get());
        })->name('computers.list');
    });
    Route::middleware('permission:export-computers')->group(function () {
        Route::get('/computers/export', [ComputerController::class, 'export'])->name('computers.export');
        Route::get('/computers/spec-export', [ComputerController::class, 'specExport'])->name('computers.spec-export');
    });
    // Template dipakai proses import juga, jadi boleh export ATAU import.
    Route::get('/computers/spec-template', [ComputerController::class, 'specTemplate'])
        ->middleware('permission:export-computers|import-computers')
        ->name('computers.spec-template');
    Route::get('/computers/check-template', [ComputerController::class, 'checkTemplate'])
        ->middleware('permission:export-computers|import-computers')
        ->name('computers.check-template');

    Route::middleware('permission:print-computers')->group(function () {
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
        // Pindai barcode inventaris lama: pilih komputer, pindai via kamera / scanner pistol
        Route::get('/computers/barcode-scan', [ComputerController::class, 'barcodeScan'])->name('computers.barcode-scan');
        Route::post('/computers/barcode-scan', [ComputerController::class, 'barcodeStore'])->name('computers.barcode-store');
        // Saklar tampil/sembunyi tombol "Lihat Software" di halaman beranda publik
        Route::patch('/settings/public-spec', [SettingController::class, 'togglePublicSpec'])->name('settings.public-spec');
        // Whitelist software yang tampil di halaman beranda publik
        Route::get('/settings/public-software', [SettingController::class, 'editPublicSoftware'])->name('settings.public-software.edit');
        Route::put('/settings/public-software', [SettingController::class, 'updatePublicSoftware'])->name('settings.public-software.update');
    });
    // Pengaturan TTD laporan
    Route::middleware('permission:view-signatures|create-signatures|edit-signatures|delete-signatures')->group(function () {
        Route::get('/settings/signatures', [SignatureController::class, 'index'])->name('settings.signatures.index');
    });
    Route::middleware('permission:create-signatures|edit-signatures')->group(function () {
        Route::get('/settings/signatures/create', [SignatureController::class, 'create'])->name('settings.signatures.create');
        Route::post('/settings/signatures', [SignatureController::class, 'store'])->name('settings.signatures.store');
    });
    Route::middleware('permission:edit-signatures')->group(function () {
        Route::get('/settings/signatures/{signature}/edit', [SignatureController::class, 'edit'])->name('settings.signatures.edit');
        Route::put('/settings/signatures/{signature}', [SignatureController::class, 'update'])->name('settings.signatures.update');
    });
    Route::middleware('permission:delete-signatures|edit-signatures')->group(function () {
        Route::delete('/settings/signatures/{signature}', [SignatureController::class, 'destroy'])->name('settings.signatures.destroy');
    });
    Route::middleware('permission:import-computers')->group(function () {
        // Import spesifikasi mengubah data komputer (izin import terpisah dari edit)
        Route::get('/computers/spec-import', [ComputerController::class, 'specImport'])->name('computers.spec-import');
        Route::post('/computers/spec-import', [ComputerController::class, 'storeSpecImport'])->name('computers.store-spec-import');
        // Import kartu kendali historis menambah/memperbarui data pengecekan
        Route::get('/computers/check-import', [ComputerController::class, 'checkImport'])->name('computers.check-import');
        Route::post('/computers/check-import', [ComputerController::class, 'storeCheckImport'])->name('computers.store-check-import');
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
        Route::get('device-checks/report', [DeviceCheckController::class, 'report'])->name('device-checks.report');
    });
    Route::middleware('permission:export-maintenance')->group(function () {
        Route::get('maintenance/export', [MaintenanceController::class, 'export'])->name('maintenance.export');
        Route::get('device-checks/export', [DeviceCheckController::class, 'export'])->name('device-checks.export');
    });
    Route::middleware('permission:print-maintenance')->group(function () {
        Route::get('maintenance/print-all', [MaintenanceController::class, 'printAll'])->name('maintenance.print-all');
        Route::get('maintenance/{maintenance}/print', [MaintenanceController::class, 'print'])->name('maintenance.print');
        Route::get('device-checks/report/print', [DeviceCheckController::class, 'reportPrint'])->name('device-checks.report-print');
        Route::get('device-checks/{deviceCheck}/print', [DeviceCheckController::class, 'print'])->name('device-checks.print');
    });
    // Template dipakai proses import juga, jadi boleh export ATAU import.
    Route::get('device-checks/template', [DeviceCheckController::class, 'template'])
        ->middleware('permission:export-maintenance|import-maintenance')
        ->name('device-checks.template');
    Route::get('maintenance/template', [MaintenanceController::class, 'template'])
        ->middleware('permission:export-maintenance|import-maintenance')
        ->name('maintenance.template');
    Route::middleware('permission:edit-maintenance')->group(function () {
        Route::post('maintenance/{maintenance}/computer/{computer}', [MaintenanceController::class, 'saveComputer'])->name('maintenance.save-computer');
    });
    Route::middleware('permission:import-maintenance')->group(function () {
        // Import pengecekan perangkat mengubah data pengecekan (meniru pola import kartu kendali)
        Route::get('device-checks/import', [DeviceCheckController::class, 'import'])->name('device-checks.import');
        Route::post('device-checks/import', [DeviceCheckController::class, 'storeImport'])->name('device-checks.store-import');
        // Import pemeliharaan mengubah data checklist (meniru pola import pengecekan perangkat)
        Route::get('maintenance/import', [MaintenanceController::class, 'import'])->name('maintenance.import');
        Route::post('maintenance/import', [MaintenanceController::class, 'storeImport'])->name('maintenance.store-import');
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
    Route::get('tickets/export', [TicketController::class, 'export'])
        ->middleware('permission:export-tickets')
        ->name('tickets.export');
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

    // Pengadaan (Admin + Laboran)
    Route::middleware('permission:edit-pengadaan')->group(function () {
        Route::patch('pengadaan/{pengadaan}/status', [PengadaanController::class, 'updateStatus'])->name('pengadaan.update-status');
    });
    Route::middleware('permission:delete-pengadaan')->group(function () {
        Route::delete('pengadaan/bulk-destroy', [PengadaanController::class, 'bulkDestroy'])->name('pengadaan.bulk-destroy');
    });
    Route::resource('pengadaan', PengadaanController::class)
        ->middlewareFor(['index', 'show'], 'permission:view-pengadaan')
        ->middlewareFor(['create', 'store'], 'permission:create-pengadaan')
        ->middlewareFor(['edit', 'update'], 'permission:edit-pengadaan')
        ->middlewareFor(['destroy'], 'permission:delete-pengadaan');

    // Peminjaman Komputer (Admin + Laboran)
    Route::get('/borrowings/export', [AdminBorrowingController::class, 'export'])
        ->middleware('permission:export-borrowings')
        ->name('borrowings.export');
    Route::middleware('permission:view-borrowings')->group(function () {
        Route::get('/borrowings', [AdminBorrowingController::class, 'index'])->name('borrowings.index');
    });
    Route::middleware('permission:edit-borrowings')->group(function () {
        Route::patch('/borrowings/{borrowing}/status', [AdminBorrowingController::class, 'updateStatus'])->name('borrowings.update-status');
        Route::patch('/borrowings/{borrowing}/mark-returned', [AdminBorrowingController::class, 'markReturned'])->name('borrowings.mark-returned');
    });

    // Penggunaan Laboratorium - check-in via QR, validasi keluar oleh admin/laboran
    Route::middleware('permission:view-lab-usages')->group(function () {
        Route::get('/lab-usages', [AdminLabUsageController::class, 'index'])->name('lab-usages.index');
    });
    Route::get('/lab-usages/export', [AdminLabUsageController::class, 'export'])
        ->middleware('permission:export-lab-usages')
        ->name('lab-usages.export');
    Route::get('/lab-usages/qr-stiker', [AdminLabUsageController::class, 'qrStiker'])
        ->middleware('permission:print-lab-usages')
        ->name('lab-usages.qr-stiker');
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

    // Monitoring PC — view-monitoring untuk membuka dashboard & melihat
    // screenshot; aksi kendali (nyalakan/matikan/perintah agent) memakai
    // permission:control-monitoring.
    Route::middleware('permission:view-monitoring')->group(function () {
        Route::get('monitoring-pc', [MonitoringPcController::class, 'index'])->name('monitoring-pc.index');
        Route::get('monitoring-pc/data', [MonitoringPcController::class, 'data'])->name('monitoring-pc.data');
        Route::get('monitoring-pc/{pc}/screenshot', [MonitoringPcController::class, 'screenshot'])->name('monitoring-pc.screenshot');
        Route::get('monitoring-pc/{pc}/software', [MonitoringPcController::class, 'software'])->name('monitoring-pc.software');
    });
    Route::middleware('permission:control-monitoring')->group(function () {
        Route::post('monitoring-pc/{pc}/power-on', [MonitoringPcController::class, 'powerOn'])->name('monitoring-pc.power-on');
        Route::post('monitoring-pc/{pc}/command', [MonitoringPcController::class, 'command'])->name('monitoring-pc.command');
        Route::post('monitoring-pc/shutdown-all', [MonitoringPcController::class, 'shutdownAll'])->name('monitoring-pc.shutdown-all');
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
