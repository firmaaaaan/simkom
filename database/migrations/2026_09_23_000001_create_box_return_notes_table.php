<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('box_return_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('box_usage_id');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->foreign('box_usage_id')->references('id')->on('box_usages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('box_return_notes');
    }
};
