<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('laboratory_id');
            $table->uuid('academic_year_id');
            $table->date('check_date');
            $table->string('officer_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('laboratory_id')->references('id')->on('laboratories')->cascadeOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
        });

        Schema::create('device_check_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('device_check_id');
            $table->uuid('computer_id');
            $table->string('item_key');
            $table->boolean('is_checked')->default(false);
            $table->timestamps();
            $table->unique(['device_check_id', 'computer_id', 'item_key'], 'device_check_items_unique');
            $table->index(['device_check_id', 'computer_id']);
            $table->foreign('device_check_id')->references('id')->on('device_checks')->cascadeOnDelete();
            $table->foreign('computer_id')->references('id')->on('computers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_check_items');
        Schema::dropIfExists('device_checks');
    }
};
