<?php

namespace App\Imports;

/**
 * State bersama antar-sheet import multi-sheet modul K3:
 * jumlah baris berhasil dan daftar error per baris.
 */
class K3ImportContext
{
    public int $importedCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }
}
