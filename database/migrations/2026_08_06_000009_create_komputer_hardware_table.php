<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komputer_hardware', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komputer_id')->constrained('komputer')->cascadeOnDelete();
            $table->foreignId('hardware_id')->constrained('hardware')->cascadeOnDelete();
            $table->integer('jumlah')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komputer_hardware');
    }
};
