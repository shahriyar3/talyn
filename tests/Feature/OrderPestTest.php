<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\User;

it('can create buy order', function () {
    $user = User::factory()->create([
        'cash_balance' => 1000000000,
        'gold_balance' => 10,
    ]);

    $this->actingAs($user);

    $response = $this->postJson('/api/orders', [
        'type' => OrderType::BUY->value,
        'price' => 25000000,
        'amount' => 2,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'user_id',
            'type',
            'price',
            'amount',
            'remaining_amount',
            'status',
            'created_at',
            'updated_at',
        ]);

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'type' => OrderType::BUY->value,
        'price' => 25000000,
        'amount' => 2,
        'status' => OrderStatus::OPEN->value,
    ]);

    $user->refresh();
    expect($user->cash_balance)->toEqual(950000000);
});

it('can create sell order', function () {
    $user = User::factory()->create([
        'cash_balance' => 1000000000,
        'gold_balance' => 10,
    ]);

    $this->actingAs($user);

    $response = $this->postJson('/api/orders', [
        'type' => OrderType::SELL->value,
        'price' => 25000000,
        'amount' => 2,
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'type' => OrderType::SELL->value,
        'price' => 25000000,
        'amount' => 2,
    ]);

    $user->refresh();
    expect($user->gold_balance)->toEqual(8);
});

it('cannot create buy order with insufficient balance', function () {
    $user = User::factory()->create([
        'cash_balance' => 10000000,
        'gold_balance' => 10,
    ]);

    $this->actingAs($user);

    $response = $this->postJson('/api/orders', [
        'type' => OrderType::BUY->value,
        'price' => 25000000,
        'amount' => 2,
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Insufficient cash balance',
        ]);

    $this->assertDatabaseCount('orders', 0);
});

it('cannot create sell order with insufficient gold', function () {
    $user = User::factory()->create([
        'cash_balance' => 1000000000,
        'gold_balance' => 1,
    ]);

    $this->actingAs($user);

    $response = $this->postJson('/api/orders', [
        'type' => OrderType::SELL->value,
        'price' => 25000000,
        'amount' => 2,
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Insufficient gold balance',
        ]);

    $this->assertDatabaseCount('orders', 0);
});

it('can cancel own order', function () {
    $user = User::factory()->create([
        'cash_balance' => 1000000000,
        'gold_balance' => 10,
    ]);

    $this->actingAs($user);

    $order = Order::query()->create([
        'user_id' => $user->id,
        'type' => OrderType::BUY->value,
        'price' => 25000000,
        'amount' => 2,
        'remaining_amount' => 2,
        'status' => OrderStatus::OPEN->value,
    ]);

    $user->cash_balance -= (25000000 * 2);
    $user->save();

    $response = $this->deleteJson('/api/orders/'.$order->id);

    $response->assertStatus(200)
        ->assertJson([
            'status' => OrderStatus::CANCELLED->value,
            'message' => 'Order cancelled successfully',
        ]);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => OrderStatus::CANCELLED->value,
    ]);

    $user->refresh();
    expect($user->cash_balance)->toEqual(1000000000);
});

it('can view own orders', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Order::query()->create([
        'user_id' => $user->id,
        'type' => OrderType::BUY->value,
        'price' => 25000000,
        'amount' => 2,
        'remaining_amount' => 2,
        'status' => OrderStatus::OPEN->value,
    ]);

    Order::query()->create([
        'user_id' => $user->id,
        'type' => OrderType::SELL->value,
        'price' => 26000000,
        'amount' => 1,
        'remaining_amount' => 1,
        'status' => OrderStatus::OPEN->value,
    ]);

    $response = $this->getJson('/api/orders');

    $response->assertOk();
});
