<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivityLogExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $logs;

    public function __construct($logs)
    {
        $this->logs = $logs;
    }

    public function collection()
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return [
            'No',
            'Waktu',
            'Akun',
            'Email',
            'Method',
            'URL',
            'Deskripsi',
            'IP Address',
            'User Agent',
        ];
    }

    public function map($log): array
    {
        static $no = 1;

        return [
            $no++,
            $log->created_at ? $log->created_at->format('d F Y H:i:s') : '-',
            $log->user->name ?? 'Guest',
            $log->user->email ?? '-',
            $log->method ?? '-',
            $log->url ?? '-',
            $log->description ?? '-',
            $log->ip_address ?? '-',
            $log->user_agent ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
