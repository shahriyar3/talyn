<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Order\CancelOrderAction;
use App\Actions\Order\CreateBuyOrderAction;
use App\Actions\Order\CreateSellOrderAction;
use App\Actions\Order\GetUserOrdersAction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class OrderService
{
    public function getUserOrders(User $user): Collection
    {
        return app(GetUserOrdersAction::class)->execute($user);
    }

    public function createBuyOrder(User $user, array $data): Order
    {
        return app(CreateBuyOrderAction::class)->execute($user, $data);
    }

    public function createSellOrder(User $user, array $data): Order
    {
        return app(CreateSellOrderAction::class)->execute($user, $data);
    }

    public function cancelOrder(Order $order, User $user): Order
    {
        return app(CancelOrderAction::class)->execute($order, $user);
    }
}
