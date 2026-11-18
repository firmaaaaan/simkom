<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('room_temperature_readings')) {
            return;
        }

        Schema::create('room_temperature_readings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('laboratory_id')->constrained('laboratories')->cascadeOnDelete();
            $table->decimal('temperature', 5, 2);
            $table->decimal('kelembapan', 5, 2)->nullable();
            $table->string('notes')->nullable();
            $table->uuid('recorded_by')->nullable();
            $table->datetime('recorded_at');
            $table->timestamps();

            $table->index(['laboratory_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_temperature_readings');
    }
};
