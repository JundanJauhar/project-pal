<?php

namespace Tests\Unit\Models;

use App\Models\Negotiation;
use App\Models\Procurement;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NegotiationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test deviasi HPS calculation (harga lebih rendah dari HPS = positif)
     */
    public function test_calculates_deviasi_hps_correctly()
    {
        $procurement = Procurement::factory()->create();
        $vendor = Vendor::factory()->create();

        $negotiation = Negotiation::create([
            'procurement_id' => $procurement->procurement_id,
            'vendor_id' => $vendor->id_vendor,
            'hps' => 1000000,
            'currency_hps' => 'IDR',
            'harga_final' => 800000,
            'currency_harga_final' => 'IDR',
        ]);

        // Deviasi = HPS - Harga Final = 1000000 - 800000 = 200000
        $this->assertEquals(200000, $negotiation->deviasi_hps);
    }

    /**
     * Test deviasi HPS calculation (harga lebih tinggi dari HPS = negatif)
     */
    public function test_calculates_negative_deviasi_hps()
    {
        $procurement = Procurement::factory()->create();
        $vendor = Vendor::factory()->create();

        $negotiation = Negotiation::create([
            'procurement_id' => $procurement->procurement_id,
            'vendor_id' => $vendor->id_vendor,
            'hps' => 1000000,
            'currency_hps' => 'IDR',
            'harga_final' => 1200000,
            'currency_harga_final' => 'IDR',
        ]);

        // Deviasi = HPS - Harga Final = 1000000 - 1200000 = -200000
        $this->assertEquals(-200000, $negotiation->deviasi_hps);
    }

    /**
     * Test deviasi budget calculation
     */
    public function test_calculates_deviasi_budget_correctly()
    {
        $procurement = Procurement::factory()->create();
        $vendor = Vendor::factory()->create();

        $negotiation = Negotiation::create([
            'procurement_id' => $procurement->procurement_id,
            'vendor_id' => $vendor->id_vendor,
            'budget' => 1500000,
            'currency_budget' => 'IDR',
            'currency_hps' => 'IDR',
            'harga_final' => 1200000,
            'currency_harga_final' => 'IDR',
        ]);

        // Deviasi = Budget - Harga Final = 1500000 - 1200000 = 300000
        $this->assertEquals(300000, $negotiation->deviasi_budget);
    }

    /**
     * Test deviasi with currency conversion
     */
    public function test_calculates_deviasi_with_different_currencies()
    {
        $procurement = Procurement::factory()->create();
        $vendor = Vendor::factory()->create();

        $negotiation = Negotiation::create([
            'procurement_id' => $procurement->procurement_id,
            'vendor_id' => $vendor->id_vendor,
            'hps' => 100,
            'currency_hps' => 'USD',
            'harga_final' => 1600000,
            'currency_harga_final' => 'IDR',
        ]);

        // HPS: 100 USD = 1,600,000 IDR
        // Final: 1,600,000 IDR
        // Deviasi = 0% (same value)
        $this->assertEquals(0.0, $negotiation->deviasi_hps);
    }

    /**
     * Test deviasi returns null when HPS is null
     */
    public function test_deviasi_hps_returns_null_when_hps_is_null()
    {
        $procurement = Procurement::factory()->create();
        $vendor = Vendor::factory()->create();

        $negotiation = Negotiation::create([
            'procurement_id' => $procurement->procurement_id,
            'vendor_id' => $vendor->id_vendor,
            'hps' => null,
            'currency_hps' => 'IDR',
            'harga_final' => 1000000,
            'currency_harga_final' => 'IDR',
        ]);

        $this->assertNull($negotiation->deviasi_hps);
    }

    /**
     * Test deviasi returns null when harga_final is null
     */
    public function test_deviasi_hps_returns_null_when_harga_final_is_null()
    {
        $procurement = Procurement::factory()->create();
        $vendor = Vendor::factory()->create();

        $negotiation = Negotiation::create([
            'procurement_id' => $procurement->procurement_id,
            'vendor_id' => $vendor->id_vendor,
            'hps' => 1000000,
            'currency_hps' => 'IDR',
            'harga_final' => null,
            'currency_harga_final' => 'IDR',
        ]);

        $this->assertNull($negotiation->deviasi_hps);
    }

    /**
     * Test deviasi budget returns null when budget is null
     */
    public function test_deviasi_budget_returns_null_when_budget_is_null()
    {
        $procurement = Procurement::factory()->create();
        $vendor = Vendor::factory()->create();

        $negotiation = Negotiation::create([
            'procurement_id' => $procurement->procurement_id,
            'vendor_id' => $vendor->id_vendor,
            'budget' => null,
            'currency_budget' => 'IDR',
            'harga_final' => 1000000,
            'currency_harga_final' => 'IDR',
        ]);

        $this->assertNull($negotiation->deviasi_budget);
    }

    /**
     * Test lead time calculation
     */
    public function test_calculates_lead_time_correctly()
    {
        $procurement = Procurement::factory()->create();
        $vendor = Vendor::factory()->create();

        $negotiation = Negotiation::create([
            'procurement_id' => $procurement->procurement_id,
            'vendor_id' => $vendor->id_vendor,
            'tanggal_kirim' => '2026-01-01',
            'tanggal_terima' => '2026-01-15',
            'lead_time' => '14 hari',
            'hps' => 1000000,
            'currency_hps' => 'IDR',
        ]);

        // Lead time is stored as string
        $this->assertEquals('14 hari', $negotiation->lead_time);
    }

    /**
     * Test procurement relationship
     */
    public function test_has_procurement_relationship()
    {
        $procurement = Procurement::factory()->create();
        $vendor = Vendor::factory()->create();

        $negotiation = Negotiation::create([
            'procurement_id' => $procurement->procurement_id,
            'vendor_id' => $vendor->id_vendor,
            'hps' => 1000000,
            'currency_hps' => 'IDR',
        ]);

        $this->assertInstanceOf(Procurement::class, $negotiation->procurement);
        $this->assertEquals($procurement->procurement_id, $negotiation->procurement->procurement_id);
    }

    /**
     * Test vendor relationship
     */
    public function test_has_vendor_relationship()
    {
        $procurement = Procurement::factory()->create();
        $vendor = Vendor::factory()->create();

        $negotiation = Negotiation::create([
            'procurement_id' => $procurement->procurement_id,
            'vendor_id' => $vendor->id_vendor,
            'hps' => 1000000,
            'currency_hps' => 'IDR',
        ]);

        $this->assertInstanceOf(Vendor::class, $negotiation->vendor);
        $this->assertEquals($vendor->id_vendor, $negotiation->vendor->id_vendor);
    }

    /**
     * Test zero HPS handling
     */
    public function test_handles_zero_hps_gracefully()
    {
        $procurement = Procurement::factory()->create();
        $vendor = Vendor::factory()->create();

        $negotiation = Negotiation::create([
            'procurement_id' => $procurement->procurement_id,
            'vendor_id' => $vendor->id_vendor,
            'hps' => 0,
            'currency_hps' => 'IDR',
            'harga_final' => 1000000,
            'currency_harga_final' => 'IDR',
        ]);

        // Should not cause division by zero error
        $deviasi = $negotiation->deviasi_hps;
        $this->assertTrue($deviasi === null || is_numeric($deviasi));
    }

    /**
     * Test appended attributes are included
     */
    public function test_appended_attributes_are_included_in_array()
    {
        $procurement = Procurement::factory()->create();
        $vendor = Vendor::factory()->create();

        $negotiation = Negotiation::create([
            'procurement_id' => $procurement->procurement_id,
            'vendor_id' => $vendor->id_vendor,
            'hps' => 1000000,
            'currency_hps' => 'IDR',
            'budget' => 1500000,
            'currency_budget' => 'IDR',
            'harga_final' => 1200000,
            'currency_harga_final' => 'IDR',
        ]);

        $array = $negotiation->toArray();

        $this->assertArrayHasKey('deviasi_hps', $array);
        $this->assertArrayHasKey('deviasi_budget', $array);
    }
}
