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
        Schema::create('peminjaman_komputer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komputer_id')->constrained('komputer')->cascadeOnDelete();
            $table->string('nama_peminjam');
            $table->string('npm_nim');
            $table->string('kode_tracker')->unique()->nullable()->after('npm_nim');
            $table->string('nama_prodi')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['dipinjam', 'dikembalikan'])->default('dipinjam');
            $table->string('status_peminjaman')->default('menunggu')->after('status');
            $table->date('tanggal_pinjam')->nullable();
            $table->time('jam_mulai')->nullable()->after('tanggal_pinjam');
            $table->time('jam_selesai')->nullable()->after('jam_mulai');
            $table->date('tanggal_kembali_aktual')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_komputer');
    }
};
