<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Waktu terakhir inventaris software PC disinkronkan dari agent. */
    public function up(): void
    {
        Schema::table('pc_monitors', function (Blueprint $table) {
            $table->timestamp('software_synced_at')->nullable()->after('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('pc_monitors', function (Blueprint $table) {
            $table->dropColumn('software_synced_at');
        });
    }
};
