<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type->value,
            'price' => $this->price,
            'price_formatted' => format_rial($this->price),
            'amount' => $this->amount,
            'remaining_amount' => $this->remaining_amount,
            'status' => $this->status->value,
            'total_value' => $this->price * $this->amount,
            'total_value_formatted' => format_rial($this->price * $this->amount),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
