<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        $vendorName = fake()->company();
        
        return [
            'name_vendor' => $vendorName,
            'address' => fake()->address(),
            'phone_number' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'user_vendor' => fake()->unique()->userName(),
            'password' => bcrypt('password'),
            'is_importer' => fake()->boolean(30), // 30% chance true
        ];
    }

    public function importer(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_importer' => true,
        ]);
    }

    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_importer' => false,
        ]);
    }
}
