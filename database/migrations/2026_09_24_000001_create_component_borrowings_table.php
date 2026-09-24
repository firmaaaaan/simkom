<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('component_borrowings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('component_id');
            $table->foreign('component_id')->references('id')->on('components')->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('user_nim');
            $table->string('user_name');
            $table->enum('status', ['Using', 'Returned'])->default('Using');
            $table->date('borrowed_at');
            $table->timestamp('returned_at')->nullable();
            $table->text('return_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_borrowings');
    }
};
