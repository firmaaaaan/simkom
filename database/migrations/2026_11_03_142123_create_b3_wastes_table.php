<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b3_wastes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('laboratory_id')->nullable()->constrained()->nullOnDelete();
            $table->string('waste_name');
            $table->string('waste_type')->nullable();
            $table->string('source')->nullable();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->text('storage_method')->nullable();
            $table->text('disposal_method')->nullable();
            $table->date('disposal_date')->nullable();
            $table->string('pic')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b3_wastes');
    }
};