<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(['id' => Setting::PUBLIC_SPEC_ID], ['value' => '1']);
        Setting::updateOrCreate(['id' => Setting::REALTIME_SCHEDULE_URL_ID], ['value' => null]);
    }
}
