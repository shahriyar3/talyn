<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CommissionRates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'buyer_id',
        'seller_id',
        'amount',
        'price',
        'commission',
        'commission_rate',
    ];

    protected $casts = [
        'amount' => 'float',
        'price' => 'integer',
        'commission' => 'integer',
        'commission_rate' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id', 'order');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id', 'buyer');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id', 'id', 'seller');
    }

    public static function calculateCommission(float $amount, int $price): array
    {
        $totalValue = $amount * $price;

        if ($amount <= 1) {
            $rate = CommissionRates::TIER1->value();
        } elseif ($amount <= 10) {
            $rate = CommissionRates::TIER2->value();
        } else {
            $rate = CommissionRates::TIER3->value();
        }

        $commission = (int) round($totalValue * $rate);

        $commission = max($commission, CommissionRates::MIN_COMMISSION->value());
        $commission = min($commission, CommissionRates::MAX_COMMISSION->value());

        return [
            'commission' => $commission,
            'rate' => $rate,
        ];
    }
}
