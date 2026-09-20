<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('box_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('box_id');
            $table->uuid('component_id');
            $table->integer('quantity')->default(0);
            $table->timestamps();
            $table->unique(['box_id', 'component_id']);
            $table->foreign('box_id')->references('id')->on('boxes')->cascadeOnDelete();
            $table->foreign('component_id')->references('id')->on('components')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('box_components');
    }
};
