<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            // Jenis kejadian: Kecelakaan / Insiden / Nearmiss / Duga Bahaya
            $table->string('telah_terjadi', 30)->default('Kecelakaan')->after('waktu_kejadian');
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropColumn('telah_terjadi');
        });
    }
};
