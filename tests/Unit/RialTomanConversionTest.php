<?php

test('rial to toman conversion', function () {
    expect(rial_to_toman(10000))->toBe(1000);
    expect(rial_to_toman(0))->toBe(0);
    expect(rial_to_toman(10))->toBe(1);
    expect(rial_to_toman(5))->toBe(0); // تقسیم صحیح 5 بر 10 برابر 0 می‌شود
    expect(rial_to_toman(999999))->toBe(99999);
});

test('toman to rial conversion', function () {
    expect(toman_to_rial(1000))->toBe(10000);
    expect(toman_to_rial(0))->toBe(0);
    expect(toman_to_rial(1))->toBe(10);
    expect(toman_to_rial(99999))->toBe(999990);
});

test('conversion is reversible', function () {
    $originalAmount = 10000; // ریال
    $converted = rial_to_toman($originalAmount);
    $backToOriginal = toman_to_rial($converted);

    expect($backToOriginal)->toBe($originalAmount);

    $originalToman = 1000;
    $convertedToRial = toman_to_rial($originalToman);
    $backToToman = rial_to_toman($convertedToRial);

    expect($backToToman)->toBe($originalToman);
});

test('currency helper class is compatible with helper functions', function () {
    $helper = new \App\Helpers\CurrencyHelper;

    $amountRial = 10000;
    $amountToman = 1000;

    expect($helper->rialToToman($amountRial))->toBe(rial_to_toman($amountRial));
    expect($helper->tomanToRial($amountToman))->toBe(toman_to_rial($amountToman));
    expect($helper->formatRial($amountRial))->toBe(format_rial($amountRial));
    expect($helper->formatToman($amountToman))->toBe(format_toman($amountToman));
});
