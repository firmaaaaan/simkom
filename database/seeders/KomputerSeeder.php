<?php

namespace Database\Seeders;

use App\Models\Hardware;
use App\Models\Komputer;
use App\Models\Laboratorium;
use App\Models\Software;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KomputerSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $labKomputer = Laboratorium::where('kode_laboratorium', 'LAB-001')->first();
        $labDatabase = Laboratorium::where('kode_laboratorium', 'LAB-006')->first();
        $labAI = Laboratorium::where('kode_laboratorium', 'LAB-007')->first();
        $labDesain = Laboratorium::where('kode_laboratorium', 'LAB-008')->first();

        $hw = [
            'processor'   => Hardware::where('kode_hardware', 'HARD-008')->first(),
            'ram8'        => Hardware::where('kode_hardware', 'HARD-001')->first(),
            'ram16'       => Hardware::where('kode_hardware', 'HARD-010')->first(),
            'vga'         => Hardware::where('kode_hardware', 'HARD-002')->first(),
            'ssd'         => Hardware::where('kode_hardware', 'HARD-003')->first(),
            'hdd'         => Hardware::where('kode_hardware', 'HARD-009')->first(),
            'monitor'     => Hardware::where('kode_hardware', 'HARD-004')->first(),
            'keyboard'    => Hardware::where('kode_hardware', 'HARD-005')->first(),
            'mouse'       => Hardware::where('kode_hardware', 'HARD-006')->first(),
            'motherboard' => Hardware::where('kode_hardware', 'HARD-007')->first(),
        ];

        $sw = [
            'windows'     => Software::where('kode_software', 'SOFT-001')->first(),
            'office'      => Software::where('kode_software', 'SOFT-002')->first(),
            'vscode'      => Software::where('kode_software', 'SOFT-003')->first(),
            'packet'      => Software::where('kode_software', 'SOFT-004')->first(),
            'photoshop'   => Software::where('kode_software', 'SOFT-005')->first(),
            'workbench'   => Software::where('kode_software', 'SOFT-006')->first(),
            'defender'    => Software::where('kode_software', 'SOFT-007')->first(),
            'matlab'      => Software::where('kode_software', 'SOFT-008')->first(),
            'labview'     => Software::where('kode_software', 'SOFT-009')->first(),
            'pycharm'     => Software::where('kode_software', 'SOFT-010')->first(),
        ];

        $komputers = [
            [
                'nama_komputer' => 'PC Komputer 01',
                'laboratorium_id' => $labKomputer ? $labKomputer->id : null,
                'status' => 'aktif',
                'catatan' => 'PC standar untuk praktikum pemrograman dasar',
                'hardware' => ['processor', 'ram8', 'vga', 'ssd', 'hdd', 'monitor', 'keyboard', 'mouse'],
                'software' => ['windows', 'office', 'vscode', 'packet', 'defender', 'workbench'],
            ],
            [
                'nama_komputer' => 'PC Komputer 02',
                'laboratorium_id' => $labKomputer ? $labKomputer->id : null,
                'status' => 'aktif',
                'catatan' => 'PC untuk kelas A',
                'hardware' => ['processor', 'ram8', 'vga', 'ssd', 'monitor', 'keyboard', 'mouse'],
                'software' => ['windows', 'office', 'vscode', 'defender', 'workbench'],
            ],
            [
                'nama_komputer' => 'PC Database 01',
                'laboratorium_id' => $labDatabase ? $labDatabase->id : null,
                'status' => 'aktif',
                'catatan' => 'PC untuk praktikum database dan big data',
                'hardware' => ['processor', 'ram8', 'vga', 'ssd', 'monitor', 'keyboard', 'mouse'],
                'software' => ['windows', 'office', 'vscode', 'workbench', 'defender'],
            ],
            [
                'nama_komputer' => 'PC AI 01',
                'laboratorium_id' => $labAI ? $labAI->id : null,
                'status' => 'perbaikan',
                'catatan' => 'PC sedang perbaikan (overheating)',
                'hardware' => ['processor', 'ram8', 'vga', 'ssd', 'hdd', 'monitor', 'keyboard', 'mouse'],
                'software' => ['windows', 'office', 'vscode', 'pycharm', 'defender'],
            ],
            [
                'nama_komputer' => 'PC Desain 01',
                'laboratorium_id' => $labDesain ? $labDesain->id : null,
                'status' => 'aktif',
                'catatan' => 'PC untuk desain grafis dan animasi',
                'hardware' => ['processor', 'ram8', 'vga', 'ssd', 'monitor', 'keyboard', 'mouse'],
                'software' => ['windows', 'office', 'photoshop', 'defender'],
            ],
            [
                'nama_komputer' => 'PC Komputer 03',
                'laboratorium_id' => null,
                'status' => 'rusak',
                'catatan' => 'PC di storage, belum terpasang ke lab',
                'hardware' => ['processor', 'ram8', 'vga', 'ssd', 'monitor', 'keyboard', 'mouse'],
                'software' => ['windows', 'office', 'defender'],
            ],
        ];

        foreach ($komputers as $idx => $data) {
            $nextNumber = Komputer::max('id') + 1;
            $kode = 'KOM-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $hwKeys = $data['hardware'];
            $swKeys = $data['software'];

            $komputer = Komputer::create([
                'kode_komputer' => $kode,
                'nama_komputer' => $data['nama_komputer'],
                'laboratorium_id' => $data['laboratorium_id'],
                'status' => $data['status'],
                'catatan' => $data['catatan'],
            ]);

            $hwSync = [];
            foreach ($hwKeys as $key) {
                if (isset($hw[$key]) && $hw[$key]) {
                    $hwSync[$hw[$key]->id] = ['jumlah' => 1];
                }
            }
            $komputer->hardware()->sync($hwSync, false);

            $swAttach = [];
            foreach ($swKeys as $key) {
                if (isset($sw[$key]) && $sw[$key]) {
                    $swAttach[] = $sw[$key]->id;
                }
            }
            if (!empty($swAttach)) {
                $komputer->software()->attach($swAttach);
            }
        }
    }
}
