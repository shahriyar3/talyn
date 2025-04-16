<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;

it('can register a user', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Shahriyar',
        'email' => 'shahriyar@email.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'gold_balance',
                'cash_balance',
            ],
            'token',
        ]);

    $this->assertDatabaseHas('users', [
        'name' => 'Shahriyar',
        'email' => 'shahriyar@email.com',
    ]);
});

it('can login', function () {
    $user = User::factory()->create([
        'email' => 'shahriyar@email.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'shahriyar@email.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
            ],
            'token',
        ]);
});

it('cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'shahriyar@email.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'shahriyar@email.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Invalid credentials',
        ]);
});

it('can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Logged out successfully',
        ]);

    // Check that the token was deleted
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('has correct currency helper functions', function () {
    // Test rial_to_toman
    expect(rial_to_toman(1000000))->toEqual(100000);

    // Test toman_to_rial
    expect(toman_to_rial(100000))->toEqual(1000000);

    // Test format_toman
    expect(format_toman(100000))->toEqual('100,000 تومان');

    // Test format_rial
    expect(format_rial(1000000))->toEqual('1,000,000 ریال');
});

it('returns user profile information', function () {
    // Create a user
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'cash_balance' => 1000000000,
        'gold_balance' => 10,
    ]);

    // Authenticate as this user
    $this->actingAs($user);

    // Get user profile
    $response = $this->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'cash_balance',
            'gold_balance',
            'created_at',
            'updated_at',
        ])
        ->assertJsonPath('name', 'Test User')
        ->assertJsonPath('email', 'test@example.com')
        ->assertJsonPath('cash_balance', 1000000000)
        ->assertJsonPath('gold_balance', 10);
});

it('returns user balance information', function () {
    // Create a user
    $user = User::factory()->create([
        'cash_balance' => 1000000000,
        'gold_balance' => 15,
    ]);

    // Authenticate as this user
    $this->actingAs($user);

    // Get user balance
    $response = $this->getJson('/api/user/balance');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'cash_balance',
            'gold_balance',
        ])
        ->assertJsonPath('cash_balance', 1000000000)
        ->assertJsonPath('gold_balance', 15);
});

it('can access user relationships correctly', function () {
    $user = User::factory()->create();

    $dummyUser = User::factory()->create([
        'id' => 999,
        'name' => 'Dummy User',
        'email' => 'dummy@example.com',
    ]);

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

    $buyTransaction = Transaction::query()->create([
        'order_id' => $sellOrder->id,
        'buyer_id' => $user->id,
        'seller_id' => 999,
        'amount' => 1,
        'price' => 26000000,
        'commission' => 520000,
        'commission_rate' => 0.02,
    ]);

    $sellTransaction = Transaction::query()->create([
        'order_id' => $buyOrder->id,
        'buyer_id' => 999,
        'seller_id' => $user->id,
        'amount' => 2,
        'price' => 25000000,
        'commission' => 750000,
        'commission_rate' => 0.015,
    ]);

    expect($user->orders)->toHaveCount(2)
        ->and($user->orders->first()->id)->toEqual($buyOrder->id)
        ->and($user->orders->last()->id)->toEqual($sellOrder->id)
        ->and($user->buyTransactions)->toHaveCount(1)
        ->and($user->buyTransactions->first()->id)->toEqual($buyTransaction->id)
        ->and($user->sellTransactions)->toHaveCount(1)
        ->and($user->sellTransactions->first()->id)->toEqual($sellTransaction->id);

});
