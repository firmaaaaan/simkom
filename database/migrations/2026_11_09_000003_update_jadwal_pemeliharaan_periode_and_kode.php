<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_pemeliharaans', function (Blueprint $table) {
            if (! Schema::hasColumn('jadwal_pemeliharaans', 'academic_year_id')) {
                $table->uuid('academic_year_id')->nullable()->after('laboratorium_name');
            }
        });

        Schema::table('jadwal_pemeliharaans', function (Blueprint $table) {
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->index('academic_year_id');
        });

        Schema::table('jadwal_pemeliharaan_items', function (Blueprint $table) {
            $table->text('kode_inventaris')->nullable()->change();
        });

        $this->backfillAcademicYears();
    }

    public function down(): void
    {
        Schema::table('jadwal_pemeliharaan_items', function (Blueprint $table) {
            $table->string('kode_inventaris', 100)->nullable()->change();
        });

        Schema::table('jadwal_pemeliharaans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('academic_year_id');
            $table->dropIndex(['academic_year_id']);
        });
    }

    /**
     * Konversi otomatis: cocokkan teks periode lama ke academic_years.name.
     * Yang tidak cocok tetap null; teks periode lama tetap tampil sebagai snapshot.
     */
    private function backfillAcademicYears(): void
    {
        $years = DB::table('academic_years')->get(['id', 'name']);
        $rows = DB::table('jadwal_pemeliharaans')->whereNull('academic_year_id')->get(['id', 'periode']);

        foreach ($rows as $row) {
            $periode = trim((string) $row->periode);
            if ($periode === '') {
                continue;
            }

            $match = null;
            foreach ($years as $year) {
                $name = trim((string) $year->name);
                if ($name === $periode || str_contains($periode, $name) || str_contains($name, $periode)) {
                    $match = $year;
                    break;
                }
            }

            if ($match) {
                DB::table('jadwal_pemeliharaans')
                    ->where('id', $row->id)
                    ->update(['academic_year_id' => $match->id]);
            }
        }
    }
};
