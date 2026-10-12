<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('software')) {
            $cols = array_filter([
                'installer_file',
                'installer_original_name',
                'checksum_sha256',
                'silent_install_args',
                'architecture',
                'installer_size',
            ], fn ($c) => Schema::hasColumn('software', $c));

            if ($cols !== []) {
                Schema::table('software', function (Blueprint $table) use ($cols) {
                    $table->dropColumn($cols);
                });
            }
        }

        if (Schema::hasTable('computers')) {
            $cols = array_filter(
                ['agent_token', 'agent_last_seen_at'],
                fn ($c) => Schema::hasColumn('computers', $c)
            );

            if ($cols !== []) {
                Schema::table('computers', function (Blueprint $table) use ($cols) {
                    if (in_array('agent_token', $cols, true)) {
                        try {
                            $table->dropUnique(['agent_token']);
                        } catch (Throwable) {
                            // index tidak ada, abaikan
                        }
                    }
                    $table->dropColumn($cols);
                });
            }
        }

        Schema::dropIfExists('software_deployment_items');
        Schema::dropIfExists('software_deployments');
        Schema::dropIfExists('personal_access_tokens');

        if (Schema::hasTable('permissions')) {
            $ids = DB::table('permissions')->where('name', 'deploy-software')->pluck('id');

            if ($ids->isNotEmpty()) {
                DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
                DB::table('permissions')->whereIn('id', $ids)->delete();

                app(PermissionRegistrar::class)->forgetCachedPermissions();
            }
        }
    }

    public function down(): void
    {
        // Tidak bisa di-down secara otomatis (data installer & agent hilang)
    }
};
