<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi in-place dari RBAC custom (tabel user_role / role_permission)
 * ke skema spatie/laravel-permission (model_has_roles / role_has_permissions)
 * tanpa kehilangan data. Kolom `label` lama dipertahankan karena dipakai UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah guard_name pada roles & permissions (default 'web').
        foreach (['roles', 'permissions'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'guard_name')) {
                Schema::table($table, function (Blueprint $t): void {
                    $t->string('guard_name')->default('web');
                });
                DB::table($table)->update(['guard_name' => 'web']);
            }
        }

        // 2. Rename pivot ke nama baku spatie.
        if (Schema::hasTable('role_permission') && ! Schema::hasTable('role_has_permissions')) {
            Schema::rename('role_permission', 'role_has_permissions');
        }
        if (Schema::hasTable('user_role') && ! Schema::hasTable('model_has_roles')) {
            Schema::rename('user_role', 'model_has_roles');
        }

        // 3. user_role (user_id, role_id) -> model_has_roles (model_id, model_type, role_id).
        if (Schema::hasTable('model_has_roles')) {
            if (Schema::hasColumn('model_has_roles', 'user_id')) {
                Schema::table('model_has_roles', function (Blueprint $t): void {
                    $t->renameColumn('user_id', 'model_id');
                });
            }
            if (! Schema::hasColumn('model_has_roles', 'model_type')) {
                Schema::table('model_has_roles', function (Blueprint $t): void {
                    $t->string('model_type')->default('App\Models\User');
                });
                DB::table('model_has_roles')->update(['model_type' => 'App\Models\User']);
            }
        }

        // 4. Tabel permission-per-user (belum pernah ada; kosong di awal).
        if (! Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $t): void {
                $t->uuid('permission_id');
                $t->uuid('model_id');
                $t->string('model_type');
                $t->primary(['permission_id', 'model_id', 'model_type']);
                $t->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            });
        }

        // Migrasi memakai DB mentah: flush cache permission spatie.
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        if (Schema::hasTable('model_has_permissions')) {
            Schema::dropIfExists('model_has_permissions');
        }

        if (Schema::hasTable('model_has_roles')) {
            if (Schema::hasColumn('model_has_roles', 'model_type')) {
                Schema::table('model_has_roles', function (Blueprint $t): void {
                    $t->dropColumn('model_type');
                });
            }
            if (Schema::hasColumn('model_has_roles', 'model_id')) {
                Schema::table('model_has_roles', function (Blueprint $t): void {
                    $t->renameColumn('model_id', 'user_id');
                });
            }
            if (! Schema::hasTable('user_role')) {
                Schema::rename('model_has_roles', 'user_role');
            }
        }

        if (Schema::hasTable('role_has_permissions') && ! Schema::hasTable('role_permission')) {
            Schema::rename('role_has_permissions', 'role_permission');
        }

        foreach (['permissions', 'roles'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'guard_name')) {
                Schema::table($table, function (Blueprint $t): void {
                    $t->dropColumn('guard_name');
                });
            }
        }
    }
};
