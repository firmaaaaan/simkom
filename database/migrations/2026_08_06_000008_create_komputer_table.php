<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komputer', function (Blueprint $table) {
            $table->id();
            $table->string('kode_komputer')->unique();
            $table->string('nama_komputer');
            $table->foreignId('laboratorium_id')->nullable()->constrained('laboratorium')->nullOnDelete();
            $table->string('foto')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->enum('status', ['aktif', 'tidak_aktif', 'perbaikan', 'rusak', 'dipinjam'])->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komputer');
    }
};
