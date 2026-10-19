<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengadaans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama_barang');
            $table->enum('kategori', ['Software', 'Hardware', 'PC']);
            $table->unsignedInteger('jumlah')->default(1);
            $table->unsignedBigInteger('harga')->default(0);
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['Proses', 'Acc', 'Ditolak'])->default('Proses');
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['kategori', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengadaans');
    }
};
