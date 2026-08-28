<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeliharaan_komputer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komputer_id')->constrained('komputer')->cascadeOnDelete();
            $table->unsignedBigInteger('tahun_ajaran_id')->nullable()->after('komputer_id');
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajaran')->nullOnDelete();
            $table->date('tanggal_pemeliharaan');
            $table->enum('jenis_pemeliharaan', ['preventif', 'korektif', 'upgrade', 'penggantian', 'lainnya'])->default('preventif');
            $table->text('deskripsi');
            $table->decimal('biaya', 12, 2)->nullable();
            $table->string('pic', 255)->nullable();
            $table->timestamps();

            $table->index(['komputer_id', 'tahun_ajaran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeliharaan_komputer');
    }
};
