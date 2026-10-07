<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('box_usage_damages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('box_usage_id');
            $table->uuid('component_id');
            $table->uuid('reported_by')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->foreign('box_usage_id')->references('id')->on('box_usages')->cascadeOnDelete();
            $table->foreign('component_id')->references('id')->on('components')->cascadeOnDelete();
            $table->foreign('reported_by')->references('id')->on('users')->nullOnDelete();

            // Satu komponen hanya boleh dilaporkan rusak sekali per pemakaian box.
            $table->unique(['box_usage_id', 'component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('box_usage_damages');
    }
};
