<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TicketExport extends BaseExport implements WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return $this->query
            ->with(['laboratory', 'computer', 'academicYear', 'reporter', 'assignee'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode Tracking',
            'Judul Kendala',
            'Komputer',
            'Laboratorium',
            'Kategori',
            'Prioritas',
            'Status',
            'Tahun Ajaran',
            'Pelapor',
            'Ditangani Oleh',
            'Deskripsi',
            'Dilaporkan',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->tracking_code,
            $ticket->title,
            $ticket->computer->code ?? '-',
            $ticket->laboratory->name ?? '-',
            $ticket->category,
            $ticket->priority,
            $ticket->status,
            $ticket->academicYear->name ?? '-',
            $ticket->reporter_name ?: ($ticket->reporter->name ?? '-'),
            $ticket->assignee->name ?? '-',
            $ticket->description,
            $ticket->created_at?->format('d/m/Y H:i'),
        ];
    }
}
