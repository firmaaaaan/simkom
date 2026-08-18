<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hardware', function (Blueprint $table) {
            $table->renameColumn('kode_komponen', 'kode_hardware');
            $table->renameColumn('nama_komponen', 'nama_hardware');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hardware', function (Blueprint $table) {
            $table->renameColumn('kode_hardware', 'kode_komponen');
            $table->renameColumn('nama_hardware', 'nama_komponen');
        });
    }
};
