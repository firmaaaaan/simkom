<?php

namespace App\Services;

use App\Models\Computer;
use App\Models\Software;

class SoftwareLicenseSync
{
    /**
     * Sinkronkan relasi computer_software dari seat lisensi.
     * PC yang mendapat lisensi otomatis terhubung ke software; PC yang
     * lisensinya dilepas akan dilepas hanya jika tidak ada lisensi lain
     * milik software yang sama untuk PC tersebut.
     */
    public function syncComputers(Software $software, array $oldComputerIds = []): void
    {
        $newComputerIds = $software->licenses()
            ->whereNotNull('assigned_computer_id')
            ->pluck('assigned_computer_id')
            ->unique()
            ->all();

        foreach ($newComputerIds as $computerId) {
            Computer::find($computerId)?->software()->syncWithoutDetaching([$software->id]);
        }

        foreach (array_diff($oldComputerIds, $newComputerIds) as $computerId) {
            $stillAssigned = $software->licenses()
                ->where('assigned_computer_id', $computerId)
                ->exists();

            if (! $stillAssigned) {
                Computer::find($computerId)?->software()->detach($software->id);
            }
        }
    }
}
