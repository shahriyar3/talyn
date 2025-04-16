<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;

it('can access user relationships correctly', function () {
    // Create a user
    $user = User::factory()->create();

    // Create another dummy user for valid foreign keys
    $dummyUser = User::factory()->create([
        'id' => 999,
        'name' => 'Dummy User',
        'email' => 'dummy@example.com',
    ]);

    // Create some orders for the user
    $buyOrder = Order::query()->create([
        'user_id' => $user->id,
        'type' => OrderType::BUY->value,
        'price' => 25000000,
        'amount' => 2,
        'remaining_amount' => 0,
        'status' => OrderStatus::FILLED->value,
    ]);

    $sellOrder = Order::query()->create([
        'user_id' => $user->id,
        'type' => OrderType::SELL->value,
        'price' => 26000000,
        'amount' => 1,
        'remaining_amount' => 0,
        'status' => OrderStatus::FILLED->value,
    ]);

    // Create buyer and seller transactions
    $buyTransaction = Transaction::query()->create([
        'order_id' => $sellOrder->id,
        'buyer_id' => $user->id,
        'seller_id' => 999, // Dummy ID
        'amount' => 1,
        'price' => 26000000,
        'commission' => 520000,
        'commission_rate' => 0.02,
    ]);

    $sellTransaction = Transaction::query()->create([
        'order_id' => $buyOrder->id,
        'buyer_id' => 999, // Dummy ID
        'seller_id' => $user->id,
        'amount' => 2,
        'price' => 25000000,
        'commission' => 750000,
        'commission_rate' => 0.015,
    ]);

    // Test the relationships
    expect($user->orders)->toHaveCount(2);
    expect($user->orders->first()->id)->toEqual($buyOrder->id);
    expect($user->orders->last()->id)->toEqual($sellOrder->id);

    expect($user->buyTransactions)->toHaveCount(1);
    expect($user->buyTransactions->first()->id)->toEqual($buyTransaction->id);

    expect($user->sellTransactions)->toHaveCount(1);
    expect($user->sellTransactions->first()->id)->toEqual($sellTransaction->id);
});
