<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 10 random users with random balances
        User::factory(10)->create();

        // Create a test user with specific email and balanced
        User::factory()->create([
            'name' => 'Shahriyar',
            'email' => 'shahriyar@email.com',
            'gold_balance' => 10, // 10 grams of gold
            'cash_balance' => 100000000, // 100 million Rials
        ]);
    }
}
