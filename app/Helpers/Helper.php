<?php

if (! function_exists('rial_to_toman')) {
    function rial_to_toman(int $amount): int
    {
        return (int) ($amount / 10);
    }
}

if (! function_exists('toman_to_rial')) {
    function toman_to_rial(int $amount): int
    {
        return $amount * 10;
    }
}

if (! function_exists('format_toman')) {
    function format_toman(int|float $amount): string
    {
        return number_format($amount).' تومان';
    }
}

if (! function_exists('format_rial')) {
    function format_rial(float|int $amount): string
    {
        return number_format($amount).' ریال';
    }
}
