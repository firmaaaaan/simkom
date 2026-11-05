<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class K3InspectionReportTemplate implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new K3InspectionSheetTemplate,
            new K3ChecklistTemplate,
            new K3FindingTemplate,
        ];
    }
}
