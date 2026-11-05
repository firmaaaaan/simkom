<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class K3InspectionReportImport implements WithMultipleSheets
{
    public function __construct(protected K3ImportContext $context) {}

    public function sheets(): array
    {
        return [
            'Laporan' => new K3InspectionSheetImport($this->context),
            'Checklist' => new K3ChecklistSheetImport($this->context),
            'Temuan' => new K3FindingSheetImport($this->context),
        ];
    }

    public function getImportedCount(): int
    {
        return $this->context->importedCount;
    }

    public function getErrors(): array
    {
        return $this->context->errors;
    }
}
