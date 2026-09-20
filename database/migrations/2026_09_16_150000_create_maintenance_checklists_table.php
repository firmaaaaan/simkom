<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('maintenance_date');
            $table->string('inspector_name')->nullable();
            $table->text('notes_computer')->nullable();
            $table->text('notes_mouse_keyboard')->nullable();
            $table->text('notes_ups')->nullable();
            $table->text('notes_monitor')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_checklist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('computer_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->integer('item_number');
            $table->boolean('is_checked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_checklist_items');
        Schema::dropIfExists('maintenance_checklists');
    }
};
