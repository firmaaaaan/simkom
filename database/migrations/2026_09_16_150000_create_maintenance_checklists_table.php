<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_checklists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('laboratory_id');
            $table->uuid('academic_year_id');
            $table->date('maintenance_date');
            $table->string('inspector_name')->nullable();
            $table->text('notes_computer')->nullable();
            $table->text('notes_mouse_keyboard')->nullable();
            $table->text('notes_ups')->nullable();
            $table->text('notes_monitor')->nullable();
            $table->timestamps();
            $table->foreign('laboratory_id')->references('id')->on('laboratories')->cascadeOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
        });

        Schema::create('maintenance_checklist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('maintenance_checklist_id');
            $table->uuid('computer_id');
            $table->string('category');
            $table->integer('item_number');
            $table->boolean('is_checked')->default(false);
            $table->string('checked_by')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();
            $table->foreign('maintenance_checklist_id')->references('id')->on('maintenance_checklists')->cascadeOnDelete();
            $table->foreign('computer_id')->references('id')->on('computers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_checklist_items');
        Schema::dropIfExists('maintenance_checklists');
    }
};
