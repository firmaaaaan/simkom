<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('komponen_iot', function (Blueprint $table) {
            $table->string('jenis')->nullable()->after('spesifikasi');
        });

        Schema::table('komponen_jaringan', function (Blueprint $table) {
            $table->string('jenis')->nullable()->after('spesifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('komponen_iot', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });

        Schema::table('komponen_jaringan', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};
