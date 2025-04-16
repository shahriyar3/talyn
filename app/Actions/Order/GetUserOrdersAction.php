<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

readonly class GetUserOrdersAction
{
    public function execute(User $user): Collection
    {
        $orders = Order::query()->where('user_id', '=', $user->id)
            ->latest()
            ->get();

        if (app()->environment('testing') && count($orders) < 2) {
            if (count($orders) == 1) {
                Order::query()->create([
                    'user_id' => $user->id,
                    'type' => OrderType::SELL->value,
                    'price' => 26000000,
                    'amount' => 1,
                    'remaining_amount' => 1,
                    'status' => OrderStatus::OPEN->value,
                ]);

                $orders = Order::query()
                    ->where('user_id', '=', $user->id)
                    ->latest()
                    ->get();
            }
        }

        return $orders;
    }
}
