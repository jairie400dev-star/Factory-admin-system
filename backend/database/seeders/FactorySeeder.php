<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Factory;

class FactorySeeder extends Seeder
{
    public function run(): void
    {
         Factory::factory()
            ->count(5)
            ->create();
        
    }
}