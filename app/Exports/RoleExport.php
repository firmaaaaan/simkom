<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RoleExport extends BaseExport implements WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return $this->query->with('permissions')->withCount(['permissions', 'users'])->get();
    }

    public function headings(): array
    {
        return [
            'Nama Role',
            'Label',
            'Jumlah User',
            'Jumlah Izin',
            'Daftar Izin',
        ];
    }

    public function map($role): array
    {
        return [
            $role->name,
            $role->label,
            $role->users_count,
            $role->permissions_count,
            $role->permissions->map(fn ($permission) => $permission->label ?: $permission->name)->implode(', ') ?: '-',
        ];
    }
}
