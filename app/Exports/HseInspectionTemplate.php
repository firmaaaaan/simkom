<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HseInspectionTemplate implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new HseSheetTemplate,
            new HseFindingTemplate,
        ];
    }
}
