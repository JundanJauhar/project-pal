<?php

namespace Database\Factories;

use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition(): array
    {
        $divisions = [
            'Operations',
            'Finance',
            'Engineering',
            'Sales',
            'Marketing',
            'Human Resources',
            'IT',
        ];

        return [
            'division_name' => fake()->unique()->randomElement($divisions),
            'description' => fake()->sentence(),
        ];
    }
}
