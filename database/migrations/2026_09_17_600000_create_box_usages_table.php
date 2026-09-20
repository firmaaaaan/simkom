<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('box_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('box_id');
            $table->string('user_name');
            $table->string('user_nim');
            $table->string('user_kelas')->nullable();
            $table->enum('status', ['Using', 'Returned'])->default('Using');
            $table->timestamp('used_at');
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
            $table->foreign('box_id')->references('id')->on('boxes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('box_usages');
    }
};
