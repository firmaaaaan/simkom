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
        Schema::create('jadwal_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_url');
            $table->string('api_token')->nullable();
            $table->enum('tipe', ['kuliah', 'non_kuliah'])->default('kuliah');
            $table->boolean('is_active')->default(true);
            $table->integer('refresh_interval')->default(60);
            $table->timestamp('last_sync')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_settings');
    }
};
