<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class K3InspectionReport extends Model
{
    use HasUuids;

    public const DOC_NUMBER = '005/K3 Lab/UNISA/XI/2024';

    public const DOC_DATE = '15 November 2024';

    public const PEMERIKSA_NAMA = 'Asih Puji Utami, S.KM., M.Kes.';

    public const PEMERIKSA_JABATAN = 'Koordinator K3 Laboratorium';

    public const MENGETAHUI_NAMA = 'Dr. Dhesi Ari Astuti, S.ST., M.Kes.';

    public const MENGETAHUI_JABATAN = 'Kepala Pusat Laboratorium Terpadu';

    public const MENGETAHUI_NAMA_PAGE5 = 'Dr. Dhesi Ari Astuti';

    public const MENGETAHUI_JABATAN_PAGE5 = 'Kepala UPT Laboratorium Unisa Yogyakarta';

    /** Checklist items from Form 05 pages 2-3. */
    public const CHECKLIST_ITEMS = [
        ['no' => 1, 'item' => 'Pernyataan komitmen K3 terpampang dengan jelas.'],
        ['no' => 2, 'item' => 'Denah gedung.'],
        ['no' => 3, 'item' => 'Dokumen identifikasi potensi bahaya dan penilaian risiko.'],
        ['no' => 4, 'item' => 'Dokumen prosedur tanggap darurat tiap ruang.'],
        ['no' => 5, 'item' => 'Poster rute evakuasi tiap ruangan.'],
        ['no' => 6, 'item' => 'Poster/petunjuk penggunaan APAR.'],
        ['no' => 7, 'item' => 'APAR dalam jumlah cukup.'],
        ['no' => 8, 'item' => 'APAR masih berfungsi.'],
        ['no' => 9, 'item' => 'APAR dilengkapi log book pemeriksaan yang diisi setiap bulan.'],
        ['no' => 10, 'item' => 'Poster informasi bencana.'],
        ['no' => 11, 'item' => 'Poster peringatan di lokasi berbahaya.'],
        ['no' => 12, 'item' => 'Poster/petunjuk tanggap darurat.'],
        ['no' => 13, 'item' => 'Sistem alarm manual/otomatis di setiap lantai.'],
        ['no' => 14, 'item' => 'Smoke/fire/heat detector atau detektor asap/panas/api minimal di setiap laboratorium.'],
        ['no' => 15, 'item' => 'Sprinkle di setiap ruangan laboratorium.'],
        ['no' => 16, 'item' => 'Hydrant di dalam/luar gedung.'],
        ['no' => 17, 'item' => 'Exit Sign terpasang di pintu keluar.'],
        ['no' => 18, 'item' => 'Rute penunjuk arah keluar di dinding/lantai.'],
        ['no' => 19, 'item' => 'Safety meeting/assembly point/titik kumpul.'],
        ['no' => 20, 'item' => 'Tangga dilengkapi dengan pegangan.'],
        ['no' => 21, 'item' => 'Tangga dilengkapi border/stoper di ujung setiap anak tangga untuk mencegah terpeleset.'],
        ['no' => 22, 'item' => 'Di setiap ruang laboratorium terdapat kotak P3K berisi :'],
        ['no' => 23, 'item' => 'Kotak P3K dilengkapi dengan logbook pemeriksaan yang diisi setiap bulan.'],
        ['no' => 24, 'item' => 'Nomor kontak ambulans yang terpampang dengan jelas.'],
        ['no' => 25, 'item' => 'Nomor kontak pemadam kebakaran terpampang jelas.'],
        ['no' => 26, 'item' => 'Area parkir khusus difabel.'],
        ['no' => 27, 'item' => 'Difable ramp di area tangga.'],
        ['no' => 28, 'item' => 'CCTV pada ruangan tertentu yang perlu pengawasan.'],
        ['no' => 29, 'item' => 'Bahan B3 diberi lambang dan label informasi.'],
        ['no' => 30, 'item' => 'Simulasi keadaan darurat.'],
        ['no' => 31, 'item' => 'Berita acara kecelakaan.'],
        ['no' => 32, 'item' => 'Safety lab induction'],
        ['no' => 33, 'item' => 'Rambu K3 Standar'],
    ];

    /** Sub-items for P3K (item 22) displayed for completeness. */
    public const P3K_ITEMS = [
        'Perban', 'Kasa steril', 'Betadine', 'Hansaplast/plester instan',
        'Plester gulung', 'Alkohol 70%', 'Sarung tangan lateks', 'Kapas',
    ];

    protected $fillable = [
        'kode',
        'hari',
        'tanggal_ba',
        'bulan_ba',
        'tahun_ba',
        'bulan_inspeksi',
        'lokasi',
        'pemeriksa_nama',
        'pemeriksa_jabatan',
        'checklist_items',
        'findings',
        'created_by',
    ];

    protected $casts = [
        'tanggal_ba' => 'date:Y-m-d',
        'checklist_items' => 'array',
        'findings' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (K3InspectionReport $report) {
            if (empty($report->kode)) {
                $report->kode = self::generateKode();
            }
        });
    }

    public static function generateKode(): string
    {
        do {
            $code = 'K3INSP-'.strtoupper(substr(uniqid(), -8));
        } while (static::where('kode', $code)->exists());

        return $code;
    }

    public static function defaultChecklist(): array
    {
        return collect(self::CHECKLIST_ITEMS)->map(fn ($item) => [
            'item_no' => $item['no'],
            'item_text' => $item['item'],
            'ya' => false,
            'tidak' => false,
            'na' => false,
            'catatan' => '',
        ])->values()->all();
    }

    public static function blankFinding(): array
    {
        return [
            'temuan' => '',
            'dokumentasi' => '',
            'saran_perbaikan' => '',
            'pelaksana' => '',
            'target' => '',
            'catatan' => '',
        ];
    }

    public static function normalizeChecklist(array $input): array
    {
        $items = [];

        foreach (self::CHECKLIST_ITEMS as $idx => $def) {
            $row = $input[$idx] ?? $input[$def['no']] ?? [];
            $ya = filter_var($row['ya'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $tidak = filter_var($row['tidak'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $na = filter_var($row['na'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // Hanya satu pilihan; prioritas jika keduanya terpilih (jarang terjadi)
            if ($ya && ($tidak || $na)) { $tidak = false; $na = false; }
            if ($tidak && $na) { $na = false; }

            $items[] = [
                'item_no' => $def['no'],
                'item_text' => $def['item'],
                'ya' => $ya,
                'tidak' => $tidak,
                'na' => $na,
                'catatan' => trim((string) ($row['catatan'] ?? '')),
            ];
        }

        return $items;
    }

    public static function normalizeFindings(array $input): array
    {
        $findings = [];

        foreach ($input as $row) {
            if (! is_array($row)) {
                continue;
            }

            $finding = [
                'temuan' => trim((string) ($row['temuan'] ?? '')),
                'dokumentasi' => trim((string) ($row['dokumentasi'] ?? '')),
                'saran_perbaikan' => trim((string) ($row['saran_perbaikan'] ?? '')),
                'pelaksana' => trim((string) ($row['pelaksana'] ?? '')),
                'target' => trim((string) ($row['target'] ?? '')),
                'catatan' => trim((string) ($row['catatan'] ?? '')),
            ];

            $isEmpty = collect($finding)->every(fn ($v) => $v === '');
            if (! $isEmpty) {
                $findings[] = $finding;
            }
        }

        return $findings;
    }

    public function getChecklistRowsAttribute(): array
    {
        $stored = $this->checklist_items ?? [];
        $rows = [];

        foreach (self::CHECKLIST_ITEMS as $idx => $def) {
            $row = $stored[$idx] ?? [];
            $rows[] = [
                'item_no' => $row['item_no'] ?? $def['no'],
                'item_text' => $row['item_text'] ?? $def['item'],
                'ya' => (bool) ($row['ya'] ?? false),
                'tidak' => (bool) ($row['tidak'] ?? false),
                'na' => (bool) ($row['na'] ?? false),
                'catatan' => (string) ($row['catatan'] ?? ''),
            ];
        }

        return $rows;
    }

    public function getFindingRowsAttribute(): array
    {
        return collect($this->findings ?? [])
            ->map(fn (array $row) => [
                'temuan' => (string) ($row['temuan'] ?? ''),
                'dokumentasi' => (string) ($row['dokumentasi'] ?? ''),
                'saran_perbaikan' => (string) ($row['saran_perbaikan'] ?? ''),
                'pelaksana' => (string) ($row['pelaksana'] ?? ''),
                'target' => (string) ($row['target'] ?? ''),
                'catatan' => (string) ($row['catatan'] ?? ''),
            ])
            ->values()
            ->all();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn (Builder $q) => $q
            ->where('kode', 'like', "%{$search}%")
            ->orWhere('lokasi', 'like', "%{$search}%")
            ->orWhere('bulan_inspeksi', 'like', "%{$search}%")
            ->orWhere('pemeriksa_nama', 'like', "%{$search}%"));
    }
}
