<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computer_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('computer_id');
            $table->uuid('academic_year_id')->nullable();
            $table->enum('overall_status', ['Baik', 'Perlu Perbaikan', 'Kritis']);
            $table->text('notes')->nullable();
            $table->uuid('checked_by')->nullable();
            $table->timestamps();
            $table->foreign('computer_id')->references('id')->on('computers')->cascadeOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->foreign('checked_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('computer_check_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('computer_check_id');
            $table->string('checkable_type');
            $table->uuid('checkable_id');
            $table->enum('status', ['Baik', 'Rusak', 'Perlu Perbaikan', 'Usang', 'Tidak Terdeteksi']);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['checkable_type', 'checkable_id']);
            $table->foreign('computer_check_id')->references('id')->on('computer_checks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computer_check_items');
        Schema::dropIfExists('computer_checks');
    }
};
