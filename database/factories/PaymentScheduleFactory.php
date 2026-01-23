<?php

namespace Database\Factories;

use App\Models\PaymentSchedule;
use App\Models\Procurement;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentScheduleFactory extends Factory
{
    protected $model = PaymentSchedule::class;

    public function definition()
    {
        return [
            'project_id' => null,
            'contract_id' => null,
            'payment_type' => $this->faker->randomElement(['dp', 'termin_1', 'termin_2', 'termin_3', 'final']),
            'amount' => $this->faker->numberBetween(1000000, 100000000),
            'percentage' => $this->faker->randomFloat(2, 10, 50),
            'due_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'status' => 'pending',
            'payment_date' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Payment yang sudah diverifikasi accounting
     */
    public function verifiedAccounting()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'verified_accounting',
                'verified_by_accounting' => 1,
                'verified_at_accounting' => now(),
            ];
        });
    }

    /**
     * Payment yang sudah diverifikasi treasury
     */
    public function verifiedTreasury()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'verified_treasury',
                'verified_by_accounting' => 1,
                'verified_at_accounting' => now()->subDays(2),
                'verified_by_treasury' => 1,
                'verified_at_treasury' => now(),
            ];
        });
    }

    /**
     * Payment yang sudah dibayar
     */
    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
                'verified_by_accounting' => 1,
                'verified_at_accounting' => now()->subDays(3),
                'verified_by_treasury' => 1,
                'verified_at_treasury' => now()->subDays(2),
                'payment_date' => now()->subDay(),
            ];
        });
    }

    /**
     * DP Payment (Down Payment)
     */
    public function dp()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_type' => 'dp',
                'amount' => 30000000, // 30% typical
            ];
        });
    }

    /**
     * Final Payment
     */
    public function finalPayment()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_type' => 'final',
            ];
        });
    }
}
