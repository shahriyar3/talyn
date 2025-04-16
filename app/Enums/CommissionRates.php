<?php

declare(strict_types=1);

namespace App\Enums;

enum CommissionRates: string
{
    case TIER1 = 'tier1';
    case TIER2 = 'tier2';
    case TIER3 = 'tier3';
    case MIN_COMMISSION = 'min_commission';
    case MAX_COMMISSION = 'max_commission';

    public function value(): float|int
    {
        return match ($this) {
            self::TIER1 => 0.02,
            self::TIER2 => 0.015,
            self::TIER3 => 0.01,
            self::MIN_COMMISSION => 50000,
            self::MAX_COMMISSION => 5000000,
        };
    }
}
