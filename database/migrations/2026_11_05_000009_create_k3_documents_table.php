<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('k3_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 30)->unique();
            $table->string('judul');
            $table->string('kategori', 50)->default('umum'); // form, peraturan, sop, pelatihan, lain-lain
            $table->string('nomor_dokumen')->nullable();
            $table->date('tanggal_berlaku')->nullable();
            $table->date('tanggal_revisi')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_extension', 10)->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->uuid('uploaded_by')->nullable();
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['kategori', 'judul']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('k3_documents');
    }
};
