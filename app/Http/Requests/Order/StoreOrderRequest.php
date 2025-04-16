<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Enums\OrderType;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:'.OrderType::BUY->value.','.OrderType::SELL->value],
            'price' => ['required', 'integer', 'min:1000000'],
            'amount' => ['required', 'numeric', 'min:0.1', 'max:1000'],
        ];
    }
}
