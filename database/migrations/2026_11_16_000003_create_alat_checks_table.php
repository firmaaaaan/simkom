<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('alat_checks')) {
            return;
        }

        Schema::create('alat_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('alat_id')->constrained('alat')->cascadeOnDelete();
            $table->date('check_date');
            $table->enum('status', ['Baik', 'Rusak']);
            $table->string('notes')->nullable();
            $table->uuid('checked_by')->nullable();
            $table->timestamps();

            $table->index(['alat_id', 'check_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alat_checks');
    }
};
