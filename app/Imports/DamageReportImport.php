<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DamageReportImport implements WithMultipleSheets
{
    public function __construct(protected K3ImportContext $context) {}

    public function sheets(): array
    {
        return [
            'Laporan' => new DamageSheetImport($this->context),
            'Peralatan' => new DamageEquipmentSheetImport($this->context),
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
