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
        Schema::table('software', function (Blueprint $table) {
            $table->dropForeign(['laboratorium_id']);
            $table->dropColumn('laboratorium_id', 'jumlah_instalasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('software', function (Blueprint $table) {
            $table->foreignId('laboratorium_id')->nullable()->constrained('laboratorium')->nullOnDelete()->after('jumlah_instalasi');
            $table->integer('jumlah_instalasi')->default(0)->after('lisensi');
        });
    }
};
