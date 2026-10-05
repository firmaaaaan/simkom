<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('box_usages', function (Blueprint $table) {
            $table->timestamp('stopped_at')->nullable()->after('returned_at');
        });

        Schema::table('box_usages', function (Blueprint $table) {
            $table->enum('status', ['Using', 'Stopped', 'Returned'])->default('Using')->change();
        });
    }

    public function down(): void
    {
        Schema::table('box_usages', function (Blueprint $table) {
            $table->enum('status', ['Using', 'Returned'])->default('Using')->change();
        });

        Schema::table('box_usages', function (Blueprint $table) {
            $table->dropColumn('stopped_at');
        });
    }
};
