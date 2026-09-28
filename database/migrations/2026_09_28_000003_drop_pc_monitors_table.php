<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fitur monitoring PC dihapus; tabel pc_monitors dibersihkan di
        // environment yang sudah pernah menjalankan migration create-nya.
        // Guard agar aman untuk fresh install (tabelnya tidak pernah dibuat).
        if (Schema::hasTable('pc_monitors')) {
            Schema::dropIfExists('pc_monitors');
        }
    }

    public function down(): void
    {
        // Tidak dikembalikan: tabel hasil fitur yang sudah dihapus dari kode.
    }
};
