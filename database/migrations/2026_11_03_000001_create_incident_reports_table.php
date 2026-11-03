<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();

            // Hari, Tanggal & Waktu kejadian
            $table->date('tanggal_kejadian');
            $table->time('waktu_kejadian');

            // Lokasi kejadian
            $table->string('lokasi');

            // Kecelakaan atas nama
            $table->string('nama_korban');
            $table->string('jabatan')->nullable();

            // Asal Institusi/Fakultas/Prodi/Unit
            $table->string('asal_institusi')->nullable();

            // Sebab-sebab kecelakaan, riwayat kejadian, saksi, akibat kejadian
            $table->text('sebab')->nullable();
            $table->text('riwayat')->nullable();
            $table->text('saksi')->nullable();
            $table->text('akibat')->nullable();

            // Dilaporkan oleh
            $table->string('pelapor');

            // Bukti dokumentasi kejadian (path gambar di disk public)
            $table->json('images')->nullable();

            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tanggal_kejadian', 'lokasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
