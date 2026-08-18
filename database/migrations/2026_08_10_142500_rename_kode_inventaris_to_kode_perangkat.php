<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventaris_iot_jaringan', function (Blueprint $table) {
            $table->renameColumn('kode_inventaris', 'kode_perangkat');
        });
    }

    public function down(): void
    {
        Schema::table('inventaris_iot_jaringan', function (Blueprint $table) {
            $table->renameColumn('kode_perangkat', 'kode_inventaris');
        });
    }
};
