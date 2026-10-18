<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Inventaris software terinstall per PC lab, di-sync dari agent
     * (registry Uninstall Windows) saat start + berkala.
     */
    public function up(): void
    {
        Schema::create('pc_softwares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pc_monitor_id');
            $table->string('name');
            $table->string('version')->nullable();
            $table->string('publisher')->nullable();
            $table->string('install_date')->nullable();
            $table->unsignedInteger('size_kb')->nullable();
            $table->timestamps();

            $table->index('pc_monitor_id');
            $table->foreign('pc_monitor_id')->references('id')->on('pc_monitors')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_softwares');
    }
};
