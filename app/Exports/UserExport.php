<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExport extends BaseExport implements WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return $this->query->with('roles')->get();
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'Role',
            'Jumlah Role',
            'Terdaftar',
        ];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->roles->map(fn ($role) => $role->label ?: $role->name)->implode(', ') ?: '-',
            $user->roles->count(),
            $user->created_at?->format('d/m/Y H:i'),
        ];
    }
}
