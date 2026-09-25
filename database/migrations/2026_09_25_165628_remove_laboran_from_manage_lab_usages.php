<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permission = DB::table('permissions')->where('name', 'manage-lab-usages')->first();
        $role = DB::table('roles')->where('name', 'laboran')->first();

        if ($permission && $role) {
            DB::table('role_permission')
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
            $attached = DB::table('role_permission')
                ->where('role_id', $role->id)
                ->where('permission_id', $permission->id)
                ->exists();

            if (! $attached) {
                DB::table('role_permission')->insert([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }
};