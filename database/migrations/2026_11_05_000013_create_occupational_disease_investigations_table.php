<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('occupational_disease_investigations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 30)->unique();

            // Identitas
            $table->string('nama');
            $table->string('nip')->nullable();
            $table->string('bagian')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('jenis_kelamin', 10)->nullable();
            $table->string('lama_bekerja')->nullable();

            // Anamnesa
            $table->text('keluhan')->nullable();
            $table->text('riwayat_penyakit')->nullable();
            $table->text('riwayat_penyakit_keluarga')->nullable();
            $table->text('riwayat_pekerjaan')->nullable();

            // Pemeriksaan
            $table->text('pemeriksaan_fisik')->nullable();
            $table->text('pemeriksaan_radiologi')->nullable();
            $table->text('pemeriksaan_laboratorium')->nullable();
            $table->text('pemeriksaan_penunjang')->nullable();
            $table->text('pemeriksaan_patologi_anatomi')->nullable();
            $table->text('perbandingan_pemeriksaan_awal')->nullable();
            $table->text('perbandingan_pemeriksaan_berkala')->nullable();

            $table->date('tanggal_pemeriksaan')->nullable();
            $table->string('diperiksa_oleh')->nullable();

            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occupational_disease_investigations');
    }
};
