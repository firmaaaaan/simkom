<?php

namespace Database\Seeders;

use App\Models\Software;
use Illuminate\Database\Seeder;

class SoftwareSeeder extends Seeder
{
    public function run(): void
    {
        $software = [
            // Operating System
            ['name' => 'Windows 11 Pro', 'code' => 'SW-OS-001', 'version' => '23H2', 'license_type' => 'OEM', 'category' => 'Operating System', 'license_count' => 100, 'status' => 'Aktif', 'description' => 'Sistem operasi Windows 11 Pro lisensi OEM'],
            ['name' => 'Windows 10 Pro', 'code' => 'SW-OS-002', 'version' => '22H2', 'license_type' => 'OEM', 'category' => 'Operating System', 'license_count' => 50, 'status' => 'Aktif', 'description' => 'Sistem operasi Windows 10 Pro lisensi OEM'],
            ['name' => 'Ubuntu 22.04 LTS', 'code' => 'SW-OS-003', 'version' => '22.04', 'license_type' => 'Open Source', 'category' => 'Operating System', 'license_count' => 999, 'status' => 'Aktif', 'description' => 'Sistem operasi Linux Ubuntu'],

            // Office
            ['name' => 'Microsoft Office 2021', 'code' => 'SW-OFF-001', 'version' => '2021', 'license_type' => 'Volume License', 'category' => 'Office Suite', 'license_count' => 80, 'status' => 'Aktif', 'description' => 'Paket Office Word, Excel, PowerPoint, Outlook'],
            ['name' => 'Google Workspace', 'code' => 'SW-OFF-002', 'version' => 'Latest', 'license_type' => 'Cloud', 'category' => 'Office Suite', 'license_count' => 200, 'status' => 'Aktif', 'description' => 'Google Docs, Sheets, Slides'],

            // Development
            ['name' => 'Visual Studio Code', 'code' => 'SW-DEV-001', 'version' => '1.92', 'license_type' => 'Free', 'category' => 'Development', 'license_count' => 999, 'status' => 'Aktif', 'description' => 'Code editor dari Microsoft'],
            ['name' => 'Visual Studio 2022', 'code' => 'SW-DEV-002', 'version' => '2022', 'license_type' => 'Education', 'category' => 'Development', 'license_count' => 50, 'status' => 'Aktif', 'description' => 'IDE untuk pengembangan .NET'],
            ['name' => 'JetBrains IntelliJ IDEA', 'code' => 'SW-DEV-003', 'version' => '2024.1', 'license_type' => 'Education', 'category' => 'Development', 'license_count' => 50, 'status' => 'Aktif', 'description' => 'IDE untuk Java Development'],
            ['name' => 'XAMPP', 'code' => 'SW-DEV-004', 'version' => '8.2', 'license_type' => 'Free', 'category' => 'Development', 'license_count' => 999, 'status' => 'Aktif', 'description' => 'Local server Apache + MySQL + PHP'],

            // Browser
            ['name' => 'Google Chrome', 'code' => 'SW-BRW-001', 'version' => 'Latest', 'license_type' => 'Free', 'category' => 'Browser', 'license_count' => 999, 'status' => 'Aktif', 'description' => 'Web browser dari Google'],
            ['name' => 'Mozilla Firefox', 'code' => 'SW-BRW-002', 'version' => 'Latest', 'license_type' => 'Free', 'category' => 'Browser', 'license_count' => 999, 'status' => 'Aktif', 'description' => 'Web browser open source'],

            // Multimedia
            ['name' => 'Adobe Photoshop 2024', 'code' => 'SW-MUL-001', 'version' => '2024', 'license_type' => 'Subscription', 'category' => 'Multimedia', 'license_count' => 20, 'status' => 'Aktif', 'description' => 'Software editing gambar'],
            ['name' => 'Adobe Premiere Pro 2024', 'code' => 'SW-MUL-002', 'version' => '2024', 'license_type' => 'Subscription', 'category' => 'Multimedia', 'license_count' => 15, 'status' => 'Aktif', 'description' => 'Software editing video'],
            ['name' => 'OBS Studio', 'code' => 'SW-MUL-003', 'version' => '30.0', 'license_type' => 'Free', 'category' => 'Multimedia', 'license_count' => 999, 'status' => 'Aktif', 'description' => 'Software screen recording dan streaming'],

            // Security
            ['name' => 'Kaspersky Endpoint Security', 'code' => 'SW-SEC-001', 'version' => '12.0', 'license_type' => 'Subscription', 'category' => 'Security', 'license_count' => 100, 'status' => 'Aktif', 'description' => 'Antivirus untuk komputer lab'],
            ['name' => 'Bitdefender GravityZone', 'code' => 'SW-SEC-002', 'version' => '6.0', 'license_type' => 'Subscription', 'category' => 'Security', 'license_count' => 100, 'status' => 'Expired', 'description' => 'Antivirus enterprise'],

            // Utility
            ['name' => '7-Zip', 'code' => 'SW-UTL-001', 'version' => '24.0', 'license_type' => 'Free', 'category' => 'Utility', 'license_count' => 999, 'status' => 'Aktif', 'description' => 'Software kompresi file'],
            ['name' => 'Adobe Acrobat Reader', 'code' => 'SW-UTL-002', 'version' => '2024', 'license_type' => 'Free', 'category' => 'Utility', 'license_count' => 999, 'status' => 'Aktif', 'description' => 'PDF reader dari Adobe'],
        ];

        foreach ($software as $item) {
            Software::create($item);
        }
    }
}
