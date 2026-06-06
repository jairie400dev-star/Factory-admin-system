<?php

namespace Database\Factories;

use App\Models\Factory as FactoryModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class FactoryFactory extends Factory
{
    protected $model = FactoryModel::class;

    public function definition(): array
    {
        return [
            'factory_name' => $this->faker->company() . ' Factory',
            'location'     => $this->faker->city(),
            'email'        => $this->faker->unique()->companyEmail(),
            'website'      => $this->faker->url(),
        ];
    }
}