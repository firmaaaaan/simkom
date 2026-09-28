<?php

use App\Models\LabUsage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL dengan explicit_defaults_for_timestamp=OFF memberi kolom TIMESTAMP
        // pertama yang NOT NULL tanpa DEFAULT eksplisit: DEFAULT CURRENT_TIMESTAMP
        // ON UPDATE CURRENT_TIMESTAMP. Akibatnya setiap UPDATE baris (mis. Validasi
        // Keluar) menimpa checked_in_at dengan waktu check-out. DEFAULT eksplisit
        // mencegah atribut ON UPDATE implisit ditambahkan.
        Schema::table('lab_usages', function (Blueprint $table) {
            $table->timestamp('checked_in_at')->useCurrent()->change();
            // Validasi exit_note membolehkan 500 karakter; kolom lama varchar(255).
            $table->text('exit_note')->nullable()->change();
        });

        Schema::table('box_usages', function (Blueprint $table) {
            $table->timestamp('used_at')->useCurrent()->change();
        });

        // Repair data lama yang sudah terkorupsi: checked_in_at yang hampir sama
        // dengan validated_at (±2 detik) adalah bekas tertimpa ON UPDATE, bukan
        // check-in asli. Check-in asli ditulis pada request yang sama dengan
        // created_at, jadi created_at dipakai sebagai sumber pemulihan.
        LabUsage::query()
            ->where('status', 'Out')
            ->whereNotNull('validated_at')
            ->get()
            ->filter(fn (LabUsage $usage) => abs($usage->checked_in_at->diffInSeconds($usage->validated_at)) <= 2)
            ->each(function (LabUsage $usage) {
                $usage->checked_in_at = $usage->created_at;
                $usage->save();
            });
    }

    public function down(): void
    {
        Schema::table('lab_usages', function (Blueprint $table) {
            $table->timestamp('checked_in_at')->change();
            $table->string('exit_note')->nullable()->change();
        });

        Schema::table('box_usages', function (Blueprint $table) {
            $table->timestamp('used_at')->change();
        });

        // Repair data tidak dibalikkan: check-in asli baris terkorupsi
        // tidak dapat diketahui lagi setelah di-overwrite.
    }
};
