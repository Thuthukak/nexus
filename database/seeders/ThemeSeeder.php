<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'primary'      => '#1E3A5F',
            'primary_text' => '#FFFFFF',
            'secondary'    => '#2E86AB',
            'accent'       => '#F39C12',
            'sidebar_bg'   => '#1E3A5F',
            'sidebar_text' => '#CBD5E1',
            'surface'      => '#FFFFFF',
            'background'   => '#F8F9FA',
            'text'         => '#2C3E50',
            'dark_mode'    => false,
        ];

        foreach ($defaults as $key => $value) {
            DB::table('settings')->upsert(
                [
                    'group'      => 'theme',
                    'key'        => $key,
                    'value'      => json_encode($value),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                ['group', 'key'],
                ['value', 'updated_at']
            );
        }
    }
}
