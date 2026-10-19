<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot proses yang SEDANG berjalan per PC lab, di-sync dari agent
     * (proses + judul jendela visible) tiap process_interval_seconds.
     * Snapshot terbaru menang (semua baris PC diganti tiap sync).
     */
    public function up(): void
    {
        Schema::create('pc_processes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pc_monitor_id');
            $table->string('name');
            $table->string('window_title')->nullable();
            $table->timestamps();

            $table->index('pc_monitor_id');
            $table->foreign('pc_monitor_id')->references('id')->on('pc_monitors')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_processes');
    }
};
