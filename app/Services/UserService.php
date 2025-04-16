<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        if (! isset($data['gold_balance'])) {
            $data['gold_balance'] = round(mt_rand(1 * 1000, 20 * 1000) / 1000, 3);
        }

        if (! isset($data['cash_balance'])) {
            $data['cash_balance'] = mt_rand(10000000, 500000000);
        }

        return User::query()->create($data);
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function getUserBalance(User $user): array
    {
        return [
            'gold_balance' => $user->gold_balance,
            'cash_balance' => $user->cash_balance,
        ];
    }
}
