<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computer_hardware', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hardware_id')->constrained('hardware')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('computer_software', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('software_id')->constrained('software')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computer_software');
        Schema::dropIfExists('computer_hardware');
    }
};
