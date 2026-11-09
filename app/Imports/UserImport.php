<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToModel, WithHeadingRow
{
    protected int $importedCount = 0;

    protected array $errors = [];

    /** @var array<string, Role>|null */
    protected ?array $roleMap = null;

    public function model(array $row)
    {
        if ($this->isBlankRow($row)) {
            return null;
        }

        $nama = $this->text($row['nama'] ?? null);
        $email = $this->text($row['email'] ?? null);

        if ($this->isContohRow($nama, $email)) {
            return null;
        }

        $rowNumber = $this->importedCount + count($this->errors) + 2;

        try {
            if ($nama === '') {
                $this->errors[] = "Baris {$rowNumber}: Nama wajib diisi.";

                return null;
            }

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = "Baris {$rowNumber}: Email wajib diisi dan harus valid.";

                return null;
            }

            $password = $this->text($row['password'] ?? null);
            $roleValue = $this->text($row['role'] ?? null);
            $role = $this->resolveRole($roleValue);

            if ($roleValue === '' || ! $role) {
                $this->errors[] = "Baris {$rowNumber}: Role wajib diisi dan harus sesuai nama/label role yang tersedia.";

                return null;
            }

            $user = User::where('email', $email)->first();
            $isNew = ! $user;

            if ($isNew) {
                if ($password === '') {
                    $this->errors[] = "Baris {$rowNumber}: Password wajib diisi untuk pengguna baru.";

                    return null;
                }

                if (strlen($password) < 8) {
                    $this->errors[] = "Baris {$rowNumber}: Password minimal 8 karakter.";

                    return null;
                }

                $user = new User([
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make($password),
                ]);
                $user->save();
            } else {
                $user->name = $nama;
                if ($password !== '') {
                    if (strlen($password) < 8) {
                        $this->errors[] = "Baris {$rowNumber}: Password minimal 8 karakter.";

                        return null;
                    }
                    $user->password = Hash::make($password);
                }
                $user->save();
            }

            $user->roles()->sync([$role->id]);
            $this->importedCount++;

            return $user;
        } catch (\Throwable $e) {
            $this->errors[] = "Baris {$rowNumber}: ".$e->getMessage();

            return null;
        }
    }

    private function text(mixed $value): string
    {
        if (is_array($value)) {
            return '';
        }

        return trim((string) ($value ?? ''));
    }

    private function isBlankRow(array $row): bool
    {
        foreach ($row as $value) {
            if (is_array($value)) {
                continue;
            }
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function isContohRow(string $nama, string $email): bool
    {
        if (strcasecmp($nama, 'Contoh') === 0) {
            return true;
        }

        return strcasecmp($email, 'contoh@unisayogya.ac.id') === 0;
    }

    private function resolveRole(string $value): ?Role
    {
        if ($value === '') {
            return null;
        }

        if ($this->roleMap === null) {
            $this->roleMap = [];
            foreach (Role::all() as $role) {
                $this->roleMap[mb_strtolower((string) $role->name)] = $role;
                if (! empty($role->label)) {
                    $this->roleMap[mb_strtolower((string) $role->label)] = $role;
                }
            }
        }

        return $this->roleMap[mb_strtolower($value)] ?? null;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
