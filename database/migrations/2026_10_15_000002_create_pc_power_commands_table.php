<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Antrean perintah power ke PC lab. Dashboard membuat baris "pending",
     * agent di PC mengambilnya via GET /api/v1/commands lalu meng-ack hasil
     * eksekusinya (shutdown /s).
     */
    public function up(): void
    {
        Schema::create('pc_power_commands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pc_monitor_id');
            $table->string('action', 20);
            $table->string('status', 20)->default('pending');
            $table->string('message')->nullable();
            $table->uuid('requested_by')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->foreign('pc_monitor_id')->references('id')->on('pc_monitors')->nullOnDelete();
            $table->foreign('requested_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['pc_monitor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_power_commands');
    }
};
