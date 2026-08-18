<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman_komputer', function (Blueprint $table) {
            $table->string('kode_tracker')->unique()->nullable()->after('npm_nim');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_komputer', function (Blueprint $table) {
            $table->dropColumn('kode_tracker');
        });
    }
};
