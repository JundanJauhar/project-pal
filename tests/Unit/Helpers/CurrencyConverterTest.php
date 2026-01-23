<?php

namespace Tests\Unit\Helpers;

use App\Helpers\CurrencyConverter;
use PHPUnit\Framework\TestCase;

class CurrencyConverterTest extends TestCase
{
    /**
     * Tes konversi mata uang dasar
     */
    public function test_mengkonversi_mata_uang_dengan_benar()
    {
        // USD to IDR
        $result = CurrencyConverter::convert(100, 'USD', 'IDR');
        $this->assertEquals(1600000, $result);

        // EUR to IDR
        $result = CurrencyConverter::convert(100, 'EUR', 'IDR');
        $this->assertEquals(1750000, $result);

        // SGD to IDR
        $result = CurrencyConverter::convert(100, 'SGD', 'IDR');
        $this->assertEquals(1200000, $result);
    }

    /**
     * Tes konversi antar mata uang yang sama
     */
    public function test_mata_uang_sama_mengembalikan_jumlah_sama()
    {
        $result = CurrencyConverter::convert(100, 'USD', 'USD');
        $this->assertEquals(100, $result);

        $result = CurrencyConverter::convert(500, 'IDR', 'IDR');
        $this->assertEquals(500, $result);
    }

    /**
     * Tes konversi dari IDR ke mata uang lain
     */
    public function test_mengkonversi_dari_idr_ke_mata_uang_lain()
    {
        // IDR to USD
        $result = CurrencyConverter::convert(16000, 'IDR', 'USD');
        $this->assertEquals(1, $result);

        // IDR to EUR
        $result = CurrencyConverter::convert(17500, 'IDR', 'EUR');
        $this->assertEquals(1, $result);

        // IDR to SGD
        $result = CurrencyConverter::convert(12000, 'IDR', 'SGD');
        $this->assertEquals(1, $result);
    }

    /**
     * Tes konversi antar mata uang non-IDR
     */
    public function test_mengkonversi_antar_mata_uang_non_idr()
    {
        // USD to EUR: 100 USD = 1,600,000 IDR → 1,600,000 / 17,500 = 91.43 EUR
        $result = CurrencyConverter::convert(100, 'USD', 'EUR');
        $this->assertEqualsWithDelta(91.43, $result, 0.01);

        // EUR to USD: 100 EUR = 1,750,000 IDR → 1,750,000 / 16,000 = 109.375 USD
        $result = CurrencyConverter::convert(100, 'EUR', 'USD');
        $this->assertEquals(109.375, $result);

        // USD to SGD: 100 USD = 1,600,000 IDR → 1,600,000 / 12,000 = 133.33 SGD
        $result = CurrencyConverter::convert(100, 'USD', 'SGD');
        $this->assertEqualsWithDelta(133.33, $result, 0.01);
    }

    /**
     * Tes mengembalikan null untuk input tidak valid
     */
    public function test_mengembalikan_null_untuk_input_tidak_valid()
    {
        // Null amount
        $result = CurrencyConverter::convert(null, 'USD', 'IDR');
        $this->assertNull($result);

        // Null from currency
        $result = CurrencyConverter::convert(100, null, 'IDR');
        $this->assertNull($result);

        // Null to currency
        $result = CurrencyConverter::convert(100, 'USD', null);
        $this->assertNull($result);

        // Zero amount
        $result = CurrencyConverter::convert(0, 'USD', 'IDR');
        $this->assertNull($result);

        // Empty string amount
        $result = CurrencyConverter::convert('', 'USD', 'IDR');
        $this->assertNull($result);
    }

    /**
     * Tes mengembalikan null untuk mata uang yang tidak didukung
     */
    public function test_mengembalikan_null_untuk_mata_uang_tidak_didukung()
    {
        // Unsupported from currency
        $result = CurrencyConverter::convert(100, 'JPY', 'IDR');
        $this->assertNull($result);

        // Unsupported to currency
        $result = CurrencyConverter::convert(100, 'USD', 'GBP');
        $this->assertNull($result);

        // Both unsupported
        $result = CurrencyConverter::convert(100, 'JPY', 'GBP');
        $this->assertNull($result);
    }

    /**
     * Tes menangani jumlah desimal
     */
    public function test_menangani_jumlah_desimal()
    {
        // 50.5 USD to IDR
        $result = CurrencyConverter::convert(50.5, 'USD', 'IDR');
        $this->assertEquals(808000, $result);

        // 0.5 USD to IDR
        $result = CurrencyConverter::convert(0.5, 'USD', 'IDR');
        $this->assertEquals(8000, $result);
    }

    /**
     * Tes menangani jumlah besar
     */
    public function test_menangani_jumlah_besar()
    {
        // 1 million USD to IDR
        $result = CurrencyConverter::convert(1000000, 'USD', 'IDR');
        $this->assertEquals(16000000000, $result);
    }

    /**
     * Tes menangani jumlah negatif (kasus edge)
     */
    public function test_menangani_jumlah_negatif()
    {
        // Negative conversion should work mathematically
        $result = CurrencyConverter::convert(-100, 'USD', 'IDR');
        $this->assertEquals(-1600000, $result);
    }
}
