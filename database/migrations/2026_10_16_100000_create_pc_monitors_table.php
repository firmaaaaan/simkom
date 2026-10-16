<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Telemetri PC lab dari agent (hostname unik, laporan CPU/RAM/disk).
     * Terpisah dari tabel "computers" (inventaris) agar data inventaris
     * tidak bergantung pada agent. Timestamp setelah migration drop fitur
     * lama (2026_10_16_000001) agar aman untuk DB existing maupun fresh.
     */
    public function up(): void
    {
        Schema::create('pc_monitors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('hostname')->unique();
            $table->string('ip_address');
            $table->string('mac_address')->nullable();
            $table->float('cpu_usage')->default(0);
            $table->float('ram_usage')->default(0);
            $table->float('disk_usage')->default(0);
            $table->string('active_user')->nullable();
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_monitors');
    }
};
