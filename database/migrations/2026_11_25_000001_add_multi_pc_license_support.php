<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('software_licenses', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_devices')->default(1);
        });

        $assignments = DB::table('software_licenses')
            ->whereNotNull('assigned_computer_id')
            ->get(['id', 'assigned_computer_id'])
            ->map(fn ($row) => [
                'software_license_id' => $row->id,
                'computer_id' => $row->assigned_computer_id,
            ])
            ->all();

        $this->dropAssignedComputerColumn();

        Schema::create('software_license_computer', function (Blueprint $table) {
            $table->uuid('software_license_id');
            $table->uuid('computer_id');
            $table->timestamps();

            $table->primary(['software_license_id', 'computer_id']);
            $table->foreign('software_license_id')->references('id')->on('software_licenses')->cascadeOnDelete();
            $table->foreign('computer_id')->references('id')->on('computers')->cascadeOnDelete();
        });

        foreach ($assignments as $assignment) {
            DB::table('software_license_computer')->insert(array_merge($assignment, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::table('software_licenses', function (Blueprint $table) {
            $table->uuid('assigned_computer_id')->nullable()->after('license_key');
        });

        $firstComputers = DB::table('software_license_computer')
            ->groupBy('software_license_id')
            ->select('software_license_id', DB::raw('MIN(computer_id) as computer_id'))
            ->get();

        foreach ($firstComputers as $row) {
            DB::table('software_licenses')
                ->where('id', $row->software_license_id)
                ->update(['assigned_computer_id' => $row->computer_id]);
        }

        Schema::dropIfExists('software_license_computer');

        Schema::table('software_licenses', function (Blueprint $table) {
            $table->dropColumn('max_devices');
            $table->index('assigned_computer_id');
            $table->unique(['software_id', 'assigned_computer_id']);
        });
    }

    private function dropAssignedComputerColumn(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->rebuildSoftwareLicensesTable();

            return;
        }

        Schema::table('software_licenses', function (Blueprint $table) {
            $table->dropForeign(['assigned_computer_id']);
            $table->dropUnique(['software_id', 'assigned_computer_id']);
            $table->dropIndex(['assigned_computer_id']);
            $table->dropColumn('assigned_computer_id');
        });
    }

    /**
     * SQLite tidak mendukung drop kolom yang masih terikat foreign key
     * inline, sehingga tabel harus dibuat ulang tanpa kolom tersebut.
     * Index dibuat setelah RENAME karena nama index di SQLite bersifat
     * global dan tidak ikut berubah saat tabel di-rename.
     */
    private function rebuildSoftwareLicensesTable(): void
    {
        DB::statement('DROP TABLE IF EXISTS software_licenses_rebuild');

        DB::statement('
            CREATE TABLE software_licenses_rebuild (
                id TEXT PRIMARY KEY,
                software_id TEXT NOT NULL,
                label TEXT,
                license_key TEXT,
                max_devices INTEGER NOT NULL DEFAULT 1,
                purchase_date DATE,
                expiry_date DATE,
                notes TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (software_id) REFERENCES software (id) ON DELETE CASCADE
            )
        ');

        DB::statement('
            INSERT INTO software_licenses_rebuild
                (id, software_id, label, license_key, max_devices, purchase_date, expiry_date, notes, created_at, updated_at)
            SELECT id, software_id, label, license_key, max_devices, purchase_date, expiry_date, notes, created_at, updated_at
            FROM software_licenses
        ');

        DB::statement('DROP TABLE software_licenses');
        DB::statement('ALTER TABLE software_licenses_rebuild RENAME TO software_licenses');
        DB::statement('
            CREATE INDEX software_licenses_software_id_index
            ON software_licenses (software_id)
        ');
    }
};
