<?php

namespace App\Imports;

class SoftwareImportContext
{
    public int $softwareCount = 0;

    public int $licenseCount = 0;

    /** @var string[] */
    public array $errors = [];
}
