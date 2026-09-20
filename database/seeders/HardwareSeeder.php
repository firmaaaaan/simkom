<?php

namespace Database\Seeders;

use App\Models\Hardware;
use Illuminate\Database\Seeder;

class HardwareSeeder extends Seeder
{
    public function run(): void
    {
        $hardware = [
            // Processor
            ['name' => 'Intel Core i5-12400', 'code' => 'HW-PROC-001', 'brand' => 'Intel', 'model' => 'i5-12400', 'category' => 'Processor', 'quantity' => 50, 'status' => 'Tersedia', 'description' => 'Processor Intel 12th Gen 6 Core 12 Thread'],
            ['name' => 'Intel Core i7-13700K', 'code' => 'HW-PROC-002', 'brand' => 'Intel', 'model' => 'i7-13700K', 'category' => 'Processor', 'quantity' => 30, 'status' => 'Digunakan', 'description' => 'Processor Intel 13th Gen 16 Core 24 Thread'],
            ['name' => 'AMD Ryzen 5 5600X', 'code' => 'HW-PROC-003', 'brand' => 'AMD', 'model' => 'Ryzen 5 5600X', 'category' => 'Processor', 'quantity' => 40, 'status' => 'Tersedia', 'description' => 'Processor AMD Zen 3 6 Core 12 Thread'],

            // RAM
            ['name' => 'Corsair Vengeance 8GB DDR4', 'code' => 'HW-RAM-001', 'brand' => 'Corsair', 'model' => 'Vengeance LPX', 'category' => 'RAM', 'quantity' => 100, 'status' => 'Tersedia', 'description' => 'RAM DDR4 3200MHz 8GB'],
            ['name' => 'Kingston Fury Beast 16GB DDR5', 'code' => 'HW-RAM-002', 'brand' => 'Kingston', 'model' => 'Fury Beast', 'category' => 'RAM', 'quantity' => 60, 'status' => 'Tersedia', 'description' => 'RAM DDR5 5200MHz 16GB'],
            ['name' => 'G.Skill Ripjaws 32GB DDR4', 'code' => 'HW-RAM-003', 'brand' => 'G.Skill', 'model' => 'Ripjaws V', 'category' => 'RAM', 'quantity' => 20, 'status' => 'Digunakan', 'description' => 'RAM DDR4 3600MHz 32GB'],

            // Storage
            ['name' => 'Samsung 970 EVO Plus 512GB', 'code' => 'HW-STOR-001', 'brand' => 'Samsung', 'model' => '970 EVO Plus', 'category' => 'Storage', 'quantity' => 80, 'status' => 'Tersedia', 'description' => 'NVMe SSD 512GB Read 3500MB/s'],
            ['name' => 'WD Blue 1TB', 'code' => 'HW-STOR-002', 'brand' => 'Western Digital', 'model' => 'WD Blue', 'category' => 'Storage', 'quantity' => 50, 'status' => 'Tersedia', 'description' => 'HDD 1TB 7200RPM'],
            ['name' => 'Kingston A2000 1TB NVMe', 'code' => 'HW-STOR-003', 'brand' => 'Kingston', 'model' => 'A2000', 'category' => 'Storage', 'quantity' => 40, 'status' => 'Digunakan', 'description' => 'NVMe SSD 1TB Read 2000MB/s'],

            // Motherboard
            ['name' => 'ASUS PRIME B660M-K', 'code' => 'HW-MB-001', 'brand' => 'ASUS', 'model' => 'PRIME B660M-K', 'category' => 'Motherboard', 'quantity' => 50, 'status' => 'Tersedia', 'description' => 'Motherboard LGA 1700 Micro-ATX'],
            ['name' => 'MSI MAG B550 TOMAHAWK', 'code' => 'HW-MB-002', 'brand' => 'MSI', 'model' => 'MAG B550 TOMAHAWK', 'category' => 'Motherboard', 'quantity' => 30, 'status' => 'Tersedia', 'description' => 'Motherboard AM4 ATX'],

            // Monitor
            ['name' => 'LG 24MP400', 'code' => 'HW-MON-001', 'brand' => 'LG', 'model' => '24MP400', 'category' => 'Monitor', 'quantity' => 60, 'status' => 'Tersedia', 'description' => 'Monitor 24 inch FHD IPS'],
            ['name' => 'Samsung Odyssey G5 27"', 'code' => 'HW-MON-002', 'brand' => 'Samsung', 'model' => 'Odyssey G5', 'category' => 'Monitor', 'quantity' => 20, 'status' => 'Digunakan', 'description' => 'Monitor Gaming 27 inch QHD 165Hz'],

            // Keyboard
            ['name' => 'Logitech K120', 'code' => 'HW-KBD-001', 'brand' => 'Logitech', 'model' => 'K120', 'category' => 'Keyboard', 'quantity' => 80, 'status' => 'Tersedia', 'description' => 'Keyboard USB Wired'],
            ['name' => 'Razer BlackWidow V3', 'code' => 'HW-KBD-002', 'brand' => 'Razer', 'model' => 'BlackWidow V3', 'category' => 'Keyboard', 'quantity' => 25, 'status' => 'Tersedia', 'description' => 'Keyboard Mechanical RGB'],

            // Mouse
            ['name' => 'Logitech M90', 'code' => 'HW-MOU-001', 'brand' => 'Logitech', 'model' => 'M90', 'category' => 'Mouse', 'quantity' => 80, 'status' => 'Tersedia', 'description' => 'Mouse USB Wired'],
            ['name' => 'Razer DeathAdder V2', 'code' => 'HW-MOU-002', 'brand' => 'Razer', 'model' => 'DeathAdder V2', 'category' => 'Mouse', 'quantity' => 25, 'status' => 'Tersedia', 'description' => 'Mouse Gaming Ergonomis'],

            // Printer
            ['name' => 'HP LaserJet Pro M15w', 'code' => 'HW-PRT-001', 'brand' => 'HP', 'model' => 'LaserJet Pro M15w', 'category' => 'Printer', 'quantity' => 10, 'status' => 'Tersedia', 'description' => 'Printer Laser Monokrom Wireless'],
            ['name' => 'Canon PIXMA G2020', 'code' => 'HW-PRT-002', 'brand' => 'Canon', 'model' => 'PIXMA G2020', 'category' => 'Printer', 'quantity' => 8, 'status' => 'Digunakan', 'description' => 'Printer Inkjet Multifungsi'],

            // Speaker
            ['name' => 'Logitech Z120', 'code' => 'HW-SPK-001', 'brand' => 'Logitech', 'model' => 'Z120', 'category' => 'Speaker', 'quantity' => 40, 'status' => 'Tersedia', 'description' => 'Speaker 2.0 Stereo USB'],
        ];

        foreach ($hardware as $item) {
            Hardware::create($item);
        }
    }
}
