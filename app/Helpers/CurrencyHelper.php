<?php

namespace App\Helpers;

class CurrencyHelper
{
    public function rialToToman(int $amount): int
    {
        return (int) ($amount / 10);
    }

    public function tomanToRial(int $amount): int
    {
        return $amount * 10;
    }

    public function formatToman(int|float $amount): string
    {
        return number_format($amount).' تومان';
    }

    public function formatRial(int|float $amount): string
    {
        return number_format($amount).' ریال';
    }
}
