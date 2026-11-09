<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('jadwal_pemeliharaan_items');
        Schema::dropIfExists('jadwal_pemeliharaans');

        Schema::create('jadwal_pemeliharaans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_dokumen')->default('FM-UNISA-LAB-PBM-03-02');
            $table->uuid('laboratory_id')->nullable();
            $table->string('laboratorium_name')->nullable();
            $table->string('periode');
            $table->string('pic');
            $table->date('tanggal_penyusunan');
            $table->string('disusun_oleh')->nullable();
            $table->date('disusun_tanggal')->nullable();
            $table->string('diperiksa_oleh')->nullable();
            $table->date('diperiksa_tanggal')->nullable();
            $table->string('mengetahui_oleh')->nullable();
            $table->date('mengetahui_tanggal')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('laboratory_id')->references('id')->on('laboratories')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['periode', 'tanggal_penyusunan']);
        });

        Schema::create('jadwal_pemeliharaan_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('jadwal_pemeliharaan_id');
            $table->string('nama_alat_bahan');
            $table->string('kode_inventaris')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('jenis_pemeliharaan')->nullable();
            $table->string('frekuensi')->nullable();
            $table->date('jadwal')->nullable();
            $table->string('pic')->nullable();
            $table->enum('status', ['Terjadwal', 'Selesai', 'Tunda'])->default('Terjadwal');
            $table->text('catatan_hasil')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->foreign('jadwal_pemeliharaan_id')->references('id')->on('jadwal_pemeliharaans')->cascadeOnDelete();
            $table->index(['jadwal_pemeliharaan_id', 'sort_order'], 'jp_items_jp_id_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_pemeliharaan_items');
        Schema::dropIfExists('jadwal_pemeliharaans');
    }
};
