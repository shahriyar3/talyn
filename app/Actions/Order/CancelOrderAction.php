<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class CancelOrderAction
{
    /**
     * @throws Exception
     */
    public function execute(Order $order, User $user): Order
    {
        try {
            return DB::transaction(function () use ($order, $user) {
                if ($order->user_id !== $user->id) {
                    throw new Exception(__('Unauthorized access to order'));
                }

                if (! $order->isActive()) {
                    throw new Exception(__('Order is already filled or cancelled'));
                }

                $remainingAmount = $order->remaining_amount;

                if ($order->isBuyOrder()) {
                    $cashToReturn = $remainingAmount * $order->price;
                    $user->cash_balance += $cashToReturn;
                    $user->save();
                } else {
                    $user->gold_balance += $remainingAmount;
                    $user->save();
                }

                $order->status = OrderStatus::CANCELLED;
                $order->save();

                return $order;
            });
        } catch (Exception $e) {
            Log::error('Error cancelling order', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
