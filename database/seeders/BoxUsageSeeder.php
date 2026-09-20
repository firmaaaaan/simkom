<?php

namespace Database\Seeders;

use App\Models\Box;
use App\Models\BoxUsage;
use Illuminate\Database\Seeder;

class BoxUsageSeeder extends Seeder
{
    public function run(): void
    {
        $boxes = Box::all();

        if ($boxes->isEmpty()) {
            return;
        }

        $usages = [
            [
                'user_name' => 'Budi Santoso',
                'user_nim'  => '12345678',
                'status'    => 'Returned',
                'used_at'   => now()->subDays(3)->setTime(9, 0),
                'returned_at' => now()->subDays(3)->setTime(12, 30),
            ],
            [
                'user_name' => 'Budi Santoso',
                'user_nim'  => '12345678',
                'status'    => 'Using',
                'used_at'   => now()->subHours(2),
                'returned_at' => null,
            ],
            [
                'user_name' => 'Ahmad Fauzi',
                'user_nim'  => '87654321',
                'status'    => 'Returned',
                'used_at'   => now()->subDays(2)->setTime(13, 0),
                'returned_at' => now()->subDays(2)->setTime(15, 45),
            ],
            [
                'user_name' => 'Rina Wulandari',
                'user_nim'  => '11223344',
                'status'    => 'Returned',
                'used_at'   => now()->subDay()->setTime(10, 0),
                'returned_at' => now()->subDay()->setTime(11, 30),
            ],
        ];

        foreach ($usages as $index => $usage) {
            $box = $boxes[$index % $boxes->count()];
            BoxUsage::create(array_merge($usage, [
                'box_id' => $box->id,
            ]));
        }
    }
}
