<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('inventaris_iot_jaringan_items');
        Schema::dropIfExists('inventaris_iot_jaringan');

        Schema::create('inventaris_iot_jaringan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_perangkat', 50)->nullable()->after('id');
            $table->string('nama_inventaris');
            $table->enum('kategori', ['IoT', 'Jaringan']);
            $table->enum('jenis', ['Satuan', 'Paket', 'Sistem', 'Box'])->default('Satuan');
            $table->string('lokasi')->nullable();
            $table->enum('status', ['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'])->default('tersedia');
            $table->enum('status_ketersediaan', ['tersedia', 'dipinjam', 'perbaikan', 'rusak'])->default('tersedia')->after('status');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('inventaris_iot_jaringan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaris_iot_jaringan_id')->constrained('inventaris_iot_jaringan')->cascadeOnDelete();
            $table->unsignedBigInteger('komponen_iot_jaringan_id')->nullable();
            $table->integer('jumlah')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaris_iot_jaringan_items');
        Schema::dropIfExists('inventaris_iot_jaringan');
    }
};
