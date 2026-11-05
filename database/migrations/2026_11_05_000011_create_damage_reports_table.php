<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 30)->unique();

            // Dilaporkan Oleh
            $table->string('pelapor_nama');
            $table->string('pelapor_ruangan');
            $table->string('pelapor_telepon')->nullable();

            // Incident Information
            $table->date('tanggal_insiden');
            $table->time('waktu_kejadiaan')->nullable();
            $table->date('tanggal_dilaporkan')->nullable();
            $table->time('waktu_dilaporkan')->nullable();
            $table->text('lokasi_spesifik')->nullable();

            // Jenis kejadian
            $table->string('jenis_kejadian', 20); // kerusakan/kehilangan/pencurian

            // Equipment Information
            $table->json('peralatan')->nullable(); // [{nama, merk, no_inventaris, jumlah, keterangan}]
            $table->text('deskripsi_kejadian');
            $table->string('perkiraan_biaya')->nullable();
            $table->string('penanggung_jawab')->nullable();

            // Police Report
            $table->boolean('dilaporkan_ke_polisi')->default(false);
            $table->string('berkas_polisi')->nullable();
            $table->string('petugas_polisi')->nullable();
            $table->string('unit_kepolisian')->nullable();
            $table->string('telepon_polisi')->nullable();

            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tanggal_insiden', 'jenis_kejadian']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
    }
};
