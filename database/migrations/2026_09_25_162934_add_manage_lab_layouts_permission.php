<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permission = DB::table('permissions')->where('name', 'manage-lab-layouts')->first();

        if (! $permission) {
            $permissionId = (string) Str::uuid();
            DB::table('permissions')->insert([
                'id' => $permissionId,
                'name' => 'manage-lab-layouts',
                'label' => 'Kelola Denah Lab',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $permissionId = $permission->id;
        }

        $role = DB::table('roles')->where('name', 'admin')->first();

        if ($role) {
            $attached = DB::table('role_permission')
                ->where('role_id', $role->id)
                ->where('permission_id', $permissionId)
                ->exists();

            if (! $attached) {
                DB::table('role_permission')->insert([
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        // No-op
    }
};