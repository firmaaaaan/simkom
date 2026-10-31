<?php

namespace App\Imports;

use App\Services\SoftwareLicenseSync;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SoftwareImport implements WithMultipleSheets
{
    private SoftwareImportContext $context;

    public function __construct()
    {
        $this->context = new SoftwareImportContext();
    }

    public function sheets(): array
    {
        return [
            'Software' => new SoftwareSheetImport($this->context),
            'Lisensi' => new SoftwareLicenseSheetImport($this->context, app(SoftwareLicenseSync::class)),
        ];
    }

    public function getImportedCount(): int
    {
        return $this->context->softwareCount;
    }

    public function getImportedLicenseCount(): int
    {
        return $this->context->licenseCount;
    }

    public function getErrors(): array
    {
        return $this->context->errors;
    }
}
