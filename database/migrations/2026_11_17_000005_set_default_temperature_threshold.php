<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratories', function (Blueprint $table) {
            $table->decimal('temp_min', 5, 2)->nullable()->default(20)->change();
            $table->decimal('temp_max', 5, 2)->nullable()->default(25)->change();
        });

        DB::table('laboratories')->whereNull('temp_min')->update(['temp_min' => 20]);
        DB::table('laboratories')->whereNull('temp_max')->update(['temp_max' => 25]);
    }

    public function down(): void
    {
        Schema::table('laboratories', function (Blueprint $table) {
            $table->decimal('temp_min', 5, 2)->nullable()->default(null)->change();
            $table->decimal('temp_max', 5, 2)->nullable()->default(null)->change();
        });
    }
};
