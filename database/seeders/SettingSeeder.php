<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'ai_global_enabled' => '1',
            'ai_lead_brief_enabled' => '1',
            'ai_report_summary_enabled' => '1',
            'ai_model' => 'gemini-1.5-flash',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
