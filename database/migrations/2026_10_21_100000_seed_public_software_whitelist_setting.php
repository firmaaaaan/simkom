<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $names = DB::table('pc_softwares')
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $row = [
            'value' => json_encode($names),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Beberapa database lama punya kolom `key` NOT NULL di tabel settings.
        if (Schema::hasColumn('settings', 'key')) {
            $row['key'] = 'public_software_whitelist';
        }

        DB::table('settings')->updateOrInsert(['id' => 2], $row);
    }

    public function down(): void
    {
        DB::table('settings')->where('id', 2)->delete();
    }
};
