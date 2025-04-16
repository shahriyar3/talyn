<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\OrderStatus;
use App\Enums\OrderType;

trait OrderScopes
{
    public function scopeBuy($query)
    {
        return $query->where('type', OrderType::BUY->value);
    }

    public function scopeSell($query)
    {
        return $query->where('type', OrderType::SELL->value);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [OrderStatus::OPEN->value, OrderStatus::PARTIAL->value]);
    }
}
