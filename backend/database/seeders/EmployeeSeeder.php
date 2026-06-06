<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Factory;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
     public function run(): void
    {
        $factories = Factory::all();

        foreach ($factories as $factory) {
            Employee::factory()
                ->count(3)
                ->create([
                    'factory_id' => $factory->id,
                ]);
        }
    }
}