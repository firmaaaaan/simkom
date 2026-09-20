<?php

namespace Database\Seeders;

use App\Models\Computer;
use App\Models\Laboratory;
use App\Models\Hardware;
use App\Models\Software;
use Illuminate\Database\Seeder;

class ComputerSeeder extends Seeder
{
    public function run(): void
    {
        $labs = Laboratory::all();
        $hardware = Hardware::all();
        $software = Software::all();

        $labConfigs = [
            'LAB-KOM-01' => ['prefix' => 'K1', 'count' => 40],
            'LAB-KOM-02' => ['prefix' => 'K2', 'count' => 40],
            'LAB-KOM-03' => ['prefix' => 'K3', 'count' => 35],
            'LAB-MUL' => ['prefix' => 'ML', 'count' => 30],
            'LAB-NET' => ['prefix' => 'NT', 'count' => 25],
            'LAB-ROB' => ['prefix' => 'RB', 'count' => 20],
        ];

        $statuses = ['Aktif', 'Aktif', 'Aktif', 'Aktif', 'Maintenance'];

        foreach ($labs as $lab) {
            $config = $labConfigs[$lab->code] ?? ['prefix' => 'PC', 'count' => 10];
            $prefix = $config['prefix'];
            $count = $config['count'];

            $labHardware = $hardware->random(min(5, $hardware->count()));
            $labSoftware = $software->random(min(8, $software->count()));

            for ($i = 1; $i <= $count; $i++) {
                $computer = Computer::create([
                    'code' => $prefix . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'laboratory_id' => $lab->id,
                    'status' => $statuses[array_rand($statuses)],
                    'description' => "Komputer {$lab->name} unit ke-{$i}",
                ]);

                $computer->hardware()->sync($labHardware->pluck('id')->toArray());
                $computer->software()->sync($labSoftware->pluck('id')->toArray());
            }
        }
    }
}
