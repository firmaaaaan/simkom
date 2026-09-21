<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hardware', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('hardware', function (Blueprint $table) {
            $table->integer('quantity')->default(0);
            $table->enum('status', ['Tersedia', 'Digunakan', 'Rusak', 'Maintenance'])->default('Tersedia');
        });
    }
};
