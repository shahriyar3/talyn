<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Traits\OrderScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory, OrderScopes;

    protected $fillable = [
        'user_id',
        'type',
        'price',
        'amount',
        'remaining_amount',
        'status',
    ];

    protected $casts = [
        'price' => 'integer',
        'amount' => 'float',
        'remaining_amount' => 'float',
        'type' => OrderType::class,
        'status' => OrderStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id', 'user');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'order_id', 'id');
    }

    public function isBuyOrder(): bool
    {
        return $this->type === OrderType::BUY;
    }

    public function isSellOrder(): bool
    {
        return $this->type === OrderType::SELL;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [OrderStatus::OPEN, OrderStatus::PARTIAL]);
    }
}
