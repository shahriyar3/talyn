<?php

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
