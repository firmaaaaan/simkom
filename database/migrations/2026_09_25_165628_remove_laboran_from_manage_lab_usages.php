<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Nama pivot lama (custom) atau baru (spatie/laravel-permission). */
    private function pivotTable(): string
    {
        return Schema::hasTable('role_permission') ? 'role_permission' : 'role_has_permissions';
    }

    public function up(): void
    {
        $permission = DB::table('permissions')->where('name', 'manage-lab-usages')->first();
        $role = DB::table('roles')->where('name', 'laboran')->first();

        if ($permission && $role) {
            DB::table($this->pivotTable())
                ->where('permission_id', $permission->id)
                ->where('role_id', $role->id)
                ->delete();
        }
    }

    public function down(): void
    {
        $permission = DB::table('permissions')->where('name', 'manage-lab-usages')->first();
        $role = DB::table('roles')->where('name', 'laboran')->first();

        if ($permission && $role) {
            $attached = DB::table($this->pivotTable())
                ->where('role_id', $role->id)
                ->where('permission_id', $permission->id)
                ->exists();

            if (! $attached) {
                DB::table($this->pivotTable())->insert([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }
};