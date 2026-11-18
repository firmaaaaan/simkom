<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('laboratories', 'kelembapan')) {
            return;
        }

        Schema::table('laboratories', function (Blueprint $table) {
            $table->decimal('kelembapan', 5, 2)->nullable()->default(null)->after('show_in_computer_list');
        });
    }

    public function down(): void
    {
        Schema::table('laboratories', function (Blueprint $table) {
            $table->dropColumn('kelembapan');
        });
    }
};
