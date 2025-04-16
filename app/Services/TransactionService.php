<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Transaction\GetUserTransactionsAction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

class TransactionService
{
    public function getUserTransactions(User $user): Collection
    {
        return app(GetUserTransactionsAction::class)->execute($user);
    }

    public function calculateCommission(float $amount, int $price): array
    {
        return Transaction::calculateCommission($amount, $price);
    }
}
