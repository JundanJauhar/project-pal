<?php

namespace Tests\Unit\Helpers;

use App\Helpers\CurrencyConverter;
use PHPUnit\Framework\TestCase;

class CurrencyConverterTest extends TestCase
{
    /**
     * Test basic currency conversion
     */
    public function test_converts_currency_correctly()
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
     * Test conversion between same currency
     */
    public function test_same_currency_returns_same_amount()
    {
        $result = CurrencyConverter::convert(100, 'USD', 'USD');
        $this->assertEquals(100, $result);

        $result = CurrencyConverter::convert(500, 'IDR', 'IDR');
        $this->assertEquals(500, $result);
    }

    /**
     * Test conversion from IDR to other currencies
     */
    public function test_converts_from_idr_to_other_currencies()
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
     * Test conversion between non-IDR currencies
     */
    public function test_converts_between_non_idr_currencies()
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
     * Test null or invalid inputs
     */
    public function test_returns_null_for_invalid_inputs()
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
     * Test unsupported currency codes
     */
    public function test_returns_null_for_unsupported_currencies()
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
     * Test decimal amounts
     */
    public function test_handles_decimal_amounts()
    {
        // 50.5 USD to IDR
        $result = CurrencyConverter::convert(50.5, 'USD', 'IDR');
        $this->assertEquals(808000, $result);

        // 0.5 USD to IDR
        $result = CurrencyConverter::convert(0.5, 'USD', 'IDR');
        $this->assertEquals(8000, $result);
    }

    /**
     * Test large amounts
     */
    public function test_handles_large_amounts()
    {
        // 1 million USD to IDR
        $result = CurrencyConverter::convert(1000000, 'USD', 'IDR');
        $this->assertEquals(16000000000, $result);
    }

    /**
     * Test negative amounts (edge case)
     */
    public function test_handles_negative_amounts()
    {
        // Negative conversion should work mathematically
        $result = CurrencyConverter::convert(-100, 'USD', 'IDR');
        $this->assertEquals(-1600000, $result);
    }
}
