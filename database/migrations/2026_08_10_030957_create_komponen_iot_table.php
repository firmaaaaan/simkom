<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komponen_iot', function (Blueprint $table) {
            $table->id();
            $table->string('kode_komponen')->unique();
            $table->string('nama_komponen');
            $table->string('kategori')->default('IoT');
            $table->string('merek')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->integer('jumlah')->default(0);
            $table->string('lokasi')->nullable();
            $table->string('status')->default('tersedia');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komponen_iot');
    }
};
