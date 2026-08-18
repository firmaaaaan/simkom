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
        Schema::create('komponen', function (Blueprint $table) {
            $table->id();
            $table->string('kode_komponen')->unique();
            $table->string('nama_komponen');
            $table->string('kategori');
            $table->string('merek')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->integer('jumlah')->default(0);
            $table->string('lokasi')->nullable();
            $table->enum('status', ['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'])->default('tersedia');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komponen');
    }
};
