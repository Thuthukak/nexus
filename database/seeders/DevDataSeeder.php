<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DevDataSeeder extends Seeder
{
    public function run(): void
    {
        // Financial — test customers
        \Modules\Financial\app\Models\Customer::insert([
            [
                'id'           => \Illuminate\Support\Str::uuid(),
                'company_name' => 'Acme Corporation',
                'contact_name' => 'John Smith',
                'email'        => 'john@acme.co.za',
                'phone'        => '011 555 0100',
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => \Illuminate\Support\Str::uuid(),
                'company_name' => 'Globex Industries',
                'contact_name' => 'Jane Doe',
                'email'        => 'jane@globex.co.za',
                'phone'        => '021 555 0200',
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);

        // HR — departments
        $departments = ['Engineering', 'Finance', 'Human Resources', 'Sales', 'Operations'];
        foreach ($departments as $name) {
            \Modules\HR\app\Models\Department::create([
                'name'      => $name,
                'is_active' => true,
            ]);
        }

        // HR — job titles
        $titles = ['Software Engineer', 'Accountant', 'HR Manager', 'Sales Executive', 'Operations Manager'];
        foreach ($titles as $name) {
            \Modules\HR\app\Models\JobTitle::create([
                'name'      => $name,
                'is_active' => true,
            ]);
        }

        // HR — leave types
        $leaveTypes = [
            ['name' => 'Annual Leave',           'days_per_year' => 15, 'is_paid' => true],
            ['name' => 'Sick Leave',              'days_per_year' => 10, 'is_paid' => true],
            ['name' => 'Family Responsibility',   'days_per_year' => 3,  'is_paid' => true],
            ['name' => 'Unpaid Leave',            'days_per_year' => 0,  'is_paid' => false],
        ];
        foreach ($leaveTypes as $type) {
            \Modules\HR\app\Models\LeaveType::create([
                ...$type,
                'requires_approval' => true,
                'is_active'         => true,
            ]);
        }

        // Bookings — resources
        $resources = [
            ['name' => 'Conference Room A', 'type' => 'room',      'capacity' => 10, 'colour' => '#3B82F6'],
            ['name' => 'Conference Room B', 'type' => 'room',      'capacity' => 6,  'colour' => '#8B5CF6'],
            ['name' => 'Treatment Room 1',  'type' => 'room',      'capacity' => 1,  'colour' => '#10B981'],
            ['name' => 'Projector Cart',    'type' => 'equipment', 'capacity' => 1,  'colour' => '#F59E0B'],
        ];
        foreach ($resources as $resource) {
            \Modules\Bookings\app\Models\Resource::create([
                ...$resource,
                'is_active' => true,
            ]);
        }

        // Bookings — services
        $services = [
            ['name' => 'Strategy Meeting',    'duration_minutes' => 60,  'buffer_minutes' => 15, 'price' => 0,    'max_participants' => 10],
            ['name' => 'Client Consultation', 'duration_minutes' => 45,  'buffer_minutes' => 15, 'price' => 850,  'max_participants' => 4],
            ['name' => 'Deep Tissue Massage', 'duration_minutes' => 60,  'buffer_minutes' => 10, 'price' => 650,  'max_participants' => 1],
            ['name' => 'Team Workshop',       'duration_minutes' => 180, 'buffer_minutes' => 30, 'price' => 2500, 'max_participants' => 20],
        ];
        foreach ($services as $service) {
            \Modules\Bookings\app\Models\Service::create([
                ...$service,
                'requires_confirmation' => true,
                'is_active'             => true,
            ]);
        }

        $this->command->info('Dev data seeded successfully.');
    }
}
