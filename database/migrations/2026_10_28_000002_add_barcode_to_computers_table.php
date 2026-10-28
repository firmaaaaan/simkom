<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Barcode inventaris sistem lama yang sudah ditempel di casing CPU.
 * Nullable karena sebagian komputer belum dipindai, unique karena satu
 * barcode hanya boleh menunjuk satu komputer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('computers', function (Blueprint $table) {
            $table->string('barcode', 100)->nullable()->unique()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('computers', function (Blueprint $table) {
            $table->dropUnique(['barcode']);
            $table->dropColumn('barcode');
        });
    }
};
