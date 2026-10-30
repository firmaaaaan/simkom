<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penugasan TTD per laporan. report_key unique karena satu laporan hanya
 * boleh punya satu TTD; signature_id cascade agar hapus TTD ikut melepas
 * penugasan laporannya (laporan kembali ke TTD default hardcoded).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_signatures', function (Blueprint $table) {
            $table->string('report_key', 50)->primary();
            $table->foreignId('signature_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_signatures');
    }
};
