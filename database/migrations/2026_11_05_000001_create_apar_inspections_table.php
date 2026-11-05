<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apar_inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();

            // No. Reg. APAR pada unit alat pemadam yang diperiksa
            $table->string('nomor_registrasi', 50);

            // Hari/Tanggal & lokasi pemeriksaan
            $table->date('tanggal_inspeksi');
            $table->string('lokasi');

            // Hasil 7 item pemeriksaan: { key: { status: baik|tidak, keterangan } }
            $table->json('items');

            // Tgl. Inspeksi / Oleh
            $table->string('diperiksa_oleh');

            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tanggal_inspeksi', 'lokasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apar_inspections');
    }
};
