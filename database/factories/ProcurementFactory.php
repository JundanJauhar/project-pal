<?php

namespace Database\Factories;

use App\Models\Procurement;
use App\Models\Project;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcurementFactory extends Factory
{
    protected $model = Procurement::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'code_procurement' => 'PROC-' . fake()->unique()->numberBetween(1000, 9999),
            'name_procurement' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'department_procurement' => Department::factory(),
            'priority' => fake()->randomElement(['rendah', 'sedang', 'tinggi']),
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+3 months'),
            'status_procurement' => 'in_progress',
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_procurement' => 'in_progress',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_procurement' => 'completed',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_procurement' => 'cancelled',
        ]);
    }
}
