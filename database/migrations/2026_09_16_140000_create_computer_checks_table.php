<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computer_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->enum('overall_status', ['Baik', 'Perlu Perbaikan', 'Kritis']);
            $table->text('notes')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('computer_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computer_check_id')->constrained()->cascadeOnDelete();
            $table->morphs('checkable');
            $table->enum('status', ['Baik', 'Rusak', 'Perlu Perbaikan', 'Usang', 'Tidak Terdeteksi']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computer_check_items');
        Schema::dropIfExists('computer_checks');
    }
};
