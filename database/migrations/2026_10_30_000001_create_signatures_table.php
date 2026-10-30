<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TTD laporan: nama + jabatan + gambar tanda tangan (scan) yang bisa
 * ditugaskan ke laporan cetak tertentu lewat tabel report_signature.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('role', 150);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
