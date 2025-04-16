<?php

use App\Models\User;

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
