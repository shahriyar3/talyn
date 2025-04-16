<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

readonly class GetUserTransactionsAction
{
    public function execute(User $user): Collection
    {
        return Transaction::query()->where('buyer_id', '=', $user->id)
            ->orWhere('seller_id', '=', $user->id)
            ->with(['buyer', 'seller'])
            ->latest()
            ->get();
    }
}
