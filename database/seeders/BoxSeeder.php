<?php

namespace Database\Seeders;

use App\Models\Box;
use App\Models\BoxComponent;
use App\Models\Component;
use Illuminate\Database\Seeder;

class BoxSeeder extends Seeder
{
    public function run(): void
    {
        $boxes = [
            ['name' => 'Box Sensor IoT',   'code' => 'BOX-001', 'location' => 'Rak A-1'],
            ['name' => 'Box Sensor IoT',   'code' => 'BOX-002', 'location' => 'Rak A-1'],
            ['name' => 'Box Mikrokontroler','code' => 'BOX-003', 'location' => 'Rak A-2'],
            ['name' => 'Box Kabel Jaringan','code' => 'BOX-004', 'location' => 'Rak B-1'],
            ['name' => 'Box Kabel Jaringan','code' => 'BOX-005', 'location' => 'Rak B-1'],
            ['name' => 'Box Peralatan',    'code' => 'BOX-006', 'location' => 'Rak C-1'],
        ];

        foreach ($boxes as $box) {
            Box::create($box);
        }

        // Assign components to boxes (reduce stock)
        $assignments = [
            // BOX-001: Box Sensor IoT (Rak A-1)
            ['box_code' => 'BOX-001', 'component_code' => 'KMP-005', 'quantity' => 5],  // DHT22
            ['box_code' => 'BOX-001', 'component_code' => 'KMP-006', 'quantity' => 3],  // Ultrasonik
            ['box_code' => 'BOX-001', 'component_code' => 'KMP-009', 'quantity' => 2],  // OLED

            // BOX-002: Box Sensor IoT (Rak A-1)
            ['box_code' => 'BOX-002', 'component_code' => 'KMP-005', 'quantity' => 3],  // DHT22
            ['box_code' => 'BOX-002', 'component_code' => 'KMP-007', 'quantity' => 4],  // Relay

            // BOX-003: Box Mikrokontroler (Rak A-2)
            ['box_code' => 'BOX-003', 'component_code' => 'KMP-001', 'quantity' => 5],  // Arduino
            ['box_code' => 'BOX-003', 'component_code' => 'KMP-002', 'quantity' => 3],  // NodeMCU
            ['box_code' => 'BOX-003', 'component_code' => 'KMP-003', 'quantity' => 2],  // ESP32
            ['box_code' => 'BOX-003', 'component_code' => 'KMP-010', 'quantity' => 5],  // Breadboard

            // BOX-004: Box Kabel Jaringan (Rak B-1)
            ['box_code' => 'BOX-004', 'component_code' => 'KMP-105', 'quantity' => 20], // UTP Cat6
            ['box_code' => 'BOX-004', 'component_code' => 'KMP-107', 'quantity' => 50], // RJ45

            // BOX-005: Box Kabel Jaringan (Rak B-1)
            ['box_code' => 'BOX-005', 'component_code' => 'KMP-105', 'quantity' => 15], // UTP Cat6
            ['box_code' => 'BOX-005', 'component_code' => 'KMP-107', 'quantity' => 30], // RJ45

            // BOX-006: Box Peralatan (Rak C-1)
            ['box_code' => 'BOX-006', 'component_code' => 'KMP-106', 'quantity' => 3],  // Crimper
            ['box_code' => 'BOX-006', 'component_code' => 'KMP-108', 'quantity' => 2],  // Cable Tester
            ['box_code' => 'BOX-006', 'component_code' => 'KMP-109', 'quantity' => 1],  // Patch Panel
        ];

        foreach ($assignments as $a) {
            $box = Box::where('code', $a['box_code'])->first();
            $component = Component::where('code', $a['component_code'])->first();

            if ($box && $component && $component->quantity >= $a['quantity']) {
                BoxComponent::create([
                    'box_id'       => $box->id,
                    'component_id' => $component->id,
                    'quantity'     => $a['quantity'],
                ]);
                $component->decrement('quantity', $a['quantity']);
            }
        }
    }
}
