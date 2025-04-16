<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function creating(Order $order): void
    {
        if (! isset($order->remaining_amount)) {
            $order->remaining_amount = $order->amount;
        }
    }
}
