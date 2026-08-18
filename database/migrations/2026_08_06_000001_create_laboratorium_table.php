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
        Schema::create('laboratorium', function (Blueprint $table) {
            $table->id();
            $table->string('kode_laboratorium')->unique();
            $table->string('nama_laboratorium');
            $table->string('gedung');
            $table->string('ruangan');
            $table->integer('kapasitas')->nullable();
            $table->text('fasilitas')->nullable();
            $table->enum('status', ['aktif', 'non-aktif'])->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratorium');
    }
};
