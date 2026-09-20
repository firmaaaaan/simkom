<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code', 20)->nullable()->unique();
            $table->foreignId('laboratory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('computer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
