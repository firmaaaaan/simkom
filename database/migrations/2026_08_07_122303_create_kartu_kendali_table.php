<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kartu_kendali', function (Blueprint $table) {
            $table->id();
            $table->morphs('inspectable');
            $table->date('tanggal_pemeriksaan');
            $table->string('pemeriksa');
            $table->enum('kondisi_keseluruhan', ['baik', 'cukup', 'rusak'])->default('baik');
            $table->text('catatan')->nullable();
            $table->json('items')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_kendali');
    }
};
