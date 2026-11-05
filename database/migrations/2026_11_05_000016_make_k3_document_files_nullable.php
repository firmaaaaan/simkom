<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom file pada k3_documents dibuat nullable agar dokumen K3 bisa
 * diimport sebagai metadata saja (berkas menyusul via upload manual).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('k3_documents', function ($table) {
            $table->string('file_name')->nullable()->change();
            $table->string('file_path')->nullable()->change();
            $table->string('file_extension', 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('k3_documents', function ($table) {
            $table->string('file_name')->nullable(false)->change();
            $table->string('file_path')->nullable(false)->change();
        });
    }
};
