<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('k3_inspection_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();

            // Berita Acara header
            $table->string('hari');
            $table->date('tanggal_ba');
            $table->string('bulan_ba');
            $table->string('tahun_ba');
            $table->string('bulan_inspeksi');
            $table->string('lokasi');
            $table->string('pemeriksa_nama');
            $table->string('pemeriksa_jabatan');

            // Checklist: array of { item_no, item_text, ya, tidak, na, catatan }
            $table->json('checklist_items');

            // Laporan temuan: array of { temuan, dokumentasi, saran_perbaikan, pelaksana, target, catatan }
            $table->json('findings');

            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tanggal_ba', 'lokasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('k3_inspection_reports');
    }
};
