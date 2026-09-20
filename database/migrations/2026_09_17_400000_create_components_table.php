<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('category');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('image')->nullable();
            $table->integer('quantity')->default(0);
            $table->enum('status', ['Tersedia', 'Digunakan', 'Rusak', 'Maintenance'])->default('Tersedia');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
