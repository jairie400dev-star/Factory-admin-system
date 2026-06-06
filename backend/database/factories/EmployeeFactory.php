<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Factory as FactoryModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
           'firstname' => $this->faker->firstName(),
            'lastname'  => $this->faker->lastName(),
            'factory_id' => FactoryModel::factory(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}