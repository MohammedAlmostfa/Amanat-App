<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'notes' => $this->notes,
            'address' => $this->address,
            'last_payment_duration' =>(int)  $this->last_payment_duration,
            'remaining_amount' => $this->latestDebt ? $this->latestDebt->remaining_amount : null,
        ];

    }
}
