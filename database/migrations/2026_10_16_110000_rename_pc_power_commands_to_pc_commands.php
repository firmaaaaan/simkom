<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generalisasi antrean perintah agent: tidak hanya power (shutdown),
     * tapi juga restart/lock/logout/screenshot — rename tabel +
     * kolom message untuk hasil eksekusi dari agent.
     */
    public function up(): void
    {
        Schema::rename('pc_power_commands', 'pc_commands');

        Schema::table('pc_commands', function (Blueprint $table) {
            $table->string('message')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pc_commands', function (Blueprint $table) {
            $table->dropColumn('message');
        });

        Schema::rename('pc_commands', 'pc_power_commands');
    }
};
