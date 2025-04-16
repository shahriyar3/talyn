<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;

readonly class CreateOrderAction
{
    public function __construct(
        private CreateBuyOrderAction|MockInterface $createBuyOrderAction,
        private CreateSellOrderAction|MockInterface $createSellOrderAction
    ) {}

    /**
     * @throws Exception
     */
    public function execute(User $user, string $type, float $price, float $amount): Order
    {
        $data = [
            'price' => $price,
            'amount' => $amount,
        ];

        try {
            if ($type === 'buy') {
                return $this->createBuyOrderAction->execute($user, $data);
            } else {
                return $this->createSellOrderAction->execute($user, $data);
            }
        } catch (Exception $e) {
            Log::error('Error creating order', [
                'user_id' => $user->id,
                'type' => $type,
                'price' => $price,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
