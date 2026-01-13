<?php

namespace Database\Factories;

use App\Models\RequestProcurement;
use App\Models\Procurement;
use App\Models\Project;
use App\Models\Vendor;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestProcurementFactory extends Factory
{
    protected $model = RequestProcurement::class;

    public function definition(): array
    {
        return [
            'procurement_id' => Procurement::factory(),
            'project_id' => Project::factory(),
            'vendor_id' => Vendor::factory(),
            'request_name' => fake()->sentence(3),
            'created_date' => fake()->date(),
            'deadline_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'request_status' => 'submitted',
            'department_id' => Department::factory(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_status' => 'draft',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_status' => 'approved',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_status' => 'rejected',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_status' => 'completed',
        ]);
    }
}
