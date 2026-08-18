<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kendala_komputer', function (Blueprint $table) {
            $table->enum('kategori_kerusakan', ['hardware', 'software', 'jaringan', 'lainnya'])->nullable()->after('nama_prodi');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kendala_komputer', function (Blueprint $table) {
            $table->dropColumn('kategori_kerusakan');
        });
    }
};
