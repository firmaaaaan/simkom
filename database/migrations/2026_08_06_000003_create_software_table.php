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
        Schema::create('software', function (Blueprint $table) {
            $table->id();
            $table->string('kode_software')->unique();
            $table->string('nama_software');
            $table->string('kategori');
            $table->string('versi')->nullable();
            $table->enum('lisensi', ['gratis', 'berbayar', 'edukasi', 'trial', 'open_source'])->default('gratis');
            $table->integer('jumlah_instalasi')->default(0);
            $table->foreignId('laboratorium_id')->nullable()->constrained('laboratorium')->nullOnDelete();
            $table->date('tanggal_instalasi')->nullable();
            $table->date('tanggal_expired')->nullable();
            $table->enum('status', ['aktif', 'tidak_aktif', 'trial'])->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software');
    }
};
