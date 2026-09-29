<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('computer_borrowings', function (Blueprint $table) {
            $table->timestamp('returned_at')->nullable()->after('admin_notes');
            $table->uuid('returned_by')->nullable()->after('returned_at');
            $table->foreign('returned_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('computer_borrowings', function (Blueprint $table) {
            $table->dropForeign(['returned_by']);
            $table->dropColumn(['returned_at', 'returned_by']);
        });
    }
};
