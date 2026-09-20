<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computer_hardware', function (Blueprint $table) {
            $table->uuid('computer_id');
            $table->uuid('hardware_id');
            $table->timestamps();
            $table->primary(['computer_id', 'hardware_id']);
            $table->foreign('computer_id')->references('id')->on('computers')->cascadeOnDelete();
            $table->foreign('hardware_id')->references('id')->on('hardware')->cascadeOnDelete();
        });

        Schema::create('computer_software', function (Blueprint $table) {
            $table->uuid('computer_id');
            $table->uuid('software_id');
            $table->timestamps();
            $table->primary(['computer_id', 'software_id']);
            $table->foreign('computer_id')->references('id')->on('computers')->cascadeOnDelete();
            $table->foreign('software_id')->references('id')->on('software')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computer_software');
        Schema::dropIfExists('computer_hardware');
    }
};
