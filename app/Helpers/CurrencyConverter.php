<?php
namespace App\Helpers;

class CurrencyConverter
{
    protected static array $rates = [
        'IDR' => 1,
        'USD' => 16000,
        'EUR' => 17500,
        'SGD' => 12000,
    ];

    public static function convert($amount, $from, $to)
    {
        if (!$amount || !$from || !$to) return null;
        if ($from === $to) return $amount;

        if (!isset(self::$rates[$from], self::$rates[$to])) {
            return null;
        }

        // konversi ke IDR → ke target
        $inIdr = $amount * self::$rates[$from];
        return $inIdr / self::$rates[$to];
    }
}
