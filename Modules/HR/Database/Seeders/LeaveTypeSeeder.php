<?php

namespace Modules\HR\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HR\app\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Annual Leave', 
            'Sick Leave', 
            'Family Responsibility', 
            'Unpaid Leave'
        ];

        foreach ($types as $name) {
            LeaveType::updateOrCreate(
                ['name' => $name], // Prevents duplicates if run multiple times
                [
                    'days_per_year'     => $this->getDaysPerYear($name),
                    'requires_approval' => true,
                    'is_paid'           => $name !== 'Unpaid Leave',
                    'is_active'         => true,
                ]
            );
        }
    }

    /**
     * Helper to keep the logic clean.
     */
    private function getDaysPerYear(string $name): int
    {
        return match ($name) {
            'Annual Leave'          => 15,
            'Sick Leave'            => 10,
            'Family Responsibility' => 3,
            default                 => 0,
        };
    }
}