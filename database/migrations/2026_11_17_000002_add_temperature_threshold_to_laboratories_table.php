<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('laboratories', 'temp_min')) {
            Schema::table('laboratories', function (Blueprint $table) {
                $table->decimal('temp_min', 5, 2)->nullable()->after('capacity');
            });
        }

        if (! Schema::hasColumn('laboratories', 'temp_max')) {
            Schema::table('laboratories', function (Blueprint $table) {
                $table->decimal('temp_max', 5, 2)->nullable()->after('temp_min');
            });
        }
    }

    public function down(): void
    {
        Schema::table('laboratories', function (Blueprint $table) {
            $table->dropColumn(['temp_min', 'temp_max']);
        });
    }
};
