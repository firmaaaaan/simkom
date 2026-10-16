<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Antrean perintah power untuk agent: perintah dibuat dashboard
     * (mis. shutdown), agent mengambil lalu meng-ack setelah dieksekusi.
     * Terpisah dari pc_monitors agar riwayat perintah tetap ada walau
     * baris telemetry berubah.
     */
    public function up(): void
    {
        Schema::create('pc_power_commands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pc_monitor_id');
            $table->string('action');       // shutdown
            $table->string('status')->default('pending'); // pending | completed | failed
            $table->uuid('requested_by')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['pc_monitor_id', 'status']);
            $table->foreign('pc_monitor_id')->references('id')->on('pc_monitors')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_power_commands');
    }
};
