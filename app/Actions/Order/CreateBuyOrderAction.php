<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateBuyOrderAction
{
    /**
     * @throws \Exception
     */
    public function execute(User $user, array $data): Order
    {
        try {
            DB::beginTransaction();

            $totalCost = $data['price'] * $data['amount'];

            if ($user->cash_balance < $totalCost) {
                throw new \Exception(__('Insufficient cash balance'));
            }

            $user->cash_balance -= $totalCost;
            $user->save();

            $order = Order::query()->create([
                'user_id' => $user->id,
                'type' => OrderType::BUY->value,
                'price' => $data['price'],
                'amount' => $data['amount'],
                'remaining_amount' => $data['amount'],
                'status' => OrderStatus::OPEN->value,
            ]);

            app(MatchOrdersAction::class)->matchBuyOrder($order);

            DB::commit();

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Create buy order failed: '.$e->getMessage());

            throw new \Exception(__('error on create buy order action: ').$e->getMessage());
        }
    }
}
