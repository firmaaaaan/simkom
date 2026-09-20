<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('check_date');
            $table->string('officer_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('device_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_check_id')->constrained()->cascadeOnDelete();
            $table->foreignId('computer_id')->constrained()->cascadeOnDelete();
            $table->string('item_key');
            $table->boolean('is_checked')->default(false);
            $table->timestamps();

            // Satu sel matriks = satu baris (komputer × item).
            $table->unique(['device_check_id', 'computer_id', 'item_key'], 'device_check_items_unique');
            $table->index(['device_check_id', 'computer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_check_items');
        Schema::dropIfExists('device_checks');
    }
};
