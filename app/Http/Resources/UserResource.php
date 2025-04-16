<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'gold_balance' => $this->gold_balance,
            'gold_balance_formatted' => $this->gold_balance.' گرم',
            'cash_balance' => $this->cash_balance,
            'cash_balance_rial' => format_rial($this->cash_balance),
            'cash_balance_toman' => format_toman(rial_to_toman($this->cash_balance)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
