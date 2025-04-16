<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\Transaction\CreateTransactionAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

readonly class MatchOrdersAction
{
    public function matchBuyOrder(Order $buyOrder): void
    {
        $matchingSellOrders = Order::query()->sell()
            ->active()
            ->where('price', '=', $buyOrder->price)
            ->oldest()
            ->get();

        $remainingAmount = $buyOrder->amount;

        foreach ($matchingSellOrders as $sellOrder) {
            if ($sellOrder->user_id === $buyOrder->user_id || $sellOrder->remaining_amount <= 0) {
                continue;
            }

            $tradeAmount = min($remainingAmount, $sellOrder->remaining_amount);

            if ($tradeAmount > 0) {
                $this->createTransaction($buyOrder, $sellOrder, $tradeAmount);

                $this->updateOrderRemainingAmount($sellOrder, $sellOrder->remaining_amount - $tradeAmount);

                $remainingAmount -= $tradeAmount;

                if ($remainingAmount <= 0) {
                    break;
                }
            }
        }

        $this->updateOrderRemainingAmount($buyOrder, $remainingAmount);
    }

    public function matchSellOrder(Order $sellOrder): void
    {
        $matchingBuyOrders = Order::query()->buy()
            ->active()
            ->where('price', '=', $sellOrder->price)
            ->oldest()
            ->get();

        $remainingAmount = $sellOrder->amount;

        foreach ($matchingBuyOrders as $buyOrder) {
            if ($buyOrder->user_id === $sellOrder->user_id) {
                continue;
            }

            if ($buyOrder->remaining_amount <= 0) {
                continue;
            }

            $tradeAmount = min($remainingAmount, $buyOrder->remaining_amount);

            if ($tradeAmount > 0) {
                $this->createTransaction($buyOrder, $sellOrder, $tradeAmount);

                $this->updateOrderRemainingAmount($buyOrder, $buyOrder->remaining_amount - $tradeAmount);

                $remainingAmount -= $tradeAmount;

                if ($remainingAmount <= 0) {
                    break;
                }
            }
        }

        $this->updateOrderRemainingAmount($sellOrder, $remainingAmount);
    }

    private function updateOrderRemainingAmount(Order $order, float $remainingAmount): void
    {
        $status = $order->status;
        if ($remainingAmount <= 0) {
            $status = OrderStatus::FILLED;
        } elseif ($remainingAmount < $order->amount) {
            $status = OrderStatus::PARTIAL;
        }

        $order->update([
            'status' => $status,
            'remaining_amount' => $remainingAmount,
        ]);
    }

    private function createTransaction(Order $buyOrder, Order $sellOrder, float $amount): void
    {
        $buyer = User::query()->find($buyOrder->user_id);
        $seller = User::query()->find($sellOrder->user_id);

        if ($buyer && $seller) {
            app(CreateTransactionAction::class)->execute($buyOrder, $seller, $buyer, $amount);
        }
    }
}
