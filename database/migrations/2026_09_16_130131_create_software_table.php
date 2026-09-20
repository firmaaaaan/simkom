<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('software', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('version')->nullable();
            $table->string('license_type')->nullable();
            $table->string('category');
            $table->integer('license_count')->default(0);
            $table->enum('status', ['Aktif', 'Expired', 'Trial', 'Non Aktif'])->default('Aktif');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('software');
    }
};
