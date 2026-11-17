<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_ruang', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('kdruang')->unique();
            $table->string('kode')->index();
            $table->string('nama');
            $table->unsignedInteger('kapasitas')->default(0);
            $table->string('ruang_asli');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_ruang');
    }
};
