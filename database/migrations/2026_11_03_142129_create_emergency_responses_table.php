<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('laboratory_id')->nullable()->constrained()->nullOnDelete();
            $table->string('scenario');
            $table->text('procedure')->nullable();
            $table->string('responsible_person')->nullable();
            $table->string('contact')->nullable();
            $table->text('equipment')->nullable();
            $table->date('drill_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_responses');
    }
};