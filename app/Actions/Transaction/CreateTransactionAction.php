<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class CreateTransactionAction
{
    public function execute(Order $order, User $seller, User $buyer, float $amount): Transaction
    {
        try {
            DB::beginTransaction();

            $totalCost = $amount * $order->price;

            $commissionData = Transaction::calculateCommission($amount, $order->price);

            $transaction = Transaction::query()->create([
                'order_id' => $order->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'amount' => $amount,
                'price' => $order->price,
                'commission' => $commissionData['commission'],
                'commission_rate' => $commissionData['rate'],
            ]);

            $buyer->gold_balance += $amount;
            $buyer->save();

            $seller->cash_balance += $totalCost;
            $seller->save();

            DB::commit();

            return $transaction;

        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error('Create transaction failed: '.$exception->getMessage());

            throw new \Exception(__('error on create transaction action: ').$exception->getMessage());
        }
    }
}
