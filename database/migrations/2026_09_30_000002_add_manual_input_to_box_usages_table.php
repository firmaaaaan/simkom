<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('box_usages', function (Blueprint $table) {
            // "qr" = dipinjam mahasiswa lewat scan QR, "manual" = dicatat langsung oleh admin.
            $table->enum('source', ['qr', 'manual'])->default('qr')->after('returned_at');
            $table->uuid('created_by')->nullable()->after('source');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('box_usages', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['source', 'created_by']);
        });
    }
};
