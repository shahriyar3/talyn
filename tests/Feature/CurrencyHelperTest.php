<?php

it('converts rial to toman correctly', function () {
    expect(rial_to_toman(10000))->toBe(1000);
    expect(rial_to_toman(25000))->toBe(2500);
    expect(rial_to_toman(0))->toBe(0);
});

it('converts toman to rial correctly', function () {
    expect(toman_to_rial(1000))->toBe(10000);
    expect(toman_to_rial(2500))->toBe(25000);
    expect(toman_to_rial(0))->toBe(0);
});

it('formats toman values correctly', function () {
    expect(format_toman(1000))->toBe('1,000 تومان');
    expect(format_toman(25000))->toBe('25,000 تومان');
    expect(format_toman(1000000))->toBe('1,000,000 تومان');
    expect(format_toman(0))->toBe('0 تومان');
});

it('formats rial values correctly', function () {
    expect(format_rial(10000))->toBe('10,000 ریال');
    expect(format_rial(25000))->toBe('25,000 ریال');
    expect(format_rial(10000000))->toBe('10,000,000 ریال');
    expect(format_rial(0))->toBe('0 ریال');
});
