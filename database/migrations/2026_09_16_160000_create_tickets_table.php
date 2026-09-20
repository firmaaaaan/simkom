<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tracking_code', 20)->nullable()->unique();
            $table->uuid('laboratory_id');
            $table->uuid('computer_id')->nullable();
            $table->uuid('academic_year_id');
            $table->uuid('reported_by')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_prodi')->nullable();
            $table->string('reporter_nim')->nullable();
            $table->enum('category', ['Komputer', 'Hardware', 'Software', 'Jaringan', 'Listrik/UPS']);
            $table->string('title');
            $table->text('description');
            $table->json('images')->nullable();
            $table->enum('priority', ['Rendah', 'Sedang', 'Tinggi', 'Darurat'])->default('Sedang');
            $table->enum('status', ['Open', 'In Progress', 'Resolved', 'Closed'])->default('Open');
            $table->timestamps();
            $table->foreign('laboratory_id')->references('id')->on('laboratories')->cascadeOnDelete();
            $table->foreign('computer_id')->references('id')->on('computers')->nullOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->foreign('reported_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
