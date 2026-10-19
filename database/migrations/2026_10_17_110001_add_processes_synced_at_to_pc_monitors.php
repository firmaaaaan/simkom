<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom proses_synced_at (waktu snapshot proses berjalan terakhir)
     * ke pc_monitors — dipakai dashboard untuk menampilkan kesegaran data
     * titik hijau "sedang berjalan" di modal Software.
     */
    public function up(): void
    {
        Schema::table('pc_monitors', function (Blueprint $table) {
            $table->timestamp('processes_synced_at')->nullable()->after('software_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('pc_monitors', function (Blueprint $table) {
            $table->dropColumn('processes_synced_at');
        });
    }
};
