<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DamageReportTemplate implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new DamageSheetTemplate,
            new DamageEquipmentTemplate,
        ];
    }
}
