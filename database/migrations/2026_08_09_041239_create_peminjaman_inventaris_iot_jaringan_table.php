<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_inventaris_iot_jaringan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaris_iot_jaringan_id')->constrained('inventaris_iot_jaringan')->cascadeOnDelete();
            $table->string('nama_peminjam');
            $table->string('npm_nim');
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali_direncanakan');
            $table->date('tanggal_kembali_aktual')->nullable();
            $table->enum('status', ['dipinjam', 'dikembalikan'])->default('dipinjam');
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_inventaris_iot_jaringan');
    }
};
