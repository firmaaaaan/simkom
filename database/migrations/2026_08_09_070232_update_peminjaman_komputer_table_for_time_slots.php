<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman_komputer', function (Blueprint $table) {
            $table->date('tanggal_pinjam')->nullable()->change();
            $table->time('jam_mulai')->nullable()->after('tanggal_pinjam');
            $table->time('jam_selesai')->nullable()->after('jam_mulai');
            $table->string('status_peminjaman')->default('menunggu')->after('status');
            $table->dropColumn('tanggal_kembali_direncanakan');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_komputer', function (Blueprint $table) {
            $table->date('tanggal_kembali_direncanakan')->nullable()->after('tanggal_pinjam');
            $table->dropColumn(['jam_mulai', 'jam_selesai', 'status_peminjaman']);
        });
    }
};
