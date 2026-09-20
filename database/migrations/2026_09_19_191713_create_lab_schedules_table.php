<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('laboratory_id');
            $table->enum('day', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('course_name');
            $table->string('study_program');
            $table->string('semester')->nullable();
            $table->string('instructor')->nullable();
            $table->string('class_group')->nullable();
            $table->uuid('created_by');
            $table->timestamps();
            $table->unique(['laboratory_id', 'day', 'start_time'], 'lab_schedule_unique_slot');
            $table->foreign('laboratory_id')->references('id')->on('laboratories')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_schedules');
    }
};
