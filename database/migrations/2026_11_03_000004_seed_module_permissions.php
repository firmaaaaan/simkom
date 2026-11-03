<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Sumber tunggal daftar permission per modul.
 *
 * Sebelumnya daftar ini dibuat oleh RoleSeeder; sekarang dibuat lewat migrasi
 * supaya "php artisan migrate" sudah cukup tanpa db:seed. RoleSeeder kini
 * hanya menyusun role dan memberikan permission yang sudah ada.
 *
 * Modul baru cukup ditambahkan ke MODULES di sini.
 */
return new class extends Migration
{
    /** modul => [label, aksi yang tersedia]. */
    private const MODULES = [
        'users' => ['User', ['view', 'create', 'edit', 'delete', 'export']],
        'roles' => ['Role', ['view', 'create', 'edit', 'delete', 'export']],
        'laboratories' => ['Laboratorium', ['view', 'create', 'edit', 'delete', 'export']],
        'academic-years' => ['Tahun Ajaran', ['view', 'create', 'edit', 'delete', 'export']],
        'hardware' => ['Hardware', ['view', 'create', 'edit', 'delete', 'export', 'import']],
        'software' => ['Software', ['view', 'create', 'edit', 'delete', 'export', 'import']],
        'license-key' => ['Product Key Lisensi', ['view']],
        'components' => ['Komponen', ['view', 'create', 'edit', 'delete', 'export', 'import', 'print']],
        'computers' => ['Komputer', ['view', 'create', 'edit', 'delete', 'export', 'import', 'print']],
        'maintenance' => ['Pemeliharaan', ['view', 'create', 'edit', 'delete', 'export', 'import', 'print']],
        'tickets' => ['Kendala Praktikum', ['view', 'create', 'edit', 'delete', 'export']],
        'borrowings' => ['Peminjaman', ['view', 'edit', 'export']],
        'pengadaan' => ['Pengadaan', ['view', 'create', 'edit', 'delete']],
        'incident-reports' => ['Laporan Insiden', ['view', 'create', 'edit', 'delete']],
        'lab-usages' => ['Penggunaan Lab', ['view', 'create', 'edit', 'delete', 'export', 'print']],
        'backups' => ['Backup', ['view', 'edit', 'delete']],
        'reports' => ['Laporan', ['view', 'print']],
        'signatures' => ['Tanda Tangan Laporan', ['view', 'create', 'edit', 'delete']],
        'monitoring' => ['Monitoring PC', ['view', 'control']],
        'todos' => ['To-Do List', ['view', 'create', 'edit', 'delete']],
    ];

    private const ACTION_LABELS = [
        'view' => 'Lihat',
        'create' => 'Tambah',
        'edit' => 'Ubah',
        'delete' => 'Hapus',
        'export' => 'Export',
        'import' => 'Import',
        'print' => 'Cetak',
        'control' => 'Kendalikan',
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::MODULES as $module => [$label, $actions]) {
            foreach ($actions as $action) {
                $name = "{$action}-{$module}";

                if (DB::table('permissions')->where('name', $name)->exists()) {
                    continue;
                }

                DB::table('permissions')->insert([
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'label' => self::ACTION_LABELS[$action].' '.$label,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Sengaja tidak menghapus permission: daftar ini sudah ada sebelum
        // migrasi dibuat (dulu dibuat RoleSeeder) dan mungkin sudah dipakai role.
    }
};
