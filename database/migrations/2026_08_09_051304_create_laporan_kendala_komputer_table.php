<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan_kendala_komputer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komputer_id')->constrained('komputer')->cascadeOnDelete();
            $table->string('nama_pelapor');
            $table->string('npm_nim');
            $table->text('deskripsi_kendala');
            $table->enum('status_kendala', ['menunggu', 'diperbaiki', 'selesai'])->default('menunggu');
            $table->string('gambar')->nullable();
            $table->date('tanggal_lapor');
            $table->date('tanggal_perbaikan')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_kendala_komputer');
    }
};
