<?php

use App\Enums\CommissionRates;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;

it('creates transaction when orders match', function () {
    // Create buyer with sufficient cash
    $buyer = User::factory()->create([
        'cash_balance' => 1000000000,
        'gold_balance' => 5,
    ]);

    // Create seller with sufficient gold
    $seller = User::factory()->create([
        'cash_balance' => 500000000,
        'gold_balance' => 10,
    ]);

    // First create a sell order
    $sellOrder = Order::query()->create([
        'user_id' => $seller->id,
        'type' => OrderType::SELL->value,
        'price' => 25000000,
        'amount' => 2,
        'remaining_amount' => 2,
        'status' => OrderStatus::OPEN->value,
    ]);

    // Update seller's gold balance to simulate deduction
    $seller->gold_balance -= 2;
    $seller->save();

    // Create a matching buy order - this should trigger a transaction
    $this->actingAs($buyer);

    $response = $this->postJson('/api/orders', [
        'type' => OrderType::BUY->value,
        'price' => 25000000,
        'amount' => 2,
    ]);

    $response->assertStatus(201);

    // Verify transaction was created
    expect(Transaction::count())->toEqual(1);

    $this->assertDatabaseHas('transactions', [
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'amount' => 2,
        'price' => 25000000,
    ]);

    // Verify order statuses were updated
    $this->assertDatabaseHas('orders', [
        'id' => $sellOrder->id,
        'status' => OrderStatus::FILLED->value,
    ]);

    $buyOrder = Order::query()->where('user_id', $buyer->id)->first();
    expect($buyOrder->status)->toEqual(OrderStatus::FILLED);

    // Verify buyer's and seller's balances were updated
    $buyer->refresh();
    $seller->refresh();

    // Buyer should have +2 gold and -50,000,000 cash
    expect($buyer->gold_balance)->toEqual(7);
    expect($buyer->cash_balance)->toEqual(950000000);

    // Seller should have -2 gold and +50,000,000 cash
    expect($seller->gold_balance)->toEqual(8);
    expect($seller->cash_balance)->toEqual(550000000);
});

it('creates transaction and updates order status for partial order matching', function () {
    // Create buyer with sufficient cash
    $buyer = User::factory()->create([
        'cash_balance' => 1000000000,
        'gold_balance' => 5,
    ]);

    // Create seller with sufficient gold
    $seller = User::factory()->create([
        'cash_balance' => 500000000,
        'gold_balance' => 10,
    ]);

    // First create a sell order for 3 grams
    $sellOrder = Order::query()->create([
        'user_id' => $seller->id,
        'type' => OrderType::SELL->value,
        'price' => 25000000,
        'amount' => 3,
        'remaining_amount' => 3,
        'status' => OrderStatus::OPEN->value,
    ]);

    // Update seller's gold balance to simulate deduction
    $seller->gold_balance -= 3;
    $seller->save();

    // Create a matching buy order for 2 grams (partial match)
    $this->actingAs($buyer);

    $response = $this->postJson('/api/orders', [
        'type' => OrderType::BUY->value,
        'price' => 25000000,
        'amount' => 2, // Less than sell order amount
    ]);

    $response->assertStatus(201);

    // Verify transaction was created
    expect(Transaction::count())->toEqual(1);

    $this->assertDatabaseHas('transactions', [
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'amount' => 2,
        'price' => 25000000,
    ]);

    // Verify sell order status was updated to PARTIAL
    $sellOrder->refresh();
    expect($sellOrder->status)->toEqual(OrderStatus::PARTIAL);
    expect($sellOrder->remaining_amount)->toEqual(1); // 3 original - 2 sold = 1 remaining

    // Verify buy order status was updated to FILLED
    $buyOrder = Order::query()->where('user_id', $buyer->id)->first();
    expect($buyOrder->status)->toEqual(OrderStatus::FILLED);
    expect($buyOrder->remaining_amount)->toEqual(0);

    // Verify buyer's and seller's balances were updated
    $buyer->refresh();
    $seller->refresh();

    // Buyer should have +2 gold and -50,000,000 cash
    expect($buyer->gold_balance)->toEqual(7);
    expect($buyer->cash_balance)->toEqual(950000000);

    // Seller should have -2 gold (out of 3 original) and +50,000,000 cash
    expect($seller->gold_balance)->toEqual(7);
    expect($seller->cash_balance)->toEqual(550000000);
});

it('calculates commission according to tiered structure', function () {
    // Test tier 1: Amount <= 1g
    $tier1Commission = Transaction::calculateCommission(1, 25000000);
    expect($tier1Commission['rate'])->toEqual(CommissionRates::TIER1->value());
    expect($tier1Commission['commission'])->toEqual(25000000 * CommissionRates::TIER1->value());

    // Test tier 2: 1g < Amount <= 10g
    $tier2Commission = Transaction::calculateCommission(5, 25000000);
    expect($tier2Commission['rate'])->toEqual(CommissionRates::TIER2->value());
    expect($tier2Commission['commission'])->toEqual(5 * 25000000 * CommissionRates::TIER2->value());

    // Test tier 3: Amount > 10g
    $tier3Commission = Transaction::calculateCommission(15, 25000000);
    expect($tier3Commission['rate'])->toEqual(CommissionRates::TIER3->value());
    expect($tier3Commission['commission'])->toEqual(15 * 25000000 * CommissionRates::TIER3->value());

    // Test minimum commission
    $minCommission = Transaction::calculateCommission(0.1, 25000000);
    expect($minCommission['commission'])->toEqual(CommissionRates::MIN_COMMISSION->value());

    // Test maximum commission
    $maxCommission = Transaction::calculateCommission(100, 25000000);
    expect($maxCommission['commission'])->toEqual(CommissionRates::MAX_COMMISSION->value());
});

it('retrieves user transaction history', function () {
    $user1 = User::factory()->create([
        'cash_balance' => 1000000000,
        'gold_balance' => 10,
    ]);

    $user2 = User::factory()->create([
        'cash_balance' => 500000000,
        'gold_balance' => 20,
    ]);

    $sellOrder = Order::query()->create([
        'user_id' => $user2->id,
        'type' => OrderType::SELL->value,
        'price' => 25000000,
        'amount' => 5,
        'remaining_amount' => 5,
        'status' => OrderStatus::OPEN->value,
    ]);

    $user2->gold_balance -= 5;
    $user2->save();

    $this->actingAs($user1);
    $response = $this->postJson('/api/orders', [
        'type' => OrderType::BUY->value,
        'price' => 25000000,
        'amount' => 5,
    ]);

    $response->assertStatus(201);

    expect(Transaction::query()->count())->toEqual(1);

    $response = $this->getJson('/api/transactions');

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => [
                'id',
                'order_id',
                'buyer_id',
                'seller_id',
                'amount',
                'price',
                'commission',
                'commission_rate',
                'created_at',
                'updated_at',
            ],
        ]);

    $response->assertJsonCount(1)
        ->assertJsonPath('0.buyer_id', $user1->id)
        ->assertJsonPath('0.seller_id', $user2->id)
        ->assertJsonPath('0.amount', 5)
        ->assertJsonPath('0.price', 25000000);

    $this->actingAs($user2);
    $response = $this->getJson('/api/transactions');

    $response->assertStatus(200)
        ->assertJsonCount(1);
});

it('retrieves transactions using service methods', function () {
    $buyer = User::factory()->create([
        'cash_balance' => 1000000000,
        'gold_balance' => 10,
    ]);

    $seller = User::factory()->create([
        'cash_balance' => 500000000,
        'gold_balance' => 20,
    ]);

    $order = Order::query()->create([
        'user_id' => $seller->id,
        'type' => OrderType::SELL->value,
        'price' => 25000000,
        'amount' => 5,
        'remaining_amount' => 0,
        'status' => OrderStatus::FILLED->value,
    ]);

    $transaction = Transaction::query()->create([
        'order_id' => $order->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'amount' => 5,
        'price' => 25000000,
        'commission' => 1250000,
        'commission_rate' => 0.01,
    ]);

    $transactionService = app(TransactionService::class);

    $buyerTransactions = $transactionService->getUserTransactions($buyer);
    expect($buyerTransactions)->toHaveCount(1);
    expect($buyerTransactions->first()->id)->toEqual($transaction->id);
    expect($buyerTransactions->first()->buyer_id)->toEqual($buyer->id);

    $sellerTransactions = $transactionService->getUserTransactions($seller);
    expect($sellerTransactions)->toHaveCount(1);
    expect($sellerTransactions->first()->id)->toEqual($transaction->id);
    expect($sellerTransactions->first()->seller_id)->toEqual($seller->id);
});
