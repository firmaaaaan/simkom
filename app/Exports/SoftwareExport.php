<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SoftwareExport implements WithMultipleSheets
{
    public function __construct(
        private bool $includeKeys = false,
        private bool $withData = true,
    ) {
    }

    public function sheets(): array
    {
        return [
            new SoftwareSheet($this->withData),
            new SoftwareLicenseSheet($this->includeKeys, $this->withData),
        ];
    }
}
