<?php

namespace Database\Factories;

use App\Models\Checkpoint;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckpointFactory extends Factory
{
    protected $model = Checkpoint::class;

    public function definition()
    {
        return [
            'point_name' => $this->faker->randomElement([
                'Request Procurement',
                'Inquiry Quotation',
                'Evaluasi Quotation',
                'Negosiasi',
                'Pembuatan OC',
                'Pengesahan Kontrak',
                'Material Delivery',
                'Inspection Report',
                'Invoice Verification',
                'Payment Authorization',
                'Payment Complete',
            ]),
            'point_sequence' => $this->faker->unique()->numberBetween(1, 11),
            'responsible_division' => null,
            'is_final' => false,
        ];
    }

    /**
     * Checkpoint 1: Request Procurement
     */
    public function requestProcurement()
    {
        return $this->state(function (array $attributes) {
            return [
                'point_name' => 'Request Procurement',
                'point_sequence' => 1,
            ];
        });
    }

    /**
     * Checkpoint 2: Inquiry Quotation
     */
    public function inquiryQuotation()
    {
        return $this->state(function (array $attributes) {
            return [
                'point_name' => 'Inquiry Quotation',
                'point_sequence' => 2,
            ];
        });
    }

    /**
     * Checkpoint 11: Payment Complete
     */
    public function paymentComplete()
    {
        return $this->state(function (array $attributes) {
            return [
                'point_name' => 'Payment Complete',
                'point_sequence' => 11,
                'is_final' => true,
            ];
        });
    }
}
