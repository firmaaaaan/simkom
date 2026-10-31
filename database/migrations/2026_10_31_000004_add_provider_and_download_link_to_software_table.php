<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('software', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('license_type');
            $table->string('download_link')->nullable()->after('provider');
        });
    }

    public function down(): void
    {
        Schema::table('software', function (Blueprint $table) {
            $table->dropColumn(['provider', 'download_link']);
        });
    }
};
