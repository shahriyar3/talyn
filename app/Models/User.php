<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'gold_balance',
        'cash_balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'gold_balance' => 'float',
        'cash_balance' => 'integer',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function buyTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_id', 'id');
    }

    public function sellTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'seller_id', 'id');
    }
}
