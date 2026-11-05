<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hse_inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();

            // Kepala form: Lokasi, Inspeksi Oleh, Tanggal Inspeksi
            $table->string('lokasi');
            $table->string('diperiksa_oleh');
            $table->date('tanggal_inspeksi');

            // Baris temuan:
            // lokasi, temuan, unsafe_condition, unsafe_action, tanggal_kejadian,
            // tindakan_perbaikan, tanggal_perbaikan, pic
            $table->json('findings');

            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tanggal_inspeksi', 'lokasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hse_inspections');
    }
};
