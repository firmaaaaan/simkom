<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class JsaImport implements WithMultipleSheets
{
    public function __construct(protected K3ImportContext $context) {}

    public function sheets(): array
    {
        return [
            'JSA' => new JsaSheetImport($this->context),
            'Langkah Pekerjaan' => new JsaStepSheetImport($this->context),
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
