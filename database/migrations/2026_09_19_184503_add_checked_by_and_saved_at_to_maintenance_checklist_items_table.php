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
        Schema::table('maintenance_checklist_items', function (Blueprint $table) {
            $table->string('checked_by')->nullable()->after('is_checked');
            $table->timestamp('saved_at')->nullable()->after('checked_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_checklist_items', function (Blueprint $table) {
            $table->dropColumn(['checked_by', 'saved_at']);
        });
    }
};
