<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accident_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();

            // Tanggal berita acara (tempat & tanggal penandatanganan)
            $table->date('tanggal');

            // Yang bertanda tangan / yang menerangkan
            $table->string('nama_pelapor');
            $table->string('jabatan_pelapor');

            // Korban kecelakaan (Peneliti/Praktikan/PLP/Pengguna Lab Lain)
            $table->string('nama_korban');
            $table->string('alamat_korban');
            $table->string('ttl_korban');
            $table->string('unit_status_korban');

            // Uraian kejadian bernomor: [ "....", "...." ]
            $table->json('uraian_kejadian');

            // Dampak dari kecelakaan kerja
            $table->text('dampak');

            // Saksi I & Saksi II
            $table->string('saksi_satu');
            $table->string('saksi_dua');

            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tanggal', 'nama_korban']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accident_reports');
    }
};
