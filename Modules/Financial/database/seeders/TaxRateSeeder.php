<?php

declare(strict_types=1);

namespace Modules\Financial\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Financial\app\Models\TaxRate;

class TaxRateSeeder extends Seeder
{
    public function run(): void
    {
        TaxRate::firstOrCreate(
            ['name' => 'VAT 15%'],
            [
                'rate'        => 15.00,
                'is_compound' => false,
                'is_default'  => true,
                'is_active'   => true,
            ]
        );

        TaxRate::firstOrCreate(
            ['name' => 'Zero Rated'],
            [
                'rate'        => 0.00,
                'is_compound' => false,
                'is_default'  => false,
                'is_active'   => true,
            ]
        );

        TaxRate::firstOrCreate(
            ['name' => 'Exempt'],
            [
                'rate'        => 0.00,
                'is_compound' => false,
                'is_default'  => false,
                'is_active'   => true,
            ]
        );
    }
}
