<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel telemetri PC lab (agent). Terpisah dari tabel "computers"
     * yang sudah ada (inventaris PC) agar data lama tidak tersentuh.
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
