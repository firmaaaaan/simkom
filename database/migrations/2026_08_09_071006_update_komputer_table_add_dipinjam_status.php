<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('komputer_new', function (Blueprint $table) {
                $table->id();
                $table->string('kode_komputer')->unique();
                $table->string('nama_komputer');
                $table->foreignId('laboratorium_id')->nullable()->constrained('laboratorium')->nullOnDelete();
                $table->string('foto')->nullable();
                $table->text('spesifikasi')->nullable();
                $table->enum('status', ['aktif', 'tidak_aktif', 'perbaikan', 'rusak', 'dipinjam'])->default('aktif');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });

            $rows = DB::table('komputer')->select('id', 'kode_komputer', 'nama_komputer', 'laboratorium_id', 'foto', 'spesifikasi', 'status', 'catatan', 'created_at', 'updated_at')->get();

            DB::table('komputer_new')->insert($rows->map(function ($row) {
                return (array) $row;
            })->toArray());

            Schema::dropIfExists('komputer');

            Schema::rename('komputer_new', 'komputer');

            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('komputer', function (Blueprint $table) {
                $table->enum('status', ['aktif', 'tidak_aktif', 'perbaikan', 'rusak', 'dipinjam'])->default('aktif')->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('komputer_new', function (Blueprint $table) {
                $table->id();
                $table->string('kode_komputer')->unique();
                $table->string('nama_komputer');
                $table->foreignId('laboratorium_id')->nullable()->constrained('laboratorium')->nullOnDelete();
                $table->string('foto')->nullable();
                $table->text('spesifikasi')->nullable();
                $table->enum('status', ['aktif', 'tidak_aktif', 'perbaikan', 'rusak'])->default('aktif');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });

            $rows = DB::table('komputer')->select('id', 'kode_komputer', 'nama_komputer', 'laboratorium_id', 'foto', 'spesifikasi', 'status', 'catatan', 'created_at', 'updated_at')->get();

            DB::table('komputer_new')->insert($rows->map(function ($row) {
                return (array) $row;
            })->toArray());

            Schema::dropIfExists('komputer');

            Schema::rename('komputer_new', 'komputer');

            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('komputer', function (Blueprint $table) {
                $table->enum('status', ['aktif', 'tidak_aktif', 'perbaikan', 'rusak'])->default('aktif')->change();
            });
        }
    }
};
