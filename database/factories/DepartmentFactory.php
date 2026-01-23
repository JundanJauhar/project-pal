<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $departments = [
            'Supply Chain',
            'Accounting',
            'Treasury',
            'Quality Assurance',
            'Engineering',
            'Design',
            'Operations',
            'IT',
        ];

        return [
            'department_name' => fake()->unique()->randomElement($departments),
            'description' => fake()->sentence(),
        ];
    }
}
