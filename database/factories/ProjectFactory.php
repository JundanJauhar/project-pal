<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'project_code' => fake()->unique()->randomElement(['PRJ', 'PROJ']) . '-' . fake()->numberBetween(1000, 9999),
            'project_name' => fake()->sentence(4),
            'description' => fake()->paragraph(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_project' => 'active',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_project' => 'completed',
            'end_date' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_project' => 'pending',
        ]);
    }
}
