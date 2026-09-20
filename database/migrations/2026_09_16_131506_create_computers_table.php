<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->uuid('laboratory_id')->nullable();
            $table->enum('status', ['Aktif', 'Tidak Aktif', 'Maintenance'])->default('Aktif');
            $table->enum('maintenance_reason', ['ticket', 'scheduled', 'manual'])->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('laboratory_id')->references('id')->on('laboratories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computers');
    }
};
