<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BorrowingExport extends BaseExport implements WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return $this->query->with(['computer', 'laboratory'])->get();
    }

    public function headings(): array
    {
        return [
            'Kode Tracking',
            'Peminjam',
            'NIM/NIDN',
            'Program Studi',
            'Komputer',
            'Laboratorium',
            'Tanggal Pinjam',
            'Jam Mulai',
            'Jam Selesai',
            'Keperluan',
            'Status',
            'Catatan Admin',
            'Diajukan',
        ];
    }

    public function map($borrowing): array
    {
        return [
            $borrowing->tracking_code,
            $borrowing->borrower_name,
            $borrowing->borrower_nim,
            $borrowing->borrower_prodi,
            $borrowing->computer->code ?? '-',
            $borrowing->laboratory->name ?? '-',
            $borrowing->borrow_date ? \Carbon\Carbon::parse($borrowing->borrow_date)->format('d/m/Y') : '-',
            $borrowing->borrow_time_start,
            $borrowing->borrow_time_end,
            $borrowing->purpose,
            $borrowing->status,
            $borrowing->admin_notes,
            $borrowing->created_at?->format('d/m/Y H:i'),
        ];
    }
}
