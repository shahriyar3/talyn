<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class CreateSellOrderAction
{
    /**
     * @throws \Exception
     */
    public function execute(User $user, array $data): Order
    {
        try {
            DB::beginTransaction();

            if ($user->gold_balance < $data['amount']) {
                throw new \Exception(__('Insufficient gold balance'));
            }

            $user->gold_balance -= $data['amount'];
            $user->save();

            $order = Order::query()->create([
                'user_id' => $user->id,
                'type' => OrderType::SELL->value,
                'price' => $data['price'],
                'amount' => $data['amount'],
                'remaining_amount' => $data['amount'],
                'status' => OrderStatus::OPEN->value,
            ]);

            app(MatchOrdersAction::class)->matchSellOrder($order);

            DB::commit();

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Create sell order failed: '.$e->getMessage());

            throw new \Exception(__('error on create sell order action: ').$e->getMessage());
        }
    }
}
