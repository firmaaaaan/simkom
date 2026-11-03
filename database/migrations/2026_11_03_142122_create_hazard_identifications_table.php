<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazard_identifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('laboratory_id')->nullable()->constrained()->nullOnDelete();
            $table->string('area_activity');
            $table->text('hazard')->nullable();
            $table->text('risk')->nullable();
            $table->string('likelihood_before')->nullable();
            $table->string('impact_before')->nullable();
            $table->string('risk_level_before')->nullable();
            $table->text('control_before')->nullable();
            $table->string('schedule')->nullable();
            $table->string('likelihood_after')->nullable();
            $table->string('impact_after')->nullable();
            $table->string('risk_level_after')->nullable();
            $table->text('control_after')->nullable();
            $table->text('recommendation')->nullable();
            $table->string('authority')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_identifications');
    }
};