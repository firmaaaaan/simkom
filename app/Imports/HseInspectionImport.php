<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HseInspectionImport implements WithMultipleSheets
{
    public function __construct(protected K3ImportContext $context) {}

    public function sheets(): array
    {
        return [
            'HSE' => new HseSheetImport($this->context),
            'Temuan' => new HseFindingSheetImport($this->context),
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
