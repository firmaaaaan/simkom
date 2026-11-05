<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_safety_analyses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();

            // Identitas pekerjaan
            $table->string('nama_pekerja');
            $table->string('nama_pekerjaan');
            $table->date('tanggal_pekerjaan');
            $table->string('tempat');

            // APD yang diperlukan: [ key => true ], plus teks lain-lain
            $table->json('apd');
            $table->string('apd_lain')->nullable();

            // Langkah pekerjaan: [ { langkah, bahaya, pengendalian, penanggung_jawab } ]
            $table->json('steps');

            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tanggal_pekerjaan', 'tempat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_safety_analyses');
    }
};
