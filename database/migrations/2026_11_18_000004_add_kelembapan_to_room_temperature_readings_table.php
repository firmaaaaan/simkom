<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('room_temperature_readings', 'kelembapan')) {
            return;
        }

        Schema::table('room_temperature_readings', function (Blueprint $table) {
            $table->decimal('kelembapan', 5, 2)->nullable()->after('temperature');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('room_temperature_readings', 'kelembapan')) {
            return;
        }

        Schema::table('room_temperature_readings', function (Blueprint $table) {
            $table->dropColumn('kelembapan');
        });
    }
};
