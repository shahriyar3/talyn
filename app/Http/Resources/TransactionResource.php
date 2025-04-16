<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'buyer_id' => $this->buyer_id,
            'buyer_name' => $this->buyer->name,
            'seller_id' => $this->seller_id,
            'seller_name' => $this->seller->name,
            'amount' => $this->amount,
            'price' => $this->price,
            'price_formatted' => format_rial($this->price),
            'commission' => $this->commission,
            'commission_formatted' => format_rial($this->commission),
            'commission_rate' => $this->commission_rate,
            'total_value' => $this->amount * $this->price,
            'total_value_formatted' => format_rial($this->amount * $this->price),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
